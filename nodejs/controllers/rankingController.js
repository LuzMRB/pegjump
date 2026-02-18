const bcrypt = require('bcrypt');
const { getPool } = require('../db');

function formatRankingRows(rows) {
    return rows.map((row) => {
        const minutes = Math.floor(row.tiempo_segundos / 60);
        const seconds = row.tiempo_segundos % 60;

        return {
            nombre: row.nombre,
            fichas_restantes: row.fichas_restantes,
            movimientos: row.movimientos,
            tiempo: `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`,
            puntuacion: row.puntuacion,
            fecha: row.fecha
        };
    });
}

async function getRanking(req, res) {
    try {
        const pool = getPool();
        if (!pool) {
            return res.status(500).json({ success: false, message: 'Database is not connected' });
        }

        const [rows] = await pool.query(`
            SELECT
                u.nombre,
                p.fichas_restantes,
                p.movimientos,
                p.tiempo_segundos,
                p.puntuacion,
                p.fecha
            FROM puntuaciones p
            JOIN usuarios u ON p.usuario_id = u.id
            ORDER BY p.puntuacion DESC
            LIMIT 10
        `);

        return res.json({
            success: true,
            data: formatRankingRows(rows)
        });
    } catch (error) {
        return res.status(500).json({ success: false, message: 'Server error', error: error.message });
    }
}

async function createRanking(req, res) {
    try {
        const pool = getPool();
        if (!pool) {
            return res.status(500).json({ success: false, message: 'Database is not connected' });
        }

        const {
            nombre = '',
            email = '',
            password = '',
            fichas_restantes = 14,
            movimientos = 0,
            tiempo_segundos = 0
        } = req.body;

        const errors = [];
        if (!nombre || nombre.trim().length < 2 || nombre.trim().length > 50) {
            errors.push('Nombre invalido (2-50 caracteres).');
        }
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push('Formato de email invalido.');
        }
        if (!password || password.length < 6) {
            errors.push('Contrasena invalida (minimo 6 caracteres).');
        } else if (!/[0-9]/.test(password)) {
            errors.push('La contrasena debe contener al menos un numero.');
        }

        if (errors.length > 0) {
            return res.status(400).json({
                success: false,
                message: 'Validation errors',
                errors
            });
        }

        const [users] = await pool.query(
            'SELECT id, password FROM usuarios WHERE nombre = ?',
            [nombre.trim()]
        );

        let userId;
        if (users.length > 0) {
            const validPassword = await bcrypt.compare(password, users[0].password);
            if (!validPassword) {
                return res.status(401).json({ success: false, message: 'Contrasena incorrecta.' });
            }
            userId = users[0].id;
        } else {
            const passwordHash = await bcrypt.hash(password, 10);
            const emailValue = email.trim() || null;
            const [insertResult] = await pool.query(
                'INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)',
                [nombre.trim(), emailValue, passwordHash]
            );
            userId = insertResult.insertId;
        }

        const scorePieces = (15 - fichas_restantes) * 100;
        const scoreMoves = Math.max(0, 200 - movimientos * 5);
        const scoreTime = Math.max(0, 500 - tiempo_segundos * 2);
        const totalScore = scorePieces + scoreMoves + scoreTime;

        await pool.query(
            `INSERT INTO puntuaciones (usuario_id, fichas_restantes, movimientos, tiempo_segundos, puntuacion)
             VALUES (?, ?, ?, ?, ?)`,
            [userId, fichas_restantes, movimientos, tiempo_segundos, totalScore]
        );

        return res.status(201).json({
            success: true,
            data: {
                nombre: nombre.trim(),
                puntuacion: totalScore,
                fichas_restantes,
                movimientos,
                tiempo_segundos
            }
        });
    } catch (error) {
        return res.status(500).json({ success: false, message: 'Server error', error: error.message });
    }
}

module.exports = {
    getRanking,
    createRanking
};
