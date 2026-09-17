const User = require('../models/user.model');
const mongoose = require('mongoose');

exports.register = async (req, res, next) => {
    try {
        const { username, firstName, lastName, birthDate, phone, parentPhone, nationalId, governorate, grade, section, secondLanguage, password } = req.body;

        if (!username || !firstName || !lastName || !phone || !nationalId || !password) {
            return res.status(400).json({ success: false, message: 'الرجاء ملء جميع الحقول المطلوبة' });
        }

        const phoneRegex = /^01[0125]\d{8}$/;
        if (!phoneRegex.test(phone) || (parentPhone && !phoneRegex.test(parentPhone))) {
            return res.status(400).json({ success: false, message: 'رقم الهاتف غير صحيح' });
        }

        const existingUser = await User.findOne({ $or: [{ phone }, { nationalId }, { username }] });
        if (existingUser) {
            return res.status(400).json({ success: false, message: 'رقم الهاتف، الرقم القومي أو اسم المستخدم مسجل مسبقاً' });
        }

        const newUser = new User({
            username, firstName, lastName, birthDate, phone, parentPhone, nationalId, governorate, grade, section, secondLanguage, password
        });

        await newUser.save();
        res.status(201).json({ success: true, user: newUser });
    } catch (err) {
        next(err);
    }
};

exports.login = async (req, res, next) => {
    try {
        const { phone, password } = req.body;

        // التحقق من حساب الأدمن المخصص
        if (phone === '01556448880' && password === 'masar2027@agency') {
            return res.json({ success: true, user: { _id: "admin-master-id", role: 'admin', firstName: 'الإدارة', lastName: '', phone: '01556448880' } });
        }

        const user = await User.findOne({ phone, password });
        if (!user) {
            return res.status(401).json({ success: false, message: 'رقم الهاتف أو كلمة المرور غير صحيحة' });
        }

        res.json({ success: true, user });
    } catch (err) {
        next(err);
    }
};

exports.ping = async (req, res, next) => {
    try {
        const { userId } = req.body;
        if (!userId) return res.status(400).json({ success: false, message: 'ID required' });

        if (!mongoose.Types.ObjectId.isValid(userId) || userId === 'admin-master-id') {
            return res.json({ success: true, note: 'Skipped invalid student ID' });
        }

        await User.findByIdAndUpdate(userId, { lastActive: new Date() });
        res.json({ success: true });
    } catch (err) {
        next(err);
    }
};

exports.online = async (req, res, next) => {
    try {
        const activeThreshold = new Date(Date.now() - 2 * 60 * 1000);
        const onlineUsers = await User.find({
            lastActive: { $gte: activeThreshold }
        }).select('firstName lastName phone grade role lastActive');
        res.json(onlineUsers);
    } catch (err) {
        next(err);
    }
};
