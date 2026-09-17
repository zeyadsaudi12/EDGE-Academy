const errorHandler = (err, req, res, next) => {
    console.error('❌ Error caught by handler:', err.stack || err);
    res.status(500).json({
        success: false,
        message: err.message || 'حدث خطأ داخلي في السيرفر'
    });
};

module.exports = errorHandler;
