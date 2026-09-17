const mongoose = require('mongoose');

const connectDB = async () => {
    try {
        await mongoose.connect(process.env.MONGODB_URI);
        console.log('✅ تم الاتصال بقاعدة بيانات MongoDB بنجاح');
    } catch (err) {
        console.error('❌ خطأ في الاتصال بقاعدة البيانات:', err);
        process.exit(1);
    }
};

module.exports = connectDB;
