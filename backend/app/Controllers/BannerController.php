<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Banner;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class BannerController extends Controller {

    // GET /api/banners
    public function index($input) {
        try {
            $bannersCollection = Banner::getCollection();
            $cursor = $bannersCollection->find([], [
                'sort' => ['order' => 1, 'createdAt' => 1]
            ]);
            $banners = Banner::toArrayMultiple($cursor);
            $this->success(['banners' => $banners]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // POST /api/banners
    public function create($input) {
        $title = $_POST['title'] ?? '';
        $link = $_POST['link'] ?? '';
        $order = isset($_POST['order']) ? intval($_POST['order']) : 0;

        $uploadsDir = __DIR__ . '/../../../uploads';
        $imagePath = $this->handleFileUpload('image', $uploadsDir);
        if (!$imagePath) {
            $imagePath = $_POST['imagePath'] ?? '';
        }

        if (!$imagePath) {
            return $this->error('الصورة مطلوبة', 400);
        }

        $newBanner = [
            'imagePath' => $imagePath,
            'title' => $title,
            'link' => $link,
            'order' => $order,
            'createdAt' => new UTCDateTime(time() * 1000)
        ];

        try {
            $bannersCollection = Banner::getCollection();
            $insertResult = $bannersCollection->insertOne($newBanner);
            $newBanner['_id'] = $insertResult->getInsertedId();
            $this->success(['banner' => Banner::toArray($newBanner)], null, 201);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // DELETE /api/banners/:id
    public function delete($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الإعلان غير موجود', 404);
        }

        try {
            $bannersCollection = Banner::getCollection();
            $banner = $bannersCollection->findOne(['_id' => new ObjectId($id)]);
            if (!$banner) {
                return $this->error('الإعلان غير موجود', 404);
            }

            // Unlink image file from disk
            if (isset($banner['imagePath'])) {
                $filePath = __DIR__ . '/../../..' . $banner['imagePath'];
                if (file_exists($filePath) && is_file($filePath)) {
                    unlink($filePath);
                }
            }

            $bannersCollection->deleteOne(['_id' => new ObjectId($id)]);
            $this->success();
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }
}
