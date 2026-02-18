const mysql = require('mysql2/promise');

const dbConfig = {
    host: '127.0.0.1',
    port: 3306,
    user: 'root',
    password: '',
    database: 'solitario_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
};

let pool = null;

async function initDB() {
    pool = mysql.createPool(dbConfig);
    const connection = await pool.getConnection();
    connection.release();
    return pool;
}

function getPool() {
    return pool;
}

module.exports = {
    initDB,
    getPool
};
