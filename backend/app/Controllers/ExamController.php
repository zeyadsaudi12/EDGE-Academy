<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Exam;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class ExamController extends Controller {

    // GET /api/exams
    public function index($input) {
        $grade = $_GET['grade'] ?? null;
        $teacherId = $_GET['teacherId'] ?? null;
        $studentId = $_GET['studentId'] ?? null;

        $filter = ['isActive' => true];
        if ($grade) {
            $filter['grades'] = $grade;
        }
        if ($teacherId) {
            $filter['teacherId'] = $teacherId;
        }

        try {
            $examsCollection = Exam::getCollection();
            $cursor = $examsCollection->find($filter, ['sort' => ['createdAt' => -1]]);
            $exams = iterator_to_array($cursor);

            // Replicate Express logic to protect results
            $mappedExams = [];
            foreach ($exams as $exam) {
                $examObj = Exam::toArray($exam);
                
                if ($studentId) {
                    $studentResult = null;
                    if (isset($exam['results'])) {
                        foreach ($exam['results'] as $res) {
                            if (isset($res['studentId']) && $res['studentId'] === $studentId) {
                                $studentResult = Exam::toArray($res);
                                break;
                            }
                        }
                    }
                    $examObj['results'] = $studentResult ? [$studentResult] : [];
                } else {
                    unset($examObj['results']);
                }
                
                $mappedExams[] = $examObj;
            }

            $this->success(['exams' => $mappedExams]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // GET /api/exams/admin
    public function adminIndex($input) {
        try {
            $examsCollection = Exam::getCollection();
            $cursor = $examsCollection->find([], ['sort' => ['createdAt' => -1]]);
            $exams = Exam::toArrayMultiple($cursor);
            $this->success(['exams' => $exams]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // GET /api/exams/:id
    public function show($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الامتحان غير موجود', 404);
        }

        $studentId = $_GET['studentId'] ?? null;
        $studentName = $_GET['studentName'] ?? 'طالب';
        $studentPhone = $_GET['studentPhone'] ?? '';

        try {
            $examsCollection = Exam::getCollection();
            $exam = $examsCollection->findOne(['_id' => new ObjectId($id)]);
            if (!$exam) {
                return $this->error('الامتحان غير موجود', 404);
            }
            
            $examObj = Exam::toArray($exam);

            // Check if randomization is enabled for this exam
            $randomCount = isset($examObj['randomCount']) ? intval($examObj['randomCount']) : 0;
            if ($randomCount > 0 && !empty($examObj['questions'])) {
                $questionsPool = $examObj['questions'];
                $studentQuestions = null;

                // Find if student has already started/submitted and has a stored question set
                $results = isset($exam['results']) ? iterator_to_array($exam['results']) : [];
                foreach ($results as $res) {
                    if (isset($res['studentId']) && $res['studentId'] === $studentId && isset($res['randomQuestions'])) {
                        $studentQuestions = Exam::toArray($res['randomQuestions']);
                        break;
                    }
                }

                // If not found, generate a new set of random questions
                if ($studentQuestions === null) {
                    // Select randomCount questions from the pool
                    $poolKeys = array_keys($questionsPool);
                    shuffle($poolKeys);
                    $selectedKeys = array_slice($poolKeys, 0, min($randomCount, count($questionsPool)));
                    
                    $studentQuestions = [];
                    foreach ($selectedKeys as $key) {
                        $q = $questionsPool[$key];
                        $q['originalIndex'] = $key; // Store original index
                        $studentQuestions[] = $q;
                    }

                    // Save this set of questions for the student in the results array
                    if ($studentId) {
                        $pendingResult = [
                            'studentId' => $studentId,
                            'studentName' => $studentName,
                            'studentPhone' => $studentPhone,
                            'randomQuestions' => $studentQuestions,
                            'isStarted' => true,
                            'isGraded' => false,
                            'score' => 0,
                            'percentage' => 0,
                            'answers' => [],
                            'essayScores' => [],
                            'submittedAt' => null
                        ];
                        
                        $updatedResults = [];
                        $found = false;
                        foreach ($results as $res) {
                            if (isset($res['studentId']) && $res['studentId'] === $studentId) {
                                $res['randomQuestions'] = $studentQuestions;
                                $res['isStarted'] = true;
                                $updatedResults[] = $res;
                                $found = true;
                            } else {
                                $updatedResults[] = $res;
                            }
                        }
                        if (!$found) {
                            $updatedResults[] = $pendingResult;
                        }

                        $examsCollection->updateOne(
                            ['_id' => new ObjectId($id)],
                            ['$set' => ['results' => $updatedResults]]
                        );
                    }
                }

                // Replace the exam's questions with the student's randomized set for this response
                $examObj['questions'] = $studentQuestions;
            }

            $this->success(['exam' => $examObj]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // GET /api/exams/student/:studentId/results
    public function studentResults($studentId, $input) {
        try {
            $examsCollection = Exam::getCollection();
            $cursor = $examsCollection->find(['results.studentId' => $studentId]);
            
            $studentResults = [];
            foreach ($cursor as $exam) {
                if (isset($exam['results'])) {
                    foreach ($exam['results'] as $res) {
                        if (isset($res['studentId']) && $res['studentId'] === $studentId) {
                            $studentResults[] = [
                                'examId' => (string)$exam['_id'],
                                'examTitle' => $exam['title'] ?? '',
                                'subject' => $exam['subject'] ?? '',
                                'teacherName' => $exam['teacherName'] ?? '',
                                'score' => $res['score'] ?? 0,
                                'totalMarks' => $exam['totalMarks'] ?? 0,
                                'percentage' => $res['percentage'] ?? 0,
                                'isGraded' => isset($res['isGraded']) ? (bool)$res['isGraded'] : true,
                                'submittedAt' => isset($res['submittedAt']) ? Exam::toArray($res['submittedAt']) : null
                            ];
                        }
                    }
                }
            }

            $this->success(['results' => $studentResults]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // POST /api/exams
    public function create($input) {
        $title = $input['title'] ?? null;
        $description = $input['description'] ?? '';
        $teacherId = $input['teacherId'] ?? null;
        $teacherName = $input['teacherName'] ?? '';
        $subject = $input['subject'] ?? '';
        $grades = $input['grades'] ?? [];
        $duration = isset($input['duration']) ? intval($input['duration']) : 30;
        $randomCount = isset($input['randomCount']) ? intval($input['randomCount']) : 0;
        $questions = $input['questions'] ?? [];
        $totalMarks = isset($input['totalMarks']) ? intval($input['totalMarks']) : count($questions);

        if (!$title || empty($questions)) {
            return $this->error('العنوان والأسئلة مطلوبة', 400);
        }

        $newExam = [
            'title' => $title,
            'description' => $description,
            'teacherId' => $teacherId,
            'teacherName' => $teacherName,
            'subject' => $subject,
            'grades' => $grades,
            'duration' => $duration,
            'randomCount' => $randomCount,
            'totalMarks' => $totalMarks,
            'questions' => $questions,
            'isActive' => true,
            'results' => [],
            'createdAt' => new UTCDateTime(time() * 1000),
            'updatedAt' => new UTCDateTime(time() * 1000)
        ];

        try {
            $examsCollection = Exam::getCollection();
            $insertResult = $examsCollection->insertOne($newExam);
            $newExam['_id'] = $insertResult->getInsertedId();
            $this->success(['exam' => Exam::toArray($newExam)], null, 201);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // PUT /api/exams/:id
    public function update($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الامتحان غير موجود', 404);
        }

        try {
            $examsCollection = Exam::getCollection();
            
            unset($input['_id']);
            $input['updatedAt'] = new UTCDateTime(time() * 1000);

            $examsCollection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => $input]
            );

            $updated = $examsCollection->findOne(['_id' => new ObjectId($id)]);
            $this->success(['exam' => Exam::toArray($updated)]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // DELETE /api/exams/:id
    public function delete($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الامتحان غير موجود', 404);
        }

        try {
            $examsCollection = Exam::getCollection();
            $examsCollection->deleteOne(['_id' => new ObjectId($id)]);
            $this->success();
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // DELETE /api/exams/:examId/results/:studentId
    public function deleteResult($examId, $studentId, $input) {
        if (strlen($examId) !== 24 || !ctype_xdigit($examId)) {
            return $this->error('الامتحان غير موجود', 404);
        }

        try {
            $examsCollection = Exam::getCollection();
            $exam = $examsCollection->findOne(['_id' => new ObjectId($examId)]);
            if (!$exam) {
                return $this->error('الامتحان غير موجود', 404);
            }

            $results = isset($exam['results']) ? iterator_to_array($exam['results']) : [];
            $filteredResults = [];

            foreach ($results as $res) {
                if (isset($res['studentId']) && $res['studentId'] !== $studentId) {
                    $filteredResults[] = $res;
                }
            }

            $examsCollection->updateOne(
                ['_id' => new ObjectId($examId)],
                ['$set' => ['results' => $filteredResults]]
            );

            $this->success([], 'تم إعادة تعيين محاولة الطالب بنجاح');
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // GET /api/exams/:id/results
    public function examResults($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الامتحان غير موجود', 404);
        }

        try {
            $examsCollection = Exam::getCollection();
            $exam = $examsCollection->findOne(['_id' => new ObjectId($id)]);
            if (!$exam) {
                return $this->error('الامتحان غير موجود', 404);
            }

            $this->success([
                'results' => Exam::toArray($exam['results'] ?? []),
                'examTitle' => $exam['title'] ?? ''
            ]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // POST /api/exams/:id/submit
    public function submit($id, $input) {
        if (strlen($id) !== 24 || !ctype_xdigit($id)) {
            return $this->error('الامتحان غير موجود', 404);
        }

        $studentId = $input['studentId'] ?? null;
        $studentName = $input['studentName'] ?? '';
        $studentPhone = $input['studentPhone'] ?? '';
        $answers = $input['answers'] ?? []; // Array index aligned with questions

        try {
            $examsCollection = Exam::getCollection();
            $exam = $examsCollection->findOne(['_id' => new ObjectId($id)]);
            if (!$exam) {
                return $this->error('الامتحان غير موجود', 404);
            }

            // Check if already submitted
            $results = isset($exam['results']) ? iterator_to_array($exam['results']) : [];
            $studentResultIndex = -1;
            $studentQuestions = null;
            $existingPending = null;

            foreach ($results as $idx => $res) {
                if (isset($res['studentId']) && $res['studentId'] === $studentId) {
                    if (isset($res['submittedAt']) && $res['submittedAt'] !== null) {
                        return $this->error('لقد أرسلت هذا امتحان مسبقاً', 400);
                    }
                    $studentResultIndex = $idx;
                    $existingPending = Exam::toArray($res);
                    if (isset($res['randomQuestions'])) {
                        $studentQuestions = Exam::toArray($res['randomQuestions']);
                    }
                    break;
                }
            }

            $score = 0;
            $hasEssays = false;
            $essayScores = [];
            $corrected = [];

            $questions = ($studentQuestions !== null) ? $studentQuestions : (isset($exam['questions']) ? iterator_to_array($exam['questions']) : []);
            $totalMarks = ($studentQuestions !== null) ? count($studentQuestions) : ($exam['totalMarks'] ?? count($questions));

            foreach ($questions as $i => $q) {
                $studentAns = trim((string)($answers[$i] ?? ''));
                $isCorrect = false;
                $qType = $q['type'] ?? 'mcq';
                $isEssay = ($qType === 'essay');

                if ($isEssay) {
                    $hasEssays = true;
                    $essayScores[] = ['questionIndex' => $i, 'score' => 0];
                } else {
                    $correctAnswer = trim((string)($q['correctAnswer'] ?? ''));
                    $isCorrect = ($studentAns === $correctAnswer);
                    if ($isCorrect) {
                        $score++;
                    }
                }

                $corrected[] = [
                    'question' => $q['text'] ?? '',
                    'options' => isset($q['options']) ? iterator_to_array($q['options']) : [],
                    'studentAnswer' => $studentAns,
                    'correctAnswer' => $q['correctAnswer'] ?? '',
                    'isCorrect' => $isCorrect,
                    'isEssay' => $isEssay
                ];
            }

            $isGraded = !$hasEssays;
            $percentage = round(($score / $totalMarks) * 100);

            $newResult = [
                'studentId' => $studentId,
                'studentName' => $studentName,
                'studentPhone' => $studentPhone,
                'score' => $score,
                'percentage' => $percentage,
                'answers' => $answers,
                'essayScores' => $essayScores,
                'isGraded' => $isGraded,
                'submittedAt' => new UTCDateTime(time() * 1000)
            ];

            if ($studentQuestions !== null) {
                $newResult['randomQuestions'] = $studentQuestions;
            }
            if ($existingPending && isset($existingPending['isStarted'])) {
                $newResult['isStarted'] = $existingPending['isStarted'];
            }

            if ($studentResultIndex !== -1) {
                $results[$studentResultIndex] = $newResult;
            } else {
                $results[] = $newResult;
            }

            $examsCollection->updateOne(
                ['_id' => new ObjectId($id)],
                ['$set' => ['results' => $results]]
            );

            $this->success([
                'score' => $score,
                'percentage' => $percentage,
                'totalMarks' => $totalMarks,
                'corrected' => $corrected,
                'isGraded' => $isGraded
            ]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // POST /api/exams/:examId/grade-essay
    public function gradeEssay($examId, $input) {
        if (strlen($examId) !== 24 || !ctype_xdigit($examId)) {
            return $this->error('الامتحان غير موجود', 404);
        }

        $studentId = $input['studentId'] ?? null;
        $grades = $input['grades'] ?? []; // Map { questionIndex: score }

        try {
            $examsCollection = Exam::getCollection();
            $exam = $examsCollection->findOne(['_id' => new ObjectId($examId)]);
            if (!$exam) {
                return $this->error('الامتحان غير موجود', 404);
            }

            $results = isset($exam['results']) ? iterator_to_array($exam['results']) : [];
            $studentResultIndex = -1;

            foreach ($results as $index => $res) {
                if (isset($res['studentId']) && $res['studentId'] === $studentId) {
                    $studentResultIndex = $index;
                    break;
                }
            }

            if ($studentResultIndex === -1) {
                return $this->error('نتيجة الطالب غير موجودة', 404);
            }

            $result = iterator_to_array($results[$studentResultIndex]);

            $essayScoreSum = 0;
            $essayScores = [];

            $studentQuestions = null;
            if (isset($result['randomQuestions'])) {
                $studentQuestions = Exam::toArray($result['randomQuestions']);
            }
            $questions = ($studentQuestions !== null) ? $studentQuestions : (isset($exam['questions']) ? iterator_to_array($exam['questions']) : []);
            
            foreach ($questions as $i => $q) {
                if (isset($q['type']) && $q['type'] === 'essay') {
                    $givenScore = floatval($grades[$i] ?? 0);
                    $essayScores[] = ['questionIndex' => $i, 'score' => $givenScore];
                    $essayScoreSum += $givenScore;
                }
            }

            // Recalculate MCQ auto score
            $mcqScore = 0;
            foreach ($questions as $i => $q) {
                if (!isset($q['type']) || $q['type'] !== 'essay') {
                    $studentAns = trim((string)($result['answers'][$i] ?? ''));
                    $correctAnswer = trim((string)($q['correctAnswer'] ?? ''));
                    if ($studentAns === $correctAnswer) {
                        $mcqScore++;
                    }
                }
            }

            $totalMarks = ($studentQuestions !== null) ? count($studentQuestions) : ($exam['totalMarks'] ?? count($questions));
            $result['score'] = $mcqScore + $essayScoreSum;
            $result['percentage'] = round(($result['score'] / $totalMarks) * 100);
            $result['essayScores'] = $essayScores;
            $result['isGraded'] = true;

            $results[$studentResultIndex] = $result;

            $examsCollection->updateOne(
                ['_id' => new ObjectId($examId)],
                ['$set' => ['results' => $results]]
            );

            $this->success(['result' => Exam::toArray($result)]);
        } catch (\Exception $e) {
            $this->error($e->getMessage(), 500);
        }
    }

    // POST /api/exams/upload-file
    public function uploadFile($input) {
        if (!isset($_FILES['examFile'])) {
            return $this->error('لم يتم رفع ملف الأسئلة الرئيسي', 400);
        }

        $examFile = $_FILES['examFile'];
        $ext = strtolower(pathinfo($examFile['name'], PATHINFO_EXTENSION));

        $uploadsDir = __DIR__ . '/../../../uploads';
        if (!is_dir($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        $filename = time() . '-' . rand(100, 999) . '.' . $ext;
        $filePath = $uploadsDir . '/' . $filename;

        if (!move_uploaded_file($examFile['tmp_name'], $filePath)) {
            return $this->error('خطأ أثناء حفظ الملف المؤقت', 500);
        }

        $text = '';
        try {
            if ($ext === 'pdf') {
                if (!class_exists('\\Smalot\\PdfParser\\Parser')) {
                    unlink($filePath);
                    return $this->error('يرجى تشغيل composer install لتثبيت مكتبة PDF Parser', 500);
                }
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($filePath);
                $text = $pdf->getText();
            } elseif ($ext === 'docx') {
                if (!class_exists('\\PhpOffice\\PhpWord\\IOFactory')) {
                    unlink($filePath);
                    return $this->error('يرجى تشغيل composer install لتثبيت مكتبة PHPWord', 500);
                }
                $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
                foreach ($phpWord->getSections() as $section) {
                    foreach ($section->getElements() as $element) {
                        if (method_exists($element, 'getText')) {
                            $text .= $element->getText() . "\n";
                        } elseif (method_exists($element, 'getElements')) {
                            foreach ($element->getElements() as $child) {
                                if (method_exists($child, 'getText')) {
                                    $text .= $child->getText() . "\n";
                                }
                            }
                        }
                    }
                }
            } else {
                unlink($filePath);
                return $this->error('صيغة ملف الأسئلة غير مدعومة، يرجى رفع ملف PDF أو DOCX', 400);
            }

            unlink($filePath); // Clean up temp file

            $questions = $this->parseExamText($text);

            // Parse optional answerFile if uploaded
            if (isset($_FILES['answerFile']) && $_FILES['answerFile']['error'] === UPLOAD_ERR_OK) {
                $answerFile = $_FILES['answerFile'];
                $ansExt = strtolower(pathinfo($answerFile['name'], PATHINFO_EXTENSION));
                $ansFilename = time() . '-ans.' . $ansExt;
                $ansPath = $uploadsDir . '/' . $ansFilename;

                if (move_uploaded_file($answerFile['tmp_name'], $ansPath)) {
                    $ansText = '';
                    if ($ansExt === 'pdf' && class_exists('\\Smalot\\PdfParser\\Parser')) {
                        $parser = new \Smalot\PdfParser\Parser();
                        $pdf = $parser->parseFile($ansPath);
                        $ansText = $pdf->getText();
                    } elseif ($ansExt === 'docx' && class_exists('\\PhpOffice\\PhpWord\\IOFactory')) {
                        $phpWord = \PhpOffice\PhpWord\IOFactory::load($ansPath);
                        foreach ($phpWord->getSections() as $section) {
                            foreach ($section->getElements() as $element) {
                                if (method_exists($element, 'getText')) {
                                    $ansText .= $element->getText() . "\n";
                                }
                            }
                        }
                    }
                    
                    unlink($ansPath); // Clean up

                    if (!empty($ansText)) {
                        $correctAnswers = $this->parseAnswersText($ansText);
                        
                        foreach ($questions as $i => &$q) {
                            $matchedAns = null;
                            foreach ($correctAnswers as $ca) {
                                if ($ca['index'] === $i) {
                                    $matchedAns = $ca;
                                    break;
                                }
                            }
                            if (!$matchedAns && isset($correctAnswers[$i])) {
                                $matchedAns = $correctAnswers[$i];
                            }

                            if ($matchedAns) {
                                $ansVal = trim($matchedAns['val']);
                                // Strip invisible BOM characters and trailing punctuation
                                $ansVal = preg_replace('/^[\x{200B}-\x{200D}\x{FEFF}]+|[\x{200B}-\x{200D}\x{FEFF}]+$/u', '', $ansVal);
                                $ansVal = preg_replace('/[\.\-\)]+$/u', '', $ansVal);
                                $ansVal = trim($ansVal);

                                $qType = $q['type'] ?? 'mcq';
                                if ($qType === 'mcq') {
                                    if (preg_match('/^(أ|ا|إ|a|1)$/iu', $ansVal)) {
                                        $q['correctAnswer'] = '0';
                                    } elseif (preg_match('/^(ب|b|2)$/iu', $ansVal)) {
                                        $q['correctAnswer'] = '1';
                                    } elseif (preg_match('/^(ج|c|3)$/iu', $ansVal)) {
                                        $q['correctAnswer'] = '2';
                                    } elseif (preg_match('/^(د|d|4)$/iu', $ansVal)) {
                                        $q['correctAnswer'] = '3';
                                    } else {
                                        // If the user typed the actual option text, try to find its index
                                        $matchedOptIndex = -1;
                                        if (isset($q['options']) && is_array($q['options'])) {
                                            foreach ($q['options'] as $optIdx => $optText) {
                                                if (trim($optText) === $ansVal || str_contains($optText, $ansVal)) {
                                                    $matchedOptIndex = $optIdx;
                                                    break;
                                                }
                                            }
                                        }
                                        if ($matchedOptIndex !== -1) {
                                            $q['correctAnswer'] = (string)$matchedOptIndex;
                                        } else {
                                            $q['correctAnswer'] = $ansVal;
                                        }
                                    }
                                } elseif ($qType === 'truefalse') {
                                    if (preg_match('/(صح|true|1)/iu', $ansVal)) $q['correctAnswer'] = 'true';
                                    else $q['correctAnswer'] = 'false';
                                } else {
                                    $q['correctAnswer'] = $ansVal;
                                }
                            }
                        }
                    }
                }
            }

            $this->success(['questions' => $questions]);
        } catch (\Exception $e) {
            if (file_exists($filePath)) unlink($filePath);
            $this->error('حدث خطأ أثناء معالجة الملف: ' . $e->getMessage(), 500);
        }
    }

    // POST /api/exams/parse-text
    public function parseText($input) {
        $questionsText = $input['questionsText'] ?? '';
        $answersText = $input['answersText'] ?? '';

        if (empty($questionsText)) {
            return $this->error('يجب إدخال نص الأسئلة', 400);
        }

        try {
            $questions = $this->parseExamText($questionsText);

            if (!empty($answersText)) {
                $correctAnswers = $this->parseAnswersText($answersText);
                
                foreach ($questions as $i => &$q) {
                    $matchedAns = null;
                    foreach ($correctAnswers as $ca) {
                        if ($ca['index'] === $i) {
                            $matchedAns = $ca;
                            break;
                        }
                    }
                    if (!$matchedAns && isset($correctAnswers[$i])) {
                        $matchedAns = $correctAnswers[$i];
                    }

                    if ($matchedAns) {
                        $ansVal = trim($matchedAns['val']);
                        // Strip invisible BOM characters and trailing punctuation
                        $ansVal = preg_replace('/^[\x{200B}-\x{200D}\x{FEFF}]+|[\x{200B}-\x{200D}\x{FEFF}]+$/u', '', $ansVal);
                        $ansVal = preg_replace('/[\.\-\)]+$/u', '', $ansVal);
                        $ansVal = trim($ansVal);

                        $qType = $q['type'] ?? 'mcq';
                        if ($qType === 'mcq') {
                            if (preg_match('/^(أ|ا|إ|a|1)$/iu', $ansVal)) {
                                $q['correctAnswer'] = '0';
                            } elseif (preg_match('/^(ب|b|2)$/iu', $ansVal)) {
                                $q['correctAnswer'] = '1';
                            } elseif (preg_match('/^(ج|c|3)$/iu', $ansVal)) {
                                $q['correctAnswer'] = '2';
                            } elseif (preg_match('/^(د|d|4)$/iu', $ansVal)) {
                                $q['correctAnswer'] = '3';
                            } else {
                                $matchedOptIndex = -1;
                                if (isset($q['options']) && is_array($q['options'])) {
                                    foreach ($q['options'] as $optIdx => $optText) {
                                        if (trim($optText) === $ansVal || str_contains($optText, $ansVal)) {
                                            $matchedOptIndex = $optIdx;
                                            break;
                                        }
                                    }
                                }
                                if ($matchedOptIndex !== -1) {
                                    $q['correctAnswer'] = (string)$matchedOptIndex;
                                } else {
                                    $q['correctAnswer'] = $ansVal;
                                }
                            }
                        } elseif ($qType === 'truefalse') {
                            if (preg_match('/(صح|true|1)/iu', $ansVal)) $q['correctAnswer'] = 'true';
                            else $q['correctAnswer'] = 'false';
                        } else {
                            $q['correctAnswer'] = $ansVal;
                        }
                    }
                }
            }

            $this->success(['questions' => $questions]);
        } catch (\Exception $e) {
            $this->error('حدث خطأ أثناء معالجة النص: ' . $e->getMessage(), 500);
        }
    }

    // Helper functions for exam file parsing (transcribed from JS regex engine)
    private function splitInlineOptions($line) {
        $markerRegex = '/(?:\s+|^)([a-d]|[A-D]|[أبجد])\s*[\.\-\)]\s+/u';
        preg_match_all($markerRegex, $line, $matches, PREG_OFFSET_CAPTURE);

        if (empty($matches[0]) || count($matches[0]) <= 1) {
            return null;
        }

        $options = [];
        $markersCount = count($matches[0]);
        for ($i = 0; $i < $markersCount; $i++) {
            $currentOffset = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            $nextOffset = ($i + 1 < $markersCount) ? $matches[0][$i+1][1] : strlen($line);
            $options[] = trim(substr($line, $currentOffset, $nextOffset - $currentOffset));
        }

        return $options;
    }

    private function parseExamText($text) {
        $text = strtr($text, [
            '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4',
            '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9'
        ]);
        $lines = array_filter(array_map('trim', explode("\n", $text)), 'strlen');
        $questions = [];
        $currentQuestion = null;

        $questionRegex = '/^(?:\[(مقالي|اختياري|صح وخطأ)\])?\s*(?:(?:س|سؤال|Q|Question)\s*(\d*)|(\d+))\s*[\.\-\:\)\/]+\s*(.*)$/ui';
        $optionRegex = '/^([أبجدa-d])[\.\-\)]\s*(.*)$/ui';
        $answerRegex = '/^(?:الإجابة|الحل|correct(?:\s*answer)?)\s*[:：\-]\s*(.*)$/ui';

        foreach ($lines as $line) {
            $isOption = preg_match($optionRegex, $line) || $this->splitInlineOptions($line);
            
            $isQuestion = false;
            $forceType = '';
            $qText = '';

            if (preg_match($questionRegex, $line, $qMatch)) {
                $isQuestion = true;
                $forceType = $qMatch[1] ?? '';
                $qText = $qMatch[4];
            } elseif (!$isOption && (str_ends_with($line, '?') || str_ends_with($line, '؟') || !$currentQuestion)) {
                $isQuestion = true;
                $qText = $line;
            } elseif ($currentQuestion && !empty($currentQuestion['options']) && count($currentQuestion['options']) >= 2 && !$isOption && !preg_match($answerRegex, $line)) {
                // Context-based fallback: if we already have a question with at least 2 options,
                // and this line is not an option and not an answer, it's highly likely a new question.
                $isQuestion = true;
                $qText = $line;
            }

            if ($isQuestion) {
                if ($currentQuestion) {
                    if (!$currentQuestion['type']) {
                        if (!empty($currentQuestion['options'])) {
                            $isTrueFalse = count($currentQuestion['options']) === 2 && 
                                           (str_contains($currentQuestion['options'][0], 'صح') || str_contains($currentQuestion['options'][0], 'خطأ'));
                            $currentQuestion['type'] = $isTrueFalse ? 'truefalse' : 'mcq';
                        } else {
                            $currentQuestion['type'] = 'essay';
                        }
                    }
                    $questions[] = $currentQuestion;
                }

                $type = 'mcq';
                if ($forceType === 'مقالي') $type = 'essay';
                elseif ($forceType === 'صح وخطأ') $type = 'truefalse';

                $currentQuestion = [
                    'text' => $qText,
                    'options' => [],
                    'correctAnswer' => '',
                    'type' => $forceType ? $type : null
                ];
                continue;
            }

            if ($currentQuestion) {
                if (preg_match($answerRegex, $line, $ansMatch)) {
                    $currentQuestion['correctAnswer'] = trim($ansMatch[1]);
                } else {
                    $inlineOpts = $this->splitInlineOptions($line);
                    if ($inlineOpts) {
                        $currentQuestion['options'] = array_merge($currentQuestion['options'], $inlineOpts);
                    } elseif (preg_match($optionRegex, $line, $optMatch)) {
                        $currentQuestion['options'][] = trim($optMatch[2]);
                    } else {
                        if (empty($currentQuestion['options']) && !$currentQuestion['correctAnswer']) {
                            $currentQuestion['text'] .= ' ' . $line;
                        } elseif (!empty($currentQuestion['options']) && !$currentQuestion['correctAnswer']) {
                            $currentQuestion['options'][count($currentQuestion['options']) - 1] .= ' ' . $line;
                        }
                    }
                }
            }
        }

        if ($currentQuestion) {
            if (!$currentQuestion['type']) {
                if (!empty($currentQuestion['options'])) {
                    $isTrueFalse = count($currentQuestion['options']) === 2 && 
                                   (str_contains($currentQuestion['options'][0], 'صح') || str_contains($currentQuestion['options'][0], 'خطأ'));
                    $currentQuestion['type'] = $isTrueFalse ? 'truefalse' : 'mcq';
                } else {
                    $currentQuestion['type'] = 'essay';
                }
            }
            $questions[] = $currentQuestion;
        }

        return $questions;
    }

    private function parseAnswersText($text) {
        $text = strtr($text, [
            '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4',
            '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9'
        ]);
        $lines = array_filter(array_map('trim', explode("\n", $text)), 'strlen');
        $answers = [];
        $ansLineRegex = '/^(\d+)\s*[\.\-\:\)\/]+\s*(.*)$/u';
foreach ($lines as $line) {
    if (preg_match($ansLineRegex, $line, $match)) {
        $val = trim($match[2]);
        // صيغة "رقم - حرف - شرح": نستخرج الحرف/القيمة بس ونتجاهل باقي الشرح
        if (preg_match('/^([أاإبجدABCDabcd]|\d+)\s*[-–:]\s*.+$/u', $val, $sub)) {
            $val = trim($sub[1]);
        }
        $answers[] = [
            'index' => intval($match[1]) - 1,
            'val' => $val
        ];
    } else {
        
                if (preg_match('/^(الإجابات|إجابات|الحل|حلول|answers|key|نموذج)/iu', $line)) {
                    continue; // Skip common title lines
                }
                
                // If the answer is literally just a number (e.g. true/false 1/0 or index), we should accept it
                $answers[] = [
                    'index' => count($answers),
                    'val' => trim($line)
                ];
            }
        }
        return $answers;
    }
}
