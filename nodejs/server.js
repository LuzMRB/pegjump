const express = require('express');
const cors = require('cors');

const { initDB } = require('./db');
const healthRoutes = require('./routes/health');
const rankingRoutes = require('./routes/ranking');
const authRoutes = require('./routes/auth');
const { health } = require('./controllers/healthController');
const { createRanking } = require('./controllers/rankingController');

const app = express();
const PORT = 3000;

app.use(cors());
app.use(express.json());

app.get('/', (req, res) => {
    res.json({
        success: true,
        service: 'solitario-backend-nodejs',
        message: 'API running',
        routes: {
            health: 'GET /api/health',
            rankingList: 'GET /api/ranking',
            rankingCreate: 'POST /api/ranking',
            authRegister: 'POST /api/auth/register',
            authLogin: 'POST /api/auth/login',
            authLogout: 'POST /api/auth/logout'
        }
    });
});

// Main routing map
app.use('/api/health', healthRoutes);
app.use('/api/ranking', rankingRoutes);
app.use('/api/auth', authRoutes);

// Backward-compatible aliases from previous API
app.get('/api/estado', health);
app.post('/api/puntuacion', createRanking);

app.use((req, res) => {
    res.status(404).json({
        success: false,
        message: 'Route not found'
    });
});

async function startServer() {
    try {
        await initDB();
        console.log('MySQL connected');
    } catch (error) {
        console.error(`MySQL connection failed: ${error.message}`);
        console.error('Server will run, but DB endpoints may fail.');
    }

    app.listen(PORT, '127.0.0.1', () => {
        console.log('');
        console.log('SOLITARIO - Node.js Routing API');
        console.log(`Server: http://127.0.0.1:${PORT}/`);
        console.log('Routes:');
        console.log(`  GET  http://127.0.0.1:${PORT}/api/health`);
        console.log(`  GET  http://127.0.0.1:${PORT}/api/ranking`);
        console.log(`  POST http://127.0.0.1:${PORT}/api/ranking`);
        console.log(`  POST http://127.0.0.1:${PORT}/api/auth/register`);
        console.log(`  POST http://127.0.0.1:${PORT}/api/auth/login`);
        console.log(`  POST http://127.0.0.1:${PORT}/api/auth/logout`);
        console.log('');
    });
}

startServer();