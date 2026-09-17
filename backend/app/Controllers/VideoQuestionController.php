<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class VideoQuestionController extends Controller {

    private function getCollection() {
        return Database::getCollection('video_questions');
    }

    // POST /api/video-questions
    public function create($input) {
        $videoId = $input['videoId'] ?? null;
        $videoTitle = $input['videoTitle'] ?? 'فيديو غير معروف';
        $question = $input['question'] ?? '';
        $studentId = $input['studentId'] ?? null;
        $studentName = $input['studentName'] ?? 'طالب مجهول';
        $studentPhone = $input['studentPhone'] ?? '';

        if (empty($videoId) || empty($question) || empty($studentId)) {
            return $this->error('البيانات غير مكتملة', 400);
        }

        $collection = $this->getCollection();

        $doc = [
            'videoId' => $videoId,
            'videoTitle' => $videoTitle,
            'question' => $question,
            'studentId' => $studentId,
            'studentName' => $studentName,
            'studentPhone' => $studentPhone,
            'status' => 'pending',
            'createdAt' => new UTCDateTime(time() * 1000)
        ];

        $result = $collection->insertOne($doc);

        if ($result->getInsertedCount() === 1) {
            $doc['_id'] = (string)$result->getInsertedId();
            return $this->success($doc);
        }

        return $this->error('حدث خطأ أثناء إرسال السؤال', 500);
    }

    // GET /api/video-questions
    public function index() {
        $collection = $this->getCollection();
        $cursor = $collection->find([], ['sort' => ['createdAt' => -1]]);
        
        $questions = [];
        foreach ($cursor as $doc) {
            $item = [];
            foreach ($doc as $k => $v) {
                if ($k === '_id') {
                    $item['_id'] = (string)$v;
                } elseif ($k === 'createdAt') {
                    $item['createdAt'] = $v->toDateTime()->format('Y-m-d H:i');
                } else {
                    $item[$k] = $v;
                }
            }
            $questions[] = $item;
        }

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => array_values($questions)]);
        exit;
    }

    // DELETE /api/video-questions/:id
    public function delete($id) {
        try {
            $collection = $this->getCollection();
            $result = $collection->deleteOne(['_id' => new ObjectId($id)]);

            if ($result->getDeletedCount() === 1) {
                return $this->success(['message' => 'تم حذف السؤال بنجاح']);
            }
            return $this->error('السؤال غير موجود', 404);
        } catch (\Exception $e) {
            return $this->error('معرف سؤال غير صالح', 400);
        }
    }
}
