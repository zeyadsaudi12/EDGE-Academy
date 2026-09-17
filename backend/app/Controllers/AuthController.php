<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use MongoDB\BSON\UTCDateTime;

class AuthController extends Controller {
    // POST /api/register
    public function register($input) {
        $username = $input['username'] ?? null;
        $firstName = $input['firstName'] ?? null;
        $lastName = $input['lastName'] ?? null;
        $birthDate = $input['birthDate'] ?? null;
        $phone = $input['phone'] ?? null;
        $parentPhone = $input['parentPhone'] ?? null;
        $nationalId = $input['nationalId'] ?? null;
        $governorate = $input['governorate'] ?? null;
        $grade = $input['grade'] ?? null;
        $section = $input['section'] ?? null;
        $secondLanguage = $input['secondLanguage'] ?? null;
        $password = $input['password'] ?? null;

        if (!$username || !$firstName || !$lastName || !$phone || !$nationalId || !$password) {
            return $this->error('الرجاء ملء جميع الحقول المطلوبة', 400);
        }

        // Validate Egyptian phone number format
        $phoneRegex = '/^01[0125]\d{8}$/';
        if (!preg_match($phoneRegex, $phone) || ($parentPhone && !preg_match($phoneRegex, $parentPhone))) {
            return $this->error('رقم الهاتف غير صحيح', 400);
        }

        $usersCollection = User::getCollection();

        // Check if phone, username, or nationalId already exists
        $existingUser = $usersCollection->findOne([
            '$or' => [
                ['phone' => $phone],
                ['nationalId' => $nationalId],
                ['username' => $username]
            ]
        ]);

        if ($existingUser) {
            return $this->error('رقم الهاتف، الرقم القومي أو اسم المستخدم مسجل مسبقاً', 400);
        }

        // Prepare birth date as BSON UTCDateTime
        $birthDateTime = null;
        if ($birthDate) {
            $birthDateTime = new UTCDateTime(strtotime($birthDate) * 1000);
        }

        // Create new user document (matching node.js structure)
        $newUser = [
            'username' => $username,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'birthDate' => $birthDateTime,
            'phone' => $phone,
            'parentPhone' => $parentPhone,
            'nationalId' => $nationalId,
            'governorate' => $governorate,
            'grade' => $grade,
            'section' => $section,
            'secondLanguage' => $secondLanguage,
            'password' => $password, // Matches Node.js plain text storage
            'balance' => 0,
            'role' => 'student',
            'followedTeachers' => [],
            'subscribedVideos' => [],
            'lastActive' => new UTCDateTime(time() * 1000),
            'createdAt' => new UTCDateTime(time() * 1000),
            'updatedAt' => new UTCDateTime(time() * 1000)
        ];

        $insertResult = $usersCollection->insertOne($newUser);
        $newUser['_id'] = $insertResult->getInsertedId();

        $this->success(['user' => User::toArray($newUser)], null);
    }

    // POST /api/login
    public function login($input) {
        $phone = $input['phone'] ?? null;
        $password = $input['password'] ?? null;
        $deviceId = $input['deviceId'] ?? null;
        $deviceName = $input['deviceName'] ?? null;

        if (!$phone || !$password) {
            return $this->error('رقم الهاتف وكلمة المرور مطلوبان', 400);
        }

        // Check for the custom admin account
        if ($phone === '01556448880' && $password === 'masar2027@agency') {
            return $this->success([
                'user' => [
                    '_id' => 'admin-master-id',
                    'role' => 'admin',
                    'firstName' => 'الإدارة',
                    'lastName' => '',
                    'phone' => '01556448880'
                ]
            ]);
        }

        $usersCollection = User::getCollection();
        $user = $usersCollection->findOne([
            'phone' => $phone,
            'password' => $password
        ]);

        if (!$user) {
            return $this->error('رقم الهاتف أو كلمة المرور غير صحيحة', 401);
        }

        // Device security check for students
        if (isset($user['role']) && $user['role'] === 'student') {
            $devices = isset($user['devices']) ? iterator_to_array($user['devices']) : [];
            
            // If deviceId is not set, generate a new one
            if (empty($deviceId)) {
                $deviceId = 'dev_' . bin2hex(random_bytes(16));
            }

            // Find if this device is already registered
            $foundIndex = -1;
            foreach ($devices as $index => $device) {
                if (isset($device['deviceId']) && $device['deviceId'] === $deviceId) {
                    $foundIndex = $index;
                    break;
                }
            }

            if ($foundIndex !== -1) {
                // Update existing device
                $devices[$foundIndex]['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $devices[$foundIndex]['lastUsed'] = new UTCDateTime(time() * 1000);
            } else {
                // Check if device count exceeds the limit of 2 devices
                if (count($devices) >= 2) {
                    return $this->error('❌ لقد تم الوصول للحد الأقصى للأجهزة المسموح بها (جهازين). يرجى تسجيل الخروج من أجهزتك الأخرى أولاً أو التواصل مع الإدارة لإعادة ضبط حسابك.', 403);
                }

                // Add new device
                $devices[] = [
                    'deviceId' => $deviceId,
                    'deviceName' => $deviceName ?: 'متصفح ويب',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    'lastUsed' => new UTCDateTime(time() * 1000)
                ];
            }

            // Save updated devices back to MongoDB
            $usersCollection->updateOne(
                ['_id' => $user['_id']],
                ['$set' => ['devices' => $devices]]
            );

            // Re-fetch updated user doc
            $user = $usersCollection->findOne(['_id' => $user['_id']]);
        }

        $userData = User::toArray($user);
        if (!empty($deviceId)) {
            $userData['deviceId'] = $deviceId;
        }

        $this->success([
            'user' => $userData,
            'deviceId' => $deviceId
        ]);
    }
}
