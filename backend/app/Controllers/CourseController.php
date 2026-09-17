<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Course;
use App\Models\Video;
use App\Models\User;
use App\Models\Teacher;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class CourseController extends Controller {

    // GET /api/courses
    public function index($input) {
        try {
            $coursesCollection = Course::getCollection();
            $cursor = $coursesCollection->find([], ['sort' => ['createdAt' => -1]]);
            $courses = Course::toArrayMultiple($cursor);

            // Fetch video counts for each course
            $videosCollection = Video::getCollection();
            foreach ($courses as &$course) {
                $cId = (string)$course['_id'];
                $videoCount = $videosCollection->countDocuments([
                    '$or' => [
                        ['courseId' => $cId],
                        ['courseId' => new ObjectId($cId)]
                    ]
                ]);
                // Add image alias (imagePath -> image)
                $course['image'] = $course['imagePath'] ?? '';
                // Add videoCount
                $course['videoCount'] = $videoCount;
                // Keep videoIds array for JS .length checks
                if (!isset($course['videoIds'])) $course['videoIds'] = [];

                // Also get teacher name if teacherId is present
                if (!empty($course['teacherId']) && strlen($course['teacherId']) === 24) {
                    $teacher = Teacher::getCollection()->findOne(['_id' => new ObjectId($course['teacherId'])]);
                    if ($teacher) {
                        $course['teacherName'] = $teacher['name'] ?? '';
                        $course['teacherImage'] = $teacher['imagePath'] ?? '';
                        $course['teacherSubject'] = $teacher['subjectAr'] ?? '';
                    }
                }
            }

            $this->json($courses);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // GET /api/courses/:id
    public function show($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الكورس غير موجود', 404);
        }

        try {
            $coursesCollection = Course::getCollection();
            $course = $coursesCollection->findOne(['_id' => new ObjectId($id)]);
            if (!$course) {
                return $this->error('الكورس غير موجود', 404);
            }

            $courseData = Course::toArray($course);

            // Fetch teacher info
            if (!empty($courseData['teacherId']) && strlen($courseData['teacherId']) === 24) {
                $teacher = Teacher::getCollection()->findOne(['_id' => new ObjectId($courseData['teacherId'])]);
                if ($teacher) {
                    $courseData['teacherName'] = $teacher['name'] ?? '';
                    $courseData['teacherImage'] = $teacher['imagePath'] ?? '';
                    $courseData['teacherSubject'] = $teacher['subjectAr'] ?? '';
                }
            }

            // Fetch all videos in this course
            $videosCollection = Video::getCollection();
            $cursor = $videosCollection->find([
                '$or' => [
                    ['courseId' => $id],
                    ['courseId' => new ObjectId($id)]
                ]
            ], ['sort' => ['createdAt' => 1]]);
            $videos = Video::toArrayMultiple($cursor);

            $courseData['videos'] = $videos;
            $courseData['videoCount'] = count($videos);

            $this->success(['course' => $courseData]);
        } catch (\Exception $e) {
            $this->error('خطأ في جلب بيانات الكورس: ' . $e->getMessage(), 500);
        }
    }

    // POST /api/courses
    public function create($input) {
        $title = $_POST['title'] ?? ($input['title'] ?? null);
        $description = $_POST['description'] ?? ($input['description'] ?? '');
        $price = isset($_POST['price']) ? (float)$_POST['price'] : (isset($input['price']) ? (float)$input['price'] : 0);
        $teacherId = $_POST['teacherId'] ?? ($input['teacherId'] ?? null);
        $grades = $_POST['grades[]'] ?? $_POST['grades'] ?? ($input['grades'] ?? []);
        $hidden = isset($_POST['hidden']) ? filter_var($_POST['hidden'], FILTER_VALIDATE_BOOLEAN) : (isset($input['hidden']) ? (bool)$input['hidden'] : false);

        if (!$title) {
            return $this->error('عنوان الكورس مطلوب', 400);
        }

        $uploadsDir = __DIR__ . '/../../../uploads';
        $imagePath = $this->handleFileUpload('image', $uploadsDir) ?? '';

        $gradesArray = [];
        if (!empty($grades)) {
            $gradesArray = is_array($grades) ? array_values($grades) : array_map('trim', explode(',', $grades));
        }

        $newCourse = [
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'teacherId' => $teacherId,
            'grades' => $gradesArray,
            'imagePath' => $imagePath,
            'hidden' => $hidden,
            'createdAt' => new UTCDateTime(time() * 1000),
            'updatedAt' => new UTCDateTime(time() * 1000)
        ];

        try {
            $coursesCollection = Course::getCollection();
            $insertResult = $coursesCollection->insertOne($newCourse);
            $newCourse['_id'] = $insertResult->getInsertedId();

            $this->success(['course' => Course::toArray($newCourse)], 'تم إنشاء الكورس بنجاح', 201);
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء إنشاء الكورس: ' . $e->getMessage(), 500);
        }
    }

    // PUT/POST /api/courses/:id
    public function update($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الكورس غير موجود', 404);
        }

        try {
            $coursesCollection = Course::getCollection();
            $existingCourse = $coursesCollection->findOne(['_id' => new ObjectId($id)]);
            if (!$existingCourse) {
                return $this->error('الكورس غير موجود', 404);
            }

            $updateData = [];
            $title = $_POST['title'] ?? ($input['title'] ?? null);
            if ($title !== null) $updateData['title'] = $title;

            $description = $_POST['description'] ?? ($input['description'] ?? null);
            if ($description !== null) $updateData['description'] = $description;

            $price = isset($_POST['price']) ? (float)$_POST['price'] : (isset($input['price']) ? (float)$input['price'] : null);
            if ($price !== null) $updateData['price'] = $price;

            $teacherId = $_POST['teacherId'] ?? ($input['teacherId'] ?? null);
            if ($teacherId !== null) $updateData['teacherId'] = $teacherId;

            $grades = $_POST['grades'] ?? ($input['grades'] ?? null);
            if ($grades !== null) {
                $updateData['grades'] = is_array($grades) ? $grades : array_map('trim', explode(',', $grades));
            }

            if (isset($_POST['hidden'])) {
                $updateData['hidden'] = filter_var($_POST['hidden'], FILTER_VALIDATE_BOOLEAN);
            } elseif (isset($input['hidden'])) {
                $updateData['hidden'] = (bool)$input['hidden'];
            }

            // Check new image upload
            $uploadsDir = __DIR__ . '/../../../uploads';
            $newImage = $this->handleFileUpload('image', $uploadsDir);
            if ($newImage) {
                $updateData['imagePath'] = $newImage;
            }

            $updateData['updatedAt'] = new UTCDateTime(time() * 1000);

            $coursesCollection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => $updateData]
            );

            $updated = $coursesCollection->findOne(['_id' => new ObjectId($id)]);
            $this->success(['course' => Course::toArray($updated)], 'تم تحديث الكورس بنجاح');
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء تعديل الكورس: ' . $e->getMessage(), 500);
        }
    }

    // DELETE /api/courses/:id
    public function delete($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الكورس غير موجود', 404);
        }

        try {
            $coursesCollection = Course::getCollection();
            $coursesCollection->deleteOne(['_id' => new ObjectId($id)]);

            // Unlink all videos belonging to this course
            $videosCollection = Video::getCollection();
            $videosCollection->updateMany(
                ['$or' => [['courseId' => $id], ['courseId' => new ObjectId($id)]]],
                ['$set' => ['courseId' => '']]
            );

            $this->success(null, 'تم حذف الكورس وإلغاء ارتباط فيديوهاته بنجاح');
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء حذف الكورس: ' . $e->getMessage(), 500);
        }
    }

    // GET /api/courses/:id/students
    public function students($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الكورس غير موجود', 404);
        }

        try {
            $usersCollection = User::getCollection();
            $cursor = $usersCollection->find([
                'role' => 'student',
                '$or' => [
                    ['subscribedCourses' => new ObjectId($id)],
                    ['subscribedCourses' => $id]
                ]
            ]);

            $students = [];
            foreach ($cursor as $user) {
                $students[] = [
                    'id' => (string)$user['_id'],
                    'name' => ($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''),
                    'phone' => $user['phone'] ?? '—',
                    'grade' => $user['grade'] ?? '—',
                    'governorate' => $user['governorate'] ?? '—'
                ];
            }

            $this->success(['students' => $students]);
        } catch (\Exception $e) {
            $this->error('خطأ في جلب طلاب الكورس: ' . $e->getMessage(), 500);
        }
    }

    // GET /api/courses/:id/videos
    public function videos($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الكورس غير موجود', 404);
        }

        try {
            $coursesCollection = Course::getCollection();
            $course = $coursesCollection->findOne(['_id' => new ObjectId($id)]);
            $isStandaloneVideo = false;

            if (!$course) {
                // Check if this id belongs to a standalone video
                $videoModel = Video::getCollection()->findOne(['_id' => new ObjectId($id)]);
                if ($videoModel) {
                    $isStandaloneVideo = true;
                    $course = [
                        '_id' => $videoModel['_id'],
                        'title' => $videoModel['title'] ?? 'كورس ومحاضرة',
                        'description' => $videoModel['description'] ?? ($videoModel['playlistName'] ?? ''),
                        'price' => $videoModel['price'] ?? null,
                        'teacherId' => $videoModel['teacherId'] ?? null,
                        'grades' => $videoModel['grades'] ?? [],
                        'imagePath' => $videoModel['imagePath'] ?? '',
                        'hidden' => $videoModel['hidden'] ?? false
                    ];
                } else {
                    return $this->error('الكورس غير موجود', 404);
                }
            }

            $courseData = is_array($course) ? $course : Course::toArray($course);
            if (isset($courseData['_id']) && $courseData['_id'] instanceof ObjectId) {
                $courseData['_id'] = (string)$courseData['_id'];
            }
            if (isset($courseData['teacherId']) && $courseData['teacherId'] instanceof ObjectId) {
                $courseData['teacherId'] = (string)$courseData['teacherId'];
            }

            $userId = $_GET['userId'] ?? ($input['userId'] ?? null);
            $isEnrolled = false;   // اشتراك فعلي فقط
            $isAdmin   = false;    // صلاحيات المشرف
            $userSubscribedVideos = [];

            if ($userId && $userId !== 'admin-master-id' && strlen($userId) === 24 && ctype_xdigit($userId)) {
                $user = User::getCollection()->findOne(['_id' => new ObjectId($userId)]);
                if ($user) {
                    $isAdmin = (isset($user['role']) && $user['role'] === 'admin') ||
                               (isset($user['phone']) && in_array($user['phone'], ['01556448880', '01234567890']));

                    // ─── تحقق من الاشتراك الفعلي (ينطبق على الجميع بما فيهم الأدمن)
                    if (isset($user['subscribedCourses'])) {
                        $subCourses = iterator_to_array($user['subscribedCourses']);
                        foreach ($subCourses as $sc) {
                            if (is_array($sc) || is_object($sc)) {
                                $scId = (string)($sc['courseId'] ?? $sc['_id'] ?? '');
                            } else {
                                $scId = (string)$sc;
                            }
                            if ($scId === (string)$id) {
                                $isEnrolled = true;
                                break;
                            }
                        }
                    }

                    if (isset($user['subscribedVideos'])) {
                        $subVids = iterator_to_array($user['subscribedVideos']);
                        $userSubscribedVideos = array_map(function($sv) { return (string)$sv; }, $subVids);
                        if (in_array((string)$id, $userSubscribedVideos)) {
                            $isEnrolled = true;
                        }
                    }
                }
            } elseif ($userId === 'admin-master-id') {
                // admin-master-id = حساب لوحة التحكم فقط
                $isAdmin = true;
            }

            // الكورس المجاني بالكامل (price === null) → مفتوح للجميع
            $rawPrice = $course['price'] ?? 'NOT_SET';
            $isCourseFree = ($rawPrice === null || $rawPrice === 'NOT_SET');
            if ($isCourseFree) {
                $isEnrolled = true;
            }

            // Fetch all videos in this course
            $videosCollection = Video::getCollection();
            if ($isStandaloneVideo) {
                $singleVid = Video::toArray($videoModel);
                $singleVid['_id'] = (string)$singleVid['_id'];
                $rawVideos = [$singleVid];
            } else {
                $cursor = $videosCollection->find([
                    '$or' => [
                        ['courseId' => $id],
                        ['courseId' => new ObjectId($id)]
                    ]
                ], ['sort' => ['createdAt' => 1]]);
                $rawVideos = Video::toArrayMultiple($cursor);
            }

            $videos = [];
            foreach ($rawVideos as $v) {
                $vId = (string)$v['_id'];
                $isFreeVideo = ($v['price'] === null);   // الفيديو مجاني بالكامل
                $isSubToVid  = in_array($vId, $userSubscribedVideos);

                // مفتوح إذا: مشترك في الكورس || مشترك في الفيديو || فيديو مجاني
                // الأدمن يشوف الفيديوهات فقط بدون أن يُعدّ "مشتركاً"
                $unlocked = $isEnrolled || $isFreeVideo || $isSubToVid || $isAdmin;

                $v['isUnlocked'] = $unlocked;
                $v['daysRemaining'] = 0;
                $videos[] = $v;
            }

            $this->success([
                'course' => $courseData,
                'isEnrolled' => $isEnrolled || $isAdmin,
                'videos' => $videos
            ]);
        } catch (\Exception $e) {
            $this->error('خطأ في جلب محاضرات الكورس: ' . $e->getMessage(), 500);
        }
    }

    // POST /api/courses/:id/buy
    public function buy($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الكورس غير موجود', 404);
        }

        $codeStr = trim($input['codeStr'] ?? ($_POST['codeStr'] ?? ''));
        $userId = trim($input['userId'] ?? ($_POST['userId'] ?? ''));

        if (!$codeStr || !$userId) {
            return $this->error('يرجى إدخال كود التفعيل وتسجيل الدخول', 400);
        }

        try {
            $codesCollection = \App\Models\Code::getCollection();
            $code = $codesCollection->findOne(['code' => strtoupper($codeStr)]);

            if (!$code) {
                // Check exact match
                $code = $codesCollection->findOne(['code' => $codeStr]);
            }

            if (!$code) {
                return $this->error('كود التفعيل غير صحيح', 404);
            }

            if (!empty($code['used'])) {
                return $this->error('هذا الكود تم استخدامه من قبل', 400);
            }

            // Verify code is for this course or a video or general course code
            $codeCourseId = isset($code['courseId']) ? (string)$code['courseId'] : null;
            $codeVideoId = isset($code['videoId']) ? (string)$code['videoId'] : null;

            if ($codeCourseId && $codeCourseId !== $id) {
                return $this->error('هذا الكود مخصص لكورس آخر', 400);
            }

            // Mark code as used
            $codesCollection->updateOne(
                ['_id' => $code['_id']],
                ['$set' => [
                    'used' => true,
                    'usedBy' => $userId,
                    'usedAt' => new UTCDateTime(time() * 1000)
                ]]
            );

            // Fetch course videos to unlock all of them
            $videosCollection = Video::getCollection();
            $cursor = $videosCollection->find([
                '$or' => [
                    ['_id' => new ObjectId($id)],
                    ['courseId' => $id],
                    ['courseId' => new ObjectId($id)]
                ]
            ]);
            $videoIds = [(string)$id];
            foreach ($cursor as $v) {
                $videoIds[] = (string)$v['_id'];
            }
            $videoIds = array_unique($videoIds);

            // Enroll user in course and all its videos
            if (strlen($userId) === 24 && ctype_xdigit($userId)) {
                $usersCollection = User::getCollection();
                $usersCollection->updateOne(
                    ['_id' => new ObjectId($userId)],
                    [
                        '$addToSet' => [
                            'subscribedCourses' => [
                                'courseId' => $id,
                                'purchasedAt' => new UTCDateTime(time() * 1000),
                                'codeUsed' => $codeStr
                            ],
                            'subscribedVideos' => ['$each' => $videoIds]
                        ]
                    ]
                );
            }

            $this->success(['enrolled' => true], 'تم تفعيل الكورس وفتح جميع محاضراته بنجاح! 🎉');
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء تفعيل الكورس: ' . $e->getMessage(), 500);
        }
    }
}
