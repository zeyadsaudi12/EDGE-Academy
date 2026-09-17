const express = require('express');
const router = express.Router();
const codeController = require('../controllers/code.controller');

router.post('/verify', codeController.verifyCode);
router.get('/', codeController.getAllCodes);
router.post('/generate', codeController.generateCodes);
router.put('/:id', codeController.updateCode);
router.delete('/:id', codeController.deleteCode);
router.delete('/', codeController.deleteAllCodes);

module.exports = router;
