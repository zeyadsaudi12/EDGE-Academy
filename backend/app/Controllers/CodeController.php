<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Code;
use App\Models\Video;
use App\Models\User;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class CodeController extends Controller {

    // GET /api/codes
    public function index($input) {
        try {
            $codesCollection = Code::getCollection();
            $cursor = $codesCollection->find([], ['sort' => ['createdAt' => -1]]);
            $codes = Code::toArrayMultiple($cursor);
            $this->json($codes);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // POST /api/codes/generate
    public function generate($input) {
        $videoId = $input['videoId'] ?? null;
        $videoTitle = $input['videoTitle'] ?? null;
        $value = $input['value'] ?? null;
        $countInput = isset($input['count']) ? intval($input['count']) : 1;

        // Limit count between 1 and 1000
        $count = min(max($countInput, 1), 1000);

        if (!$videoId || !$videoTitle) {
            return $this->error('معرف الفيديو وعنوان الفيديو مطلوبان', 400);
        }

        $newCodes = [];
        $createdAt = new UTCDateTime(time() * 1000);
        
        for ($i = 0; $i < $count; $i++) {
            // Generate a random string like 'MS-XXXXXX'
            $randomStr = strtoupper(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 6));
            $codeStr = 'MS-' . $randomStr;

            $newCodes[] = [
                'code' => $codeStr,
                'videoId' => $videoId,
                'videoTitle' => $videoTitle,
                'value' => $value,
                'views' => 0,
                'used' => false,
                'studentId' => null,
                'createdAt' => $createdAt,
                'updatedAt' => $createdAt
            ];
        }

        try {
            $codesCollection = Code::getCollection();
            $insertResult = $codesCollection->insertMany($newCodes);
            
            // Re-fetch inserted codes or map IDs
            $insertedIds = $insertResult->getInsertedIds();
            foreach ($newCodes as $index => &$code) {
                $code['_id'] = $insertedIds[$index];
            }

            $this->success(['codes' => Code::toArrayMultiple($newCodes)], null, 201);
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء توليد الأكواد: ' . $e->getMessage(), 500);
        }
    }

    // PUT /api/codes/:id
    public function update($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الكود غير موجود', 404);
        }

        $used = isset($input['used']) ? (bool)$input['used'] : false;

        try {
            $codesCollection = Code::getCollection();
            $codesCollection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => ['used' => $used, 'updatedAt' => new UTCDateTime(time() * 1000)]]
            );
            $this->success();
        } catch (\Exception $e) {
            $this->error('حدث خطأ', 500);
        }
    }

    // DELETE /api/codes/:id
    public function delete($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الكود غير موجود', 404);
        }

        try {
            $codesCollection = Code::getCollection();
            $codesCollection->deleteOne(['_id' => new ObjectId($id)]);
            $this->success();
        } catch (\Exception $e) {
            $this->error('حدث خطأ', 500);
        }
    }

    // DELETE /api/codes
    public function deleteAll($input) {
        try {
            $codesCollection = Code::getCollection();
            $codesCollection->deleteMany([]);
            $this->success();
        } catch (\Exception $e) {
            $this->error('حدث خطأ', 500);
        }
    }

    // POST /api/codes/verify
    public function verify($input) {
        $codeStr = $input['codeStr'] ?? null;
        $videoId = $input['videoId'] ?? null;
        $studentId = $input['studentId'] ?? null;
        $alreadySubscribed = isset($input['alreadySubscribed']) && $input['alreadySubscribed'] === true;

        try {
            $codesCollection = Code::getCollection();
            $videosCollection = Video::getCollection();
            $usersCollection = User::getCollection();

            // Fast-path: if frontend says already subscribed, verify directly from DB
            if ($alreadySubscribed && $studentId && $videoId) {
                if ($studentId === 'admin-master-id') {
                    return $this->success([
                        'remainingViews' => 'مفتوحة دائماً',
                        'alreadySubscribed' => true
                    ]);
                }
                if (strlen($studentId) === 24 && ctype_xdigit($studentId)) {
                    $user = $usersCollection->findOne(['_id' => new ObjectId($studentId)]);
                    if ($user) {
                        $subscribedVideos = isset($user['subscribedVideos']) ? iterator_to_array($user['subscribedVideos']) : [];
                        $subscribedVideosStrs = array_map(function($id) { return (string)$id; }, $subscribedVideos);
                        if (in_array((string)$videoId, $subscribedVideosStrs)) {
                            return $this->success([
                                'remainingViews' => 'مفتوحة دائماً',
                                'alreadySubscribed' => true
                            ]);
                        }
                    }
                }
                return $this->error('أنت غير مشترك في هذه المحاضرة', 403);
            }

            // 0. Fetch video first if videoId is provided to do early bypass
            if ($videoId) {
                if (strlen($videoId) === 24 && ctype_xdigit($videoId)) {
                    $video = $videosCollection->findOne(['_id' => new ObjectId($videoId)]);
                    if (!$video) {
                        return $this->error('المحاضرة غير موجودة', 404);
                    }
                    if (isset($video['closed']) && $video['closed'] === true) {
                        return $this->error('هذه المحاضرة مغلقة حالياً بواسطة الإدارة', 403);
                    }

                    // 1. إذا كان أدمن أو طالب مشترك أو المحاضرة مجانية
                    if ($studentId === 'admin-master-id') {
                        return $this->success([
                            'remainingViews' => 'مفتوحة دائماً',
                            'alreadySubscribed' => true
                        ]);
                    }

                    if ($studentId && strlen($studentId) === 24 && ctype_xdigit($studentId)) {
                        $user = $usersCollection->findOne(['_id' => new ObjectId($studentId)]);
                        if ($user) {
                            $isAdmin = (isset($user['role']) && $user['role'] === 'admin') || 
                                       (isset($user['phone']) && ($user['phone'] === '01556448880' || $user['phone'] === '01234567890'));
                            
                            $subscribedVideos = isset($user['subscribedVideos']) ? iterator_to_array($user['subscribedVideos']) : [];
                            $subscribedVideosStrs = array_map(function($id) { return (string)$id; }, $subscribedVideos);
                            $isSubscribed = in_array((string)$videoId, $subscribedVideosStrs);
                            
                            // null = مفتوح للجميع، 0 = مجاني بكود، >0 = مدفوع
                            $priceVal = $video['price'] ?? null;
                            $isFreeForAll = ($priceVal === null);
                            $isFreeWithCode = ($priceVal !== null && (float)$priceVal === 0.0);

                            if ($isAdmin || $isSubscribed || $isFreeForAll || $isFreeWithCode) {
                                // إذا كانت مجانية (بدون كود) سجّل الاشتراك تلقائياً
                                if (($isFreeForAll || $isFreeWithCode) && !$isSubscribed && !$isAdmin) {
                                    $subscribedVideos[] = $videoId;
                                    $usersCollection->updateOne(
                                        ['_id' => new ObjectId($studentId)],
                                        ['$set' => ['subscribedVideos' => $subscribedVideos]]
                                    );
                                }
                                return $this->success([
                                    'remainingViews' => 'مفتوحة دائماً',
                                    'alreadySubscribed' => true
                                ]);
                            }
                        }
                    } else {
                        // مستخدم غير مسجّل - السماح فقط لو المحاضرة مفتوحة للكل (price = null)
                        $priceVal = $video['price'] ?? null;
                        if ($priceVal === null) {
                            return $this->success([
                                'remainingViews' => 'مفتوحة دائماً',
                                'alreadySubscribed' => true
                            ]);
                        }
                    }
                }
            }

            if (!$codeStr) {
                return $this->error('يرجى إدخال كود الشحن لتفعيل الاشتراك', 400);
            }

            // 0.1 Find code if videoId is not passed in request
            if (!$videoId) {
                $code = $codesCollection->findOne(['code' => $codeStr]);
                if ($code) {
                    $videoId = (string)$code['videoId'];
                } else {
                    return $this->error('كود غير صحيح', 404);
                }
            } else {
                $code = $codesCollection->findOne(['code' => $codeStr, 'videoId' => $videoId]);
                if (!$code) {
                    return $this->error('كود غير صحيح', 404);
                }
            }

            // 0.2 Verify that video is not closed (if not fetched before)
            if (!isset($video)) {
                $video = $videosCollection->findOne(['_id' => new ObjectId($videoId)]);
                if (!$video) {
                    return $this->error('المحاضرة غير موجودة', 404);
                }
                if (isset($video['closed']) && $video['closed'] === true) {
                    return $this->error('هذه المحاضرة مغلقة حالياً بواسطة الإدارة', 403);
                }
            }

            $views = $code['views'] ?? 0;
            if ($views >= 1) {
                return $this->error('هذا الكود تم استخدامه بالفعل (صالح للاستخدام مرة واحدة فقط)', 400);
            }

            $codeStudentId = $code['studentId'] ?? null;
            if ($codeStudentId && (string)$codeStudentId !== $studentId) {
                return $this->error('كود مستخدم من طالب آخر', 403);
            }

            // 3. Activate subscription for student
            if ($studentId && $studentId !== 'admin-master-id' && strlen($studentId) === 24 && ctype_xdigit($studentId)) {
                $user = $usersCollection->findOne(['_id' => new ObjectId($studentId)]);
                if ($user) {
                    $subscribedVideos = isset($user['subscribedVideos']) ? iterator_to_array($user['subscribedVideos']) : [];
                    $subscribedVideosStrs = array_map(function($id) { return (string)$id; }, $subscribedVideos);
                    if (!in_array((string)$videoId, $subscribedVideosStrs)) {
                        $subscribedVideos[] = $videoId;
                        $usersCollection->updateOne(
                            ['_id' => new ObjectId($studentId)],
                            ['$set' => ['subscribedVideos' => $subscribedVideos]]
                        );
                    }
                }
            }

            // 4. Increment code views
            $updateFields = [
                'views' => $views + 1,
                'used' => true,
                'updatedAt' => new UTCDateTime(time() * 1000)
            ];
            
            if (!$codeStudentId && $studentId && $studentId !== 'admin-master-id' && strlen($studentId) === 24 && ctype_xdigit($studentId)) {
                $updateFields['studentId'] = $studentId;
            }

            $codesCollection->updateOne(
                ['_id' => $code['_id']],
                ['$set' => $updateFields]
            );

            // Re-fetch updated code
            $updatedCode = $codesCollection->findOne(['_id' => $code['_id']]);

            $this->success([
                'remainingViews' => 1 - ($views + 1),
                'code' => Code::toArray($updatedCode)
            ]);
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء التحقق: ' . $e->getMessage(), 500);
        }
    }
}
