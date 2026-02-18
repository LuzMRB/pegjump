const { getPool } = require('../db');

async function health(req, res) {
    let dbStatus = 'disconnected';

    try {
        const pool = getPool();
        if (pool) {
            await pool.query('SELECT 1');
            dbStatus = 'connected';
        }
    } catch (error) {
        dbStatus = `error: ${error.message}`;
    }

    return res.json({
        success: true,
        data: {
            ok: true,
            status: 'healthy',
            service: 'solitario-backend-nodejs',
            dbStatus,
            uptimeSeconds: Math.round(process.uptime()),
            timestamp: new Date().toISOString()
        }
    });
}

module.exports = {
    health
};
