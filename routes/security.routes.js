const express = require('express');
const router = express.Router();
const securityController = require('../controllers/security.controller');

router.post('/report', securityController.report);
router.get('/reports', securityController.reports);
router.delete('/reports', securityController.deleteAll);
router.post('/unlock', securityController.unlock);
router.get('/check-lock', securityController.checkLock);

module.exports = router;
