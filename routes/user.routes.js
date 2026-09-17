const express = require('express');
const router = express.Router();
const userController = require('../controllers/user.controller');

router.post('/:id/follow', userController.follow);
router.post('/:id/subscribe', userController.subscribe);
router.get('/', userController.getAllUsers);
router.get('/:id', userController.getUserById);
router.delete('/:id', userController.deleteUser);
router.put('/:id', userController.updateUser);

module.exports = router;
