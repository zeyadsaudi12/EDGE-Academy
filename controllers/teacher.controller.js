const Teacher = require('../models/teacher.model');
const User = require('../models/user.model');
const fs = require('fs');
const path = require('path');

exports.getFollowerCounts = async (req, res, next) => {
    try {
        const students = await User.find({ role: 'student' }).select('followedTeachers');
        const counts = {};
        students.forEach(s => {
            (s.followedTeachers || []).forEach(tid => {
                counts[tid] = (counts[tid] || 0) + 1;
            });
        });
        res.json(counts);
    } catch (err) {
        next(err);
    }
};

exports.updateTeacher = async (req, res, next) => {
    try {
        const { name, subjectAr, bio, grades } = req.body;
        const teacher = await Teacher.findById(req.params.id);
        if (!teacher) {
            return res.status(404).json({ success: false, message: 'المعلم غير موجود' });
        }

        teacher.name = name || teacher.name;
        teacher.subjectAr = subjectAr || teacher.subjectAr;
        teacher.bio = bio || teacher.bio;

        if (grades) {
            teacher.grades = Array.isArray(grades) ? grades : grades.split(',').map(g => g.trim());
        }

        if (req.file) {
            if (teacher.imagePath) {
                const oldPath = path.join(__dirname, '..', teacher.imagePath);
                if (fs.existsSync(oldPath)) {
                    try { fs.unlinkSync(oldPath); } catch (e) { console.error("Error deleting old image:", e); }
                }
            }
            teacher.imagePath = `/uploads/${req.file.filename}`;
        }

        await teacher.save();
        res.json({ success: true, teacher });
    } catch (err) {
        next(err);
    }
};

exports.getAllTeachers = async (req, res, next) => {
    try {
        const teachers = await Teacher.find().lean();
        res.json(teachers);
    } catch (err) {
        next(err);
    }
};

exports.createTeacher = async (req, res, next) => {
    try {
        const { name, subjectAr, bio, grades } = req.body;
        const imagePath = req.file ? `/uploads/${req.file.filename}` : '';
        let gradesArray = [];
        if (grades) gradesArray = Array.isArray(grades) ? grades : grades.split(',').map(g => g.trim());
        const newTeacher = new Teacher({ name, subjectAr, bio, imagePath, grades: gradesArray });
        await newTeacher.save();
        res.status(201).json({ success: true, teacher: newTeacher });
    } catch (err) {
        next(err);
    }
};

exports.deleteTeacher = async (req, res, next) => {
    try {
        const teacher = await Teacher.findByIdAndDelete(req.params.id);
        if (!teacher) {
            return res.status(404).json({ success: false, message: 'المعلم غير موجود' });
        }
        if (teacher.imagePath) {
            const imgPath = path.join(__dirname, '..', teacher.imagePath);
            if (fs.existsSync(imgPath)) {
                try { fs.unlinkSync(imgPath); } catch (e) { console.error('خطأ في حذف صورة المعلم:', e); }
            }
        }
        res.json({ success: true, message: 'تم حذف المعلم بنجاح' });
    } catch (err) {
        next(err);
    }
};
