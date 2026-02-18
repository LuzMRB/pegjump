const bcrypt = require('bcrypt');
const { getPool } = require('../db');

async function register(req, res) {
    try {
        const pool = getPool();
        if (!pool) {
            return res.status(500).json({ success: false, message: 'Database is not connected' });
        }

        const { nombre = '', email = '', password = '' } = req.body;
        if (!nombre || !password) {
            return res.status(400).json({ success: false, message: 'Nombre y password son obligatorios.' });
        }

        const [existing] = await pool.query(
            'SELECT id FROM usuarios WHERE nombre = ? LIMIT 1',
            [nombre.trim()]
        );
        if (existing.length > 0) {
            return res.status(409).json({ success: false, message: 'El usuario ya existe.' });
        }

        const passwordHash = await bcrypt.hash(password, 10);
        const emailValue = email.trim() || null;
        const [result] = await pool.query(
            'INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)',
            [nombre.trim(), emailValue, passwordHash]
        );

        return res.status(201).json({
            success: true,
            data: {
                id: result.insertId,
                nombre: nombre.trim(),
                email: emailValue
            }
        });
    } catch (error) {
        return res.status(500).json({ success: false, message: 'Server error', error: error.message });
    }
}

async function login(req, res) {
    try {
        const pool = getPool();
        if (!pool) {
            return res.status(500).json({ success: false, message: 'Database is not connected' });
        }

        const { nombre = '', password = '' } = req.body;
        if (!nombre || !password) {
            return res.status(400).json({ success: false, message: 'Nombre y password son obligatorios.' });
        }

        const [rows] = await pool.query(
            'SELECT id, nombre, email, password FROM usuarios WHERE nombre = ? LIMIT 1',
            [nombre.trim()]
        );
        if (rows.length === 0) {
            return res.status(401).json({ success: false, message: 'Credenciales invalidas.' });
        }

        const validPassword = await bcrypt.compare(password, rows[0].password);
        if (!validPassword) {
            return res.status(401).json({ success: false, message: 'Credenciales invalidas.' });
        }

        return res.json({
            success: true,
            data: {
                id: rows[0].id,
                nombre: rows[0].nombre,
                email: rows[0].email
            }
        });
    } catch (error) {
        return res.status(500).json({ success: false, message: 'Server error', error: error.message });
    }
}

async function logout(req, res) {
    return res.json({
        success: true,
        message: 'Sesion cerrada correctamente.'
    });
}

module.exports = {
    register,
    login,
    logout
};
