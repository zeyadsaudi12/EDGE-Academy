const User = require('../models/user.model');
const Code = require('../models/code.model');
const mongoose = require('mongoose');

exports.follow = async (req, res, next) => {
    try {
        const { teacherId } = req.body;
        if (!mongoose.Types.ObjectId.isValid(req.params.id) || req.params.id === 'admin-master-id') {
            return res.status(400).json({ success: false, message: 'معرف مستخدم غير صالح' });
        }
        const user = await User.findById(req.params.id);
        if (!user) return res.status(404).json({ success: false, message: 'المستخدم غير موجود' });

        if (!user.followedTeachers) user.followedTeachers = [];

        const index = user.followedTeachers.indexOf(teacherId);
        let isFollowing = false;

        if (index > -1) {
            user.followedTeachers.splice(index, 1); // إلغاء المتابعة
        } else {
            user.followedTeachers.push(teacherId); // تفعيل المتابعة
            isFollowing = true;
        }

        await user.save();
        res.json({ success: true, isFollowing, followedTeachers: user.followedTeachers });
    } catch (err) {
        next(err);
    }
};

exports.subscribe = async (req, res, next) => {
    try {
        const { codeStr, videoId } = req.body;
        if (!mongoose.Types.ObjectId.isValid(req.params.id) || req.params.id === 'admin-master-id') {
            return res.status(400).json({ success: false, message: 'معرف مستخدم غير صالح' });
        }

        // 1. التحقق من كود الشحن وصلاحيته لهذا الفيديو
        const code = await Code.findOne({ code: codeStr, videoId });
        if (!code) {
            return res.status(404).json({ success: false, message: 'كود الشحن غير صحيح أو لا يخص هذه المحاضرة' });
        }
        if (code.views >= 1) {
            return res.status(400).json({ success: false, message: 'هذا الكود مستخدم بالفعل (صالح للاستخدام مرة واحدة فقط)' });
        }
        if (code.studentId && code.studentId.toString() !== req.params.id) {
            return res.status(403).json({ success: false, message: 'كود الشحن مستخدم بالفعل بواسطة طالب آخر' });
        }

        // 2. تحديث بيانات المستخدم (إضافة المحاضرة لكورسات الطالب)
        const user = await User.findById(req.params.id);
        if (!user) return res.status(404).json({ success: false, message: 'المستخدم غير موجود' });

        if (!user.subscribedVideos) user.subscribedVideos = [];
        if (!user.subscribedVideos.includes(videoId)) {
            user.subscribedVideos.push(videoId);
        }

        // 3. ربط الكود بالطالب وزيادة الـ views
        code.views += 1;
        code.used = true;
        code.studentId = req.params.id;

        await Promise.all([user.save(), code.save()]);
        res.json({ success: true, remainingViews: 1 - code.views, user });
    } catch (err) {
        next(err);
    }
};

exports.getAllUsers = async (req, res, next) => {
    try {
        const users = await User.find({ role: 'student' }).select('-password');
        res.json(users);
    } catch (err) {
        next(err);
    }
};

exports.getUserById = async (req, res, next) => {
    try {
        const user = await User.findById(req.params.id);
        if (!user) return res.status(404).json({ success: false, message: 'المستحدم غير موجود' });
        res.json({ success: true, user });
    } catch (err) {
        next(err);
    }
};

exports.deleteUser = async (req, res, next) => {
    try {
        await User.findByIdAndDelete(req.params.id);
        res.json({ success: true });
    } catch (err) {
        next(err);
    }
};

exports.updateUser = async (req, res, next) => {
    try {
        const updated = await User.findByIdAndUpdate(req.params.id, req.body, { new: true }).select('-password');
        res.json({ success: true, user: updated });
    } catch (err) {
        next(err);
    }
};
