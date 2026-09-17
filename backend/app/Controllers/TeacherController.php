<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Teacher;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class TeacherController extends Controller {

    // GET /api/teachers
    public function index($input) {
        try {
            $teachersCollection = Teacher::getCollection();
            $cursor = $teachersCollection->find();
            $teachers = Teacher::toArrayMultiple($cursor);
            $this->json($teachers);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // GET /api/teachers/:id
    public function show($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('المعلم غير موجود', 404);
        }

        try {
            $teachersCollection = Teacher::getCollection();
            $teacher = $teachersCollection->findOne(['_id' => new ObjectId($id)]);
            if (!$teacher) {
                return $this->error('المعلم غير موجود', 404);
            }
            $this->json(Teacher::toArray($teacher));
        } catch (\Exception $e) {
            $this->error('خطأ في جلب بيانات المعلم: ' . $e->getMessage(), 500);
        }
    }

  // POST /api/teachers (مع حل مشكلة تكرار الرقم القومي الفريد للحسابات التلقائية)
    public function create($input) {
        $name = $_POST['name'] ?? null;
        $subjectAr = $_POST['subjectAr'] ?? null;
        $bio = $_POST['bio'] ?? '';
        $grades = $_POST['grades'] ?? '';

        if (!$name || !$subjectAr) {
            return $this->error('الاسم والمادة مطلوبان', 400);
        }

        $uploadsDir = __DIR__ . '/../../../uploads';
        $imagePath = $this->handleFileUpload('image', $uploadsDir) ?? '';

        $gradesArray = [];
        if (!empty($grades)) {
            $gradesArray = is_array($grades) ? $grades : array_map('trim', explode(',', $grades));
        }

        $newTeacher = [
            'name' => $name,
            'subjectAr' => $subjectAr,
            'bio' => $bio,
            'imagePath' => $imagePath,
            'grades' => $gradesArray,
            'createdAt' => new UTCDateTime(time() * 1000)
        ];

        try {
            $teachersCollection = Teacher::getCollection();
            $insertResult = $teachersCollection->insertOne($newTeacher);
            $teacherId = $insertResult->getInsertedId();
            $newTeacher['_id'] = $teacherId;

            // استدعاء كوليكشن المستخدمين بالنظام المتوافق والمعتمد في مشروعك
            $usersCollection = \App\Models\User::getCollection();

            // 1. توليد رقم هاتف عشوائي فريد يبدأ بـ 05
            $uniquePhone = '';
            do {
                $uniquePhone = '05' . rand(100000000, 999999999);
                $existing = $usersCollection->findOne(['phone' => $uniquePhone]);
            } while ($existing !== null);

            // 2. توليد كلمة مرور عشوائية وصعبة التخمين مكونة من 8 أحرف وأرقام
            $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $randomPassword = '';
            for ($i = 0; $i < 8; $i++) {
                $randomPassword .= $chars[rand(0, strlen($chars) - 1)];
            }

            // 3. تسجيل الحساب تلقائياً برتبة "teacher" مع حل مشكلة قيود الـ Unique Index في المونجو
            $usersCollection->insertOne([
                'phone' => $uniquePhone,
                'password' => $randomPassword,
                'role' => 'teacher',
                'teacherId' => $teacherId,
                'firstName' => 'مساعد ' . $name,
                'lastName' => 'التعليمي',
                'username' => 'helper_' . $uniquePhone, // جعل اسم المستخدم فريداً بالهاتف المولد
                'nationalId' => 'helper_' . $uniquePhone, // حل مشكلة الرقم القومي المكرر بحقنه بقيمة فريدة ومميزة
                'createdAt' => new UTCDateTime(time() * 1000)
            ]);

            // إرجاع النتيجة الناجحة
            $this->success([
                'teacher' => Teacher::toArray($newTeacher),
                'assistantAccount' => [
                    'phone' => $uniquePhone,
                    'password' => $randomPassword
                ]
            ], null, 201);

        } catch (\Throwable $e) {
            $this->error('خطأ برمجي في السيرفر: ' . $e->getMessage() . ' في السطر ' . $e->getLine(), 500);
        }
    }
  
  
  
  
  
  // POST /api/teachers/:id/update (دالة تعديل بيانات المعلم والترقية التلقائية للحسابات القديمة)
    public function update($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('المعلم غير موجود', 404);
        }

        $name = $_POST['name'] ?? null;
        $subjectAr = $_POST['subjectAr'] ?? null;
        $bio = $_POST['bio'] ?? null;
        $grades = $_POST['grades'] ?? null;
        
        // استقبال جدول المواعيد كـ JSON وإلغاء ترميزه لمصفوفة PHP
        $scheduleRaw = $_POST['schedule'] ?? null;
        $schedule = $scheduleRaw ? json_decode($scheduleRaw, true) : null;

        try {
            $teachersCollection = Teacher::getCollection();
            $teacher = $teachersCollection->findOne(['_id' => new ObjectId($id)]);

            if (!$teacher) {
                return $this->error('المعلم غير موجود', 404);
            }

            $updateData = [];
            if ($name) $updateData['name'] = $name;
            if ($subjectAr) $updateData['subjectAr'] = $subjectAr;
            if ($bio !== null) $updateData['bio'] = $bio;
            if ($schedule !== null) $updateData['schedule'] = $schedule;

            if ($grades !== null) {
                $gradesArray = [];
                if (!empty($grades)) {
                    $gradesArray = is_array($grades) ? $grades : array_map('trim', explode(',', $grades));
                }
                $updateData['grades'] = $gradesArray;
            }

            // استبدال الصورة الشخصية الجديدة
            $uploadsDir = __DIR__ . '/../../../uploads';
            $newImagePath = $this->handleFileUpload('image', $uploadsDir);
            if ($newImagePath) {
                if (!empty($teacher['imagePath'])) {
                    $oldFilePath = __DIR__ . '/../../../' . ltrim($teacher['imagePath'], '/');
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }
                $updateData['imagePath'] = $newImagePath;
            }

            if (!empty($updateData)) {
                $teachersCollection->updateOne(
                    ['_id' => new ObjectId($id)],
                    ['$set' => $updateData]
                );
            }

            // 🌟 [الترقية التلقائية الذكية لمعلمي السنتر القدامى]
            // نتحقق مما إذا كان هذا المعلم يمتلك حساب مساعد مسجل مسبقاً
            $usersCollection = \App\Models\User::getCollection();
            $existingAssistant = $usersCollection->findOne(['teacherId' => new ObjectId($id)]);
            
            $generatedAccount = null;

            if (!$existingAssistant) {
                // لو لم يمتلك حساب مساعد، نقوم بتوليد وإنشاء حساب له فوراً وحفظه
                
                // 1. توليد هاتف عشوائي فريد ومميز يبدأ بـ 05
                $uniquePhone = '';
                do {
                    $uniquePhone = '05' . rand(100000000, 999999999);
                    $existing = $usersCollection->findOne(['phone' => $uniquePhone]);
                } while ($existing !== null);

                // 2. توليد كلمة مرور عشوائية وصعبة التخمين مكونة من 8 أحرف وأرقام
                $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
                $randomPassword = '';
                for ($i = 0; $i < 8; $i++) {
                    $randomPassword .= $chars[rand(0, strlen($chars) - 1)];
                }

                // 3. تسجيل حساب المساعد وربطه بمعرف المعلم القديم وحل مشكلة الرقم القومي المكرر
                $usersCollection->insertOne([
                    'phone' => $uniquePhone,
                    'password' => $randomPassword,
                    'role' => 'teacher',
                    'teacherId' => new ObjectId($id),
                    'firstName' => 'مساعد ' . ($name || $teacher['name']),
                    'lastName' => 'التعليمي',
                    'username' => 'helper_' . $uniquePhone,
                    'nationalId' => 'helper_' . $uniquePhone,
                    'createdAt' => new UTCDateTime(time() * 1000)
                ]);

                $generatedAccount = [
                    'phone' => $uniquePhone,
                    'password' => $randomPassword
                ];
            }

            $updatedTeacher = $teachersCollection->findOne(['_id' => new ObjectId($id)]);
            
            // تمرير الحساب الجديد للإدارة في حال تم إنشاؤه
            $response = ['teacher' => Teacher::toArray($updatedTeacher)];
            if ($generatedAccount) {
                $response['assistantAccount'] = $generatedAccount;
            }

            $this->success($response);
        } catch (\Throwable $e) {
            $this->error('حدث خطأ أثناء التعديل: ' . $e->getMessage() . ' في السطر ' . $e->getLine(), 500);
        }
    }

    // DELETE /api/teachers/:id
    public function delete($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('المعلم غير موجود', 404);
        }

        try {
            $teachersCollection = Teacher::getCollection();
            $teachersCollection->deleteOne(['_id' => new ObjectId($id)]);
            $this->success();
        } catch (\Exception $e) {
            $this->error('حدث خطأ', 500);
        }
    }


    // POST /api/attendance/scan
    public function registerAttendance($input) {
        $data = json_decode(file_get_contents('php://input'), true);
        $studentId = $data['studentId'] ?? null;
        $videoId = $data['videoId'] ?? null;
        $center = $data['center'] ?? '—';
        $day = $data['day'] ?? '—';
        $grade = $data['grade'] ?? '—';
        $time = $data['time'] ?? '—';

        if (!$studentId || !$videoId) {
            return $this->error('بيانات الطالب والمحاضرة مطلوبة', 400);
        }

        try {
            $usersCollection = \App\Models\User::getCollection();
            
            if (strlen($studentId) !== 24 || !ctype_xdigit($studentId)) {
                return $this->error('كود الطالب الممسوح غير صحيح', 400);
            }
            
            $user = $usersCollection->findOne(['_id' => new \MongoDB\BSON\ObjectId($studentId)]);

            if (!$user) {
                return $this->error('الطالب غير مسجل بالمنصة', 404);
            }

            // 1. تفعيل المحاضرة لحساب الطالب مباشرة
            $subscribed = isset($user['subscribedVideos']) ? (array)$user['subscribedVideos'] : [];
            if (!in_array($videoId, $subscribed)) {
                $subscribed[] = $videoId;
                $usersCollection->updateOne(
                    ['_id' => new \MongoDB\BSON\ObjectId($studentId)],
                    ['$set' => ['subscribedVideos' => $subscribed]]
                );
            }

            // 2. تسجيل وحفظ تقرير حضور تفصيلي في جدول الحضور بقاعدة البيانات
            $db = \App\Core\Database::getDb();
            $attendanceCollection = $db->selectCollection('attendances');
            
            $attendanceCollection->insertOne([
                'studentId' => new \MongoDB\BSON\ObjectId($studentId),
                'studentName' => ($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''),
                'studentPhone' => $user['phone'] ?? '',
                'videoId' => new \MongoDB\BSON\ObjectId($videoId),
                'center' => $center,
                'day' => $day,
                'grade' => $grade,
                'time' => $time,
                'scannedAt' => new \MongoDB\BSON\UTCDateTime(time() * 1000)
            ]);

            $this->success([
                'message' => 'تم تسجيل الحضور وتفعيل الكورس بنجاح ✅',
                'studentName' => ($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')
            ]);
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء معالجة الحضور: ' . $e->getMessage(), 500);
        }
    }

    // GET /api/teachers/follower-counts
    public function getFollowerCounts($input) {
        try {
            $usersCollection = \App\Models\User::getCollection();
            $cursor = $usersCollection->find(
                ['role' => 'student'],
                ['projection' => ['followedTeachers' => 1]]
            );
            
            $counts = [];
            foreach ($cursor as $student) {
                if (isset($student['followedTeachers'])) {
                    $teachers = iterator_to_array($student['followedTeachers']);
                    foreach ($teachers as $tid) {
                        $tidStr = (string)$tid;
                        if (!isset($counts[$tidStr])) {
                            $counts[$tidStr] = 0;
                        }
                        $counts[$tidStr]++;
                    }
                }
            }
            $this->json($counts);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
}