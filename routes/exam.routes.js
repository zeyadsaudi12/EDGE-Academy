const express = require('express');
const router = express.Router();
const examController = require('../controllers/exam.controller');
const upload = require('../middleware/upload');

router.get('/student/:studentId/results', examController.getStudentResults);
router.get('/', examController.getAllExams);
router.delete('/:examId/results/:studentId', examController.resetStudentExam);
router.get('/admin', examController.getAllExamsForAdmin);
router.get('/:id', examController.getExamById);
router.post('/', examController.createExam);
router.put('/:id', examController.updateExam);
router.delete('/:id', examController.deleteExam);
router.post('/:id/submit', examController.submitExam);
router.post('/:examId/grade-essay', examController.gradeEssay);
router.post('/upload-file', upload.fields([{ name: 'examFile', maxCount: 1 }, { name: 'answerFile', maxCount: 1 }]), examController.uploadFile);
router.get('/:id/results', examController.getExamResults);

module.exports = router;
