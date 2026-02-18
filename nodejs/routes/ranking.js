const express = require('express');
const { getRanking, createRanking } = require('../controllers/rankingController');

const router = express.Router();

router.get('/', getRanking);
router.post('/', createRanking);

module.exports = router;
