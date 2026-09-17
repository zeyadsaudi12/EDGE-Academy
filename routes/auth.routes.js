const express = require('express');
const router = express.Router();
const authController = require('../controllers/auth.controller');

router.post('/register', authController.register);
router.post('/login', authController.login);
router.post('/users/ping', authController.ping);
router.get('/users/online', authController.online);

module.exports = router;
