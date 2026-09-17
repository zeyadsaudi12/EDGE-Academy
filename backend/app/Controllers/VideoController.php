<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Video;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class VideoController extends Controller {

    // GET /api/videos
    public function index($input) {
        $videosCollection = Video::getCollection();
        $cursor = $videosCollection->find([], ['sort' => ['createdAt' => -1]]);
        $videos = Video::toArrayMultiple($cursor);
        $this->json($videos);
    }

    // GET /api/videos/:id
    public function show($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الفيديو غير موجود', 404);
        }

        try {
            $videosCollection = Video::getCollection();
            $video = $videosCollection->findOne(['_id' => new ObjectId($id)]);
            if (!$video) {
                return $this->error('الفيديو غير موجود', 404);
            }
            $this->success(['video' => Video::toArray($video)]);
        } catch (\Exception $e) {
            $this->error('خطأ في جلب الفيديو', 500);
        }
    }
// GET /api/videos/:id/watchers (المطور لدعم مطابقة البيانات القديمة والجديدة معاً)
    public function watchers($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('المحاضرة غير موجودة', 404);
        }

        try {
            $usersCollection = \App\Models\User::getCollection();
            
            // البحث الذكي باستخدام $or لمطابقة الكورسات المفعلة كـ ObjectId أو كـ String
            $cursor = $usersCollection->find([
                'role' => 'student',
                '$or' => [
                    ['subscribedVideos' => new ObjectId($id)],
                    ['subscribedVideos' => $id] // دعم الحسابات القديمة المسجلة كنصوص
                ]
            ]);

            $watchers = [];
            foreach ($cursor as $user) {
                $watchers[] = [
                    'name' => ($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''),
                    'phone' => $user['phone'] ?? '—',
                    'grade' => $user['grade'] ?? '—',
                    'governorate' => $user['governorate'] ?? '—'
                ];
            }

            $this->success(['watchers' => $watchers]);
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء جلب المشاهدين: ' . $e->getMessage(), 500);
        }
    }







// GET /api/attendance/pending-sessions (جلب الحصص المعلقة التي تم تحضيرها ولم يرفع فيديو لها بعد)
    public function pendingSessions($input) {
        try {
            $db = \App\Core\Database::getDb();
            $attendanceCollection = $db->selectCollection('attendances');
            
            // جلب المجموعات الفريدة من جدول الحضور المعلق (videoId = pending)
            $cursor = $attendanceCollection->aggregate([
                ['$match' => ['videoId' => 'pending']],
                ['$group' => [
                    '_id' => [
                        'center' => '$center',
                        'day' => '$day',
                        'grade' => '$grade'
                    ],
                    'count' => ['$sum' => 1]
                ]]
            ]);
            
            $sessions = [];
            foreach ($cursor as $doc) {
                $sessions[] = [
                    'center' => $doc['_id']['center'],
                    'day' => $doc['_id']['day'],
                    'grade' => $doc['_id']['grade'],
                    'count' => $doc['count']
                ];
            }
            
            $this->success(['sessions' => $sessions]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
// POST /api/videos (رفع فيديو جديد وتفعيل الحضور الفعلي المعلق بالسنتر بنظام المقارنة النصية المضمون)
    public function create($input) {
        $title = $_POST['title'] ?? null;
        $link = $_POST['link'] ?? '';
        $priceRaw = $_POST['price'] ?? null;
        $courseId = $_POST['courseId'] ?? '';
        $releaseAfterDays = isset($_POST['releaseAfterDays']) ? (int)$_POST['releaseAfterDays'] : 0;

        // إذا كان الفيديو تابع لكورس، أو فارغ = مجاني مفتوح لمشتركي الكورس، 0 = مجاني بكود، >0 = مدفوع
        if (!empty($courseId)) {
            $price = ($priceRaw === '' || $priceRaw === null) ? null : (int)$priceRaw;
        } else {
            $price = ($priceRaw === '' || $priceRaw === null) ? null : (int)$priceRaw;
        }

        $teacherId = $_POST['teacherId'] ?? null;
        $grades = $_POST['grades'] ?? null;
        $playlistName = $_POST['playlistName'] ?? '';
        $examLink = $_POST['examLink'] ?? '';
        $startDate = $_POST['startDate'] ?? '';
        $endDate = $_POST['endDate'] ?? '';
        $hidden = isset($_POST['hidden']) && ($_POST['hidden'] === 'true' || $_POST['hidden'] === '1');

        // استقبال بيانات ربط الجلسة والحصة الفعلية بالسنتر
        $linkCenter = $_POST['linkCenter'] ?? null;
        $linkDay = $_POST['linkDay'] ?? null;
        $linkGrade = $_POST['linkGrade'] ?? null;

        if (!$title || !$teacherId || !$grades) {
            return $this->error('العنوان، المعلم، والمرحلة مطلوبة', 400);
        }

        $uploadsDir = __DIR__ . '/../../../uploads';
        $imagePath = $this->handleFileUpload('image', $uploadsDir) ?? '';
        $videoPath = $this->handleFileUpload('video', $uploadsDir) ?? '';

        $gradesArray = is_array($grades) ? $grades : array_map('trim', explode(',', $grades));

        // الامتحان القبلي المطلوب لفتح المحاضرة (بنسبة 50% على الأقل)
        $requiredExamId = $_POST['requiredExamId'] ?? '';

        // رفع ملفات المذكرة (ملف أو أكثر) والواجب
        $bookletFiles = $this->handleMultipleFilesUpload('bookletFiles', $uploadsDir);
        $homeworkFiles = $this->handleMultipleFilesUpload('homeworkFiles', $uploadsDir);

        // استقبال الأجزاء الإضافية للفيديو (بلاي ليست داخلية)
        $partsRaw = $_POST['parts'] ?? null;
        $parts = [];
        if ($partsRaw && is_array($partsRaw)) {
            foreach ($partsRaw as $p) {
                $label = trim($p['label'] ?? '');
                $url   = trim($p['url']   ?? '');
                if ($label && $url) {
                    $parts[] = ['label' => $label, 'url' => $url];
                }
            }
        }

        $newVideo = [
            'title' => $title,
            'link' => $link,
            'price' => $price === null ? null : (int)$price,
            'imagePath' => $imagePath,
            'videoPath' => $videoPath,
            'grades' => $gradesArray,
            'teacherId' => $teacherId,
            'courseId' => $courseId,
            'releaseAfterDays' => $releaseAfterDays,
            'playlistName' => $playlistName,
            'examLink' => $examLink,
            'requiredExamId' => $requiredExamId,
            'bookletFiles' => $bookletFiles,
            'homeworkFiles' => $homeworkFiles,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'hidden' => $hidden,
            'parts' => $parts,
            'createdAt' => new UTCDateTime(time() * 1000)
        ];

        try {
            $videosCollection = Video::getCollection();
            $insertResult = $videosCollection->insertOne($newVideo);
            $videoId = $insertResult->getInsertedId();
            $newVideo['_id'] = $videoId;

            // 🌟 الربط والتفعيل التلقائي الفوري والمؤمن بنظام المقارنة النصية
            if (!empty($linkCenter) && !empty($linkDay) && !empty($linkGrade)) {
                $attendanceCollection = \App\Models\Attendance::getCollection();
                $usersCollection = \App\Models\User::getCollection();

                // 1. جلب جميع سجلات الحضور المعلقة لهذه الحصة بالسنتر
                $cursor = $attendanceCollection->find([
                    'center' => $linkCenter,
                    'day' => $linkDay,
                    'grade' => $linkGrade,
                    'videoId' => 'pending' // معلق
                ]);

                foreach ($cursor as $att) {
                    $studentId = $att['studentId'];

                    // 2. تحديث سجل الحضور بالمعرف الجديد الفعلي
                    $attendanceCollection->updateOne(
                        ['_id' => $att['_id']],
                        ['$set' => ['videoId' => $videoId]]
                    );

                    // 3. تفعيل الفيديو مباشرة في مكتبة الطالب بالمنصة (subscribedVideos)
                    $user = $usersCollection->findOne(['_id' => $studentId]);
                    if ($user) {
                        $subscribed = isset($user['subscribedVideos']) ? (array)$user['subscribedVideos'] : [];
                        
                        // تنظيف مصفوفة الطالب وتحويل كافة العناصر إلى نصوص للمقارنة المضمونة 100%
                        $cleanSubscribed = [];
                        $stringSubscribed = [];
                        
                        foreach ($subscribed as $v) {
                            if ($v instanceof \MongoDB\BSON\ObjectId) {
                                $cleanSubscribed[] = $v;
                                $stringSubscribed[] = (string)$v;
                            } elseif (is_string($v) && strlen($v) === 24 && ctype_xdigit($v)) {
                                $cleanSubscribed[] = new \MongoDB\BSON\ObjectId($v);
                                $stringSubscribed[] = $v;
                            } else {
                                $cleanSubscribed[] = $v;
                                $stringSubscribed[] = (string)$v;
                            }
                        }

                        // مقارنة النصوص الآمنة لتفعيل الكورس وتجنب المشاكل البرمجية للكائنات
                        if (!in_array((string)$videoId, $stringSubscribed)) {
                            $cleanSubscribed[] = $videoId;
                            $usersCollection->updateOne(
                                ['_id' => $studentId],
                                ['$set' => ['subscribedVideos' => $cleanSubscribed]]
                            );
                        }
                    }
                }
            }

            $this->success(['video' => Video::toArray($newVideo)], 'تم رفع المحاضرة وفتحها تلقائياً للحاضرين بالسنتر! ✅', 201);
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء رفع المحاضرة: ' . $e->getMessage(), 500);
        }
    }

    // PUT /api/videos/:id
    public function update($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('المحاضرة غير موجودة', 404);
        }

        try {
            $videosCollection = Video::getCollection();
            $video = $videosCollection->findOne(['_id' => new ObjectId($id)]);
            if (!$video) {
                return $this->error('المحاضرة غير موجودة', 404);
            }

            $title = $input['title'] ?? $video['title'];
            $price = array_key_exists('price', $input) ? ($input['price'] === '' || $input['price'] === null ? null : (int)$input['price']) : ($video['price'] ?? null);
            $link = $input['link'] ?? $video['link'];
            $examLink = $input['examLink'] ?? $video['examLink'];
            $startDate = isset($input['startDate']) ? $input['startDate'] : ($video['startDate'] ?? '');
            $endDate = isset($input['endDate']) ? $input['endDate'] : ($video['endDate'] ?? '');
            $hidden = isset($input['hidden']) ? (bool)$input['hidden'] : ($video['hidden'] ?? false);
            $courseId = array_key_exists('courseId', $input) ? $input['courseId'] : ($video['courseId'] ?? '');
            $releaseAfterDays = isset($input['releaseAfterDays']) ? (int)$input['releaseAfterDays'] : ($video['releaseAfterDays'] ?? 0);
            $requiredExamId = array_key_exists('requiredExamId', $input) ? $input['requiredExamId'] : ($video['requiredExamId'] ?? '');
            $bookletFiles = array_key_exists('bookletFiles', $input) ? $input['bookletFiles'] : ($video['bookletFiles'] ?? []);
            $homeworkFiles = array_key_exists('homeworkFiles', $input) ? $input['homeworkFiles'] : ($video['homeworkFiles'] ?? []);

            $videosCollection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => [
                    'title' => $title,
                    'price' => $price,
                    'link' => $link,
                    'examLink' => $examLink,
                    'requiredExamId' => $requiredExamId,
                    'bookletFiles' => $bookletFiles,
                    'homeworkFiles' => $homeworkFiles,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'hidden' => $hidden,
                    'courseId' => $courseId,
                    'releaseAfterDays' => $releaseAfterDays,
                    'updatedAt' => new UTCDateTime(time() * 1000)
                ]]
            );

            $updatedVideo = $videosCollection->findOne(['_id' => new ObjectId($id)]);
            $this->success(['video' => Video::toArray($updatedVideo)]);
        } catch (\Exception $e) {
            $this->error('حدث خطأ في التعديل', 500);
        }
    }

    // DELETE /api/videos/:id
    public function delete($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('المحاضرة غير موجودة', 404);
        }

        try {
            $videosCollection = Video::getCollection();
            $videosCollection->deleteOne(['_id' => new ObjectId($id)]);
            $this->success();
        } catch (\Exception $e) {
            $this->error('حدث خطأ في الحذف', 500);
        }
    }

    // PUT /api/videos/:id/toggle
    public function toggle($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('المحاضرة غير موجودة', 404);
        }

        try {
            $videosCollection = Video::getCollection();
            $video = $videosCollection->findOne(['_id' => new ObjectId($id)]);
            if (!$video) {
                return $this->error('المحاضرة غير موجودة', 404);
            }

            $closed = isset($video['closed']) ? (bool)$video['closed'] : false;
            $newClosedState = !$closed;

            $videosCollection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => ['closed' => $newClosedState]]
            );

            $this->success(['closed' => $newClosedState]);
        } catch (\Exception $e) {
            $this->error('حدث خطأ في تعديل حالة الفيديو', 500);
        }
    }

    // PUT /api/videos/:id/toggle-visibility
    public function toggleVisibility($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('المحاضرة غير موجودة', 404);
        }

        try {
            $videosCollection = Video::getCollection();
            $video = $videosCollection->findOne(['_id' => new ObjectId($id)]);
            if (!$video) {
                return $this->error('المحاضرة غير موجودة', 404);
            }

            $hidden = isset($video['hidden']) ? (bool)$video['hidden'] : false;
            $newHiddenState = !$hidden;

            $videosCollection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => ['hidden' => $newHiddenState]]
            );

            $this->success(['hidden' => $newHiddenState]);
        } catch (\Exception $e) {
            $this->error('حدث خطأ في تعديل حالة الفيديو', 500);
        }
    }

    // POST /api/videos/:id/like
    public function like($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('المحاضرة غير موجودة', 404);
        }

        $studentId = $input['studentId'] ?? null;
        if (!$studentId) {
            return $this->error('studentId مطلوب', 400);
        }

        try {
            $videosCollection = Video::getCollection();
            $video = $videosCollection->findOne(['_id' => new ObjectId($id)]);
            if (!$video) {
                return $this->error('المحاضرة غير موجودة', 404);
            }

            $likes = isset($video['likes']) ? iterator_to_array($video['likes']) : [];
            $index = array_search($studentId, $likes);
            $liked = false;

            if ($index !== false) {
                array_splice($likes, $index, 1);
            } else {
                $likes[] = $studentId;
                $liked = true;
            }

            $videosCollection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => ['likes' => $likes]]
            );

            $this->success([
                'liked' => $liked,
                'likesCount' => count($likes),
                'likes' => $likes
            ]);
        } catch (\Exception $e) {
            $this->error('حدث خطأ في تحديث الإعجاب', 500);
        }
    }
}
