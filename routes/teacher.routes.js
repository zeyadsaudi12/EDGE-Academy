const express = require('express');
const router = express.Router();
const teacherController = require('../controllers/teacher.controller');
const upload = require('../middleware/upload');

router.get('/follower-counts', teacherController.getFollowerCounts);
router.put('/:id', upload.single('image'), teacherController.updateTeacher);
router.get('/', teacherController.getAllTeachers);
router.post('/', upload.single('image'), teacherController.createTeacher);
router.delete('/:id', teacherController.deleteTeacher);

module.exports = router;
