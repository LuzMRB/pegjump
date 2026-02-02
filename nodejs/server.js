// SERVER.JS  Servidor Express.js con MySQL

// Este archivo REIMPLEMENTA las funcionalidades del backend PHP
// usando Node.js con Express.js y MySQL.

// EQUIVALENCIAS PHP -> NODE.JS:
//   php/process.php      -> POST /api/puntuacion
//   php/get_ranking.php   -> GET  /api/ranking
//   php/config.php        -> Configuracion de MySQL aqui mismo

// COMO EJECUTAR:
//   1. Abrir terminal en la carpeta nodejs/
//   2. Ejecutar: npm install  (instala express, mysql2, cors, bcrypt)
//   3. Ejecutar: node server.js
//   4. Abrir navegador en: http://127.0.0.1:3000
//   5. Para parar el servidor: Ctrl + C

// IMPORTANTE: XAMPP (MySQL) debe estar encendido para que funcione
//             la conexion a la base de datos



// 1. IMPORTAR MODULOS (equivalente a require_once en PHP)

// require() en Node.js carga modulos:
//   - Modulos de npm (express, mysql2): Se instalan con npm install
//   - Modulos nativos (path): Vienen con Node.js

// En PHP usamos: require_once 'config.php';
// En Node.js:   const express = require('express');

const express = require('express');       // Framework web (como Laravel para PHP)
const mysql = require('mysql2/promise');   // Conector MySQL (como mysqli en PHP)
const cors = require('cors');             // Permite peticiones desde otro origen
const bcrypt = require('bcrypt');         // Hash de contrasenas (como password_hash en PHP)
const path = require('path');             // Manejo de rutas de archivos (modulo nativo)


// 2. CREAR LA APLICACION EXPRESS

// express() crea una aplicacion web.
// Es como tener Apache + PHP pero todo en JavaScript.

// Express usa el patron de "middleware":
//   Cada peticion pasa por una cadena de funciones
//   antes de llegar a la ruta final.

const app = express();
const PUERTO = 3000;


// 3. CONFIGURAR MIDDLEWARE


// Middleware: Funciones que procesan la peticion ANTES de llegar a la ruta.
// app.use() registra middleware que se aplica a TODAS las peticiones.

// express.json(): Parsea el cuerpo de peticiones POST con formato JSON
// Es el equivalente a json_decode(file_get_contents('php://input')) en PHP
app.use(express.json());

// cors(): Permite que el frontend (en puerto 80) haga peticiones al backend (puerto 3000)
// Sin esto, el navegador bloquea las peticiones por politica de mismo origen
app.use(cors());

// express.static(): Sirve archivos estaticos (HTML, CSS, JS, imagenes)
// Esto permite que Node.js sirva el frontend directamente
// Es opcional si ya usamos Apache/XAMPP para el frontend
app.use(express.static(path.join(__dirname, '..', 'public')));


// 4. CONFIGURACION DE BASE DE DATOS


// En PHP tenemos config.php con:
//   $servidor = "127.0.0.1";
//   $usuario = "root";
//   $conexion = mysqli_connect(...)

// En Node.js usamos mysql2 con un "pool" de conexiones.
// Un pool reutiliza conexiones en vez de crear una nueva cada vez.
// Esto es mas eficiente que mysqli_connect() + mysqli_close().

// IMPORTANTE: Cambiar estos datos si tu configuracion MySQL es diferente

const dbConfig = {
    host: '127.0.0.1',       // Igual que $servidor en config.php
    port: 3306,               // Puerto MySQL (igual que en config.php)
    user: 'root',             // Igual que $usuario en config.php
    password: '',             // Igual que $clave en config.php (vacio en XAMPP)
    database: 'solitario_db', // Igual que $baseDatos en config.php
    waitForConnections: true,
    connectionLimit: 10,      // Maximo 10 conexiones simultaneas
    queueLimit: 0
};

let pool;

// Funcion para inicializar el pool de conexiones
async function inicializarDB() {
    try {
        pool = mysql.createPool(dbConfig);
        // Probar la conexion
        const conexion = await pool.getConnection();
        console.log('Conexion a MySQL establecida correctamente');
        conexion.release(); // Devolver la conexion al pool
    } catch (error) {
        console.error('Error de conexion a MySQL:', error.message);
        console.error('Asegurate de que XAMPP/MySQL esta encendido');
        // El servidor arranca igual, pero las rutas de BD daran error
    }
}



// 5. RUTAS (ENDPOINTS DE LA API)


// En PHP, cada archivo es una ruta
// En Express, definimos las rutas con:
//   app.get('/ruta', (req, res) => { ... })   para peticiones GET
//   app.post('/ruta', (req, res) => { ... })  para peticiones POST

// req = request (peticion del cliente)
// res = response (respuesta al cliente)
// Son los mismos conceptos que en el servidor basico (UD5 seccion 4.1)


// ---- RUTA: Pagina principal ----
app.get('/', (req, res) => {
    res.send(`
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Solitario - API Node.js + Express</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 700px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
                h1 { color: #5d4037; }
                .info { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 15px 0; }
                code { background: #e8e8e8; padding: 2px 6px; border-radius: 3px; }
                .exito { color: #2e7d32; font-weight: bold; }
                a { color: #8b5e3c; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #ddd; }
                th { background: #5d4037; color: white; }
            </style>
        </head>
        <body>
            <h1>Solitario Triangular - API Node.js</h1>
            <p class="exito">Servidor Express.js + MySQL funcionando!</p>

            <div class="info">
                <h3>Endpoints de la API:</h3>
                <table>
                    <tr><th>Metodo</th><th>Ruta Node.js</th><th>Equivalente PHP</th></tr>
                    <tr><td>GET</td><td><a href="/api/estado">/api/estado</a></td><td>-</td></tr>
                    <tr><td>GET</td><td><a href="/api/ranking">/api/ranking</a></td><td>get_ranking.php</td></tr>
                    <tr><td>POST</td><td>/api/puntuacion</td><td>process.php</td></tr>
                </table>
            </div>

            <div class="info">
                <h3>Por que Node.js ademas de PHP?</h3>
                <p>PHP: Requiere Apache/XAMPP para ejecutar. Cada peticion crea un nuevo proceso.</p>
                <p>Node.js: Es su propio servidor. Maneja multiples peticiones en un solo proceso (event loop).</p>
                <p>Ambos conectan a la MISMA base de datos MySQL <code>solitario_db</code>.</p>
            </div>
        </body>
        </html>
    `);
});


// ---- RUTA: Estado del servidor (GET /api/estado) ----
app.get('/api/estado', async (req, res) => {
    let dbEstado = 'Desconectado';

    try {
        if (pool) {
            const [rows] = await pool.query('SELECT 1');
            dbEstado = 'Conectado';
        }
    } catch (error) {
        dbEstado = 'Error: ' + error.message;
    }

    res.json({
        exito: true,
        servidor: 'Express.js + Node.js ' + process.version,
        baseDatos: dbEstado,
        puerto: PUERTO,
        fecha: new Date().toISOString()
    });
});


// RUTA: Obtener Ranking (GET /api/ranking) 
// REIMPLEMENTA: php/get_ranking.php

// En PHP:
//   $sql = "SELECT ... FROM puntuaciones p JOIN usuarios u ...";
//   $resultado = mysqli_query($conexion, $sql);
//   echo json_encode(['ranking' => $filas]);

// En Node.js (Express + mysql2):
//   const [filas] = await pool.query("SELECT ... FROM puntuaciones ...");
//   res.json({ ranking: filas });

// La consulta SQL es IDENTICA. Solo cambia el lenguaje que la ejecuta.

app.get('/api/ranking', async (req, res) => {
    try {
        // Verificar conexion a BD
        if (!pool) {
            return res.status(500).json({
                exito: false,
                mensaje: 'No hay conexion a la base de datos'
            });
        }

        // La misma consulta SQL que en get_ranking.php
        // pool.query() es el equivalente a mysqli_query()
        const [filas] = await pool.query(`
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

        // Formatear resultados (igual que en la clase Puntuacion PHP)
        const ranking = filas.map(fila => {
            const minutos = Math.floor(fila.tiempo_segundos / 60);
            const segundos = fila.tiempo_segundos % 60;
            return {
                nombre: fila.nombre,
                fichas_restantes: fila.fichas_restantes,
                movimientos: fila.movimientos,
                tiempo: `${String(minutos).padStart(2, '0')}:${String(segundos).padStart(2, '0')}`,
                puntuacion: fila.puntuacion,
                fecha: fila.fecha
            };
        });

        // res.json() es el equivalente a echo json_encode() en PHP
        res.json({ exito: true, ranking: ranking });

    } catch (error) {
        console.error('Error al obtener ranking:', error);
        res.status(500).json({ exito: false, mensaje: 'Error del servidor' });
    }
});


// RUTA: Guardar Puntuacion (POST /api/puntuacion)

// REIMPLEMENTA: php/process.php

// En PHP:
//   $datos = json_decode(file_get_contents('php://input'), true);
//   $nombre = $datos['nombre'];
//   $hash = password_hash($password, PASSWORD_DEFAULT);
//   ...
//   echo json_encode(['exito' => true]);
//
// En Node.js (Express):
//   const { nombre, password } = req.body;  // Express ya parsea el JSON
//   const hash = await bcrypt.hash(password, 10);

//   res.json({ exito: true });

app.post('/api/puntuacion', async (req, res) => {
    try {
        if (!pool) {
            return res.status(500).json({
                exito: false,
                mensaje: 'No hay conexion a la base de datos'
            });
        }

        // PASO 1: Extraer datos del cuerpo de la peticion
        // En PHP: $datos['nombre'], $datos['email'], etc.
        // En Express: req.body.nombre, req.body.email (ya parseado por express.json())
        const {
            nombre = '',
            email = '',
            password = '',
            fichas_restantes = 14,
            movimientos = 0,
            tiempo_segundos = 0
        } = req.body;

        // PASO 2: Validacion en el servidor (igual que en PHP)
        const errores = [];

        if (!nombre || nombre.trim().length < 2 || nombre.trim().length > 50) {
            errores.push('Nombre invalido (2-50 caracteres).');
        }
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errores.push('Formato de email invalido.');
        }
        if (!password || password.length < 6) {
            errores.push('Contrasena invalida (minimo 6 caracteres).');
        } else if (!/[0-9]/.test(password)) {
            errores.push('La contrasena debe contener al menos un numero.');
        }

        if (errores.length > 0) {
            return res.status(400).json({
                exito: false,
                mensaje: 'Errores de validacion.',
                errores: errores
            });
        }

        // PASO 3: Buscar o crear usuario
        // pool.query() con ? usa consultas preparadas (previene SQL injection)
        // Es el equivalente a mysqli_prepare() + bind_param() en PHP
        const [usuarios] = await pool.query(
            'SELECT id, nombre, password FROM usuarios WHERE nombre = ?',
            [nombre.trim()]
        );

        let usuarioId;

        if (usuarios.length > 0) {
            // Usuario existe: verificar contrasena
            // bcrypt.compare() es el equivalente a password_verify() en PHP
            const passwordValida = await bcrypt.compare(password, usuarios[0].password);

            if (!passwordValida) {
                return res.status(401).json({
                    exito: false,
                    mensaje: 'Contrasena incorrecta.'
                });
            }
            usuarioId = usuarios[0].id;

        } else {
            // Crear usuario nuevo
            // bcrypt.hash() es el equivalente a password_hash() en PHP
            // El 10 es el "salt rounds" (similar a PASSWORD_DEFAULT en PHP)
            const passwordHash = await bcrypt.hash(password, 10);
            const emailParaBD = email.trim() || null;

            const [resultado] = await pool.query(
                'INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)',
                [nombre.trim(), emailParaBD, passwordHash]
            );

            // resultado.insertId es el equivalente a mysqli_insert_id() en PHP
            usuarioId = resultado.insertId;
        }

        // PASO 4: Calcular y guardar puntuacion
        // La misma formula que en la clase Puntuacion de PHP
        const puntosFichas = (15 - fichas_restantes) * 100;
        const puntosMovimientos = Math.max(0, 200 - movimientos * 5);
        const puntosTiempo = Math.max(0, 500 - tiempo_segundos * 2);
        const puntuacionTotal = puntosFichas + puntosMovimientos + puntosTiempo;

        await pool.query(
            `INSERT INTO puntuaciones (usuario_id, fichas_restantes, movimientos, tiempo_segundos, puntuacion)
             VALUES (?, ?, ?, ?, ?)`,
            [usuarioId, fichas_restantes, movimientos, tiempo_segundos, puntuacionTotal]
        );

        // PASO 5: Enviar respuesta JSON
        res.json({
            exito: true,
            mensaje: 'Puntuacion guardada correctamente!',
            datos: {
                nombre: nombre.trim(),
                puntuacion: puntuacionTotal,
                fichas_restantes: fichas_restantes,
                movimientos: movimientos,
                tiempo_segundos: tiempo_segundos
            }
        });

    } catch (error) {
        console.error('Error al guardar puntuacion:', error);
        res.status(500).json({ exito: false, mensaje: 'Error del servidor.' });
    }
});



// 6. INICIAR EL SERVIDOR

// app.listen() es el equivalente a server.listen() del servidor basico
// Pero Express maneja las rutas internamente de forma mas organizada

async function iniciar() {
    // Primero intentar conectar a MySQL
    await inicializarDB();

    // Luego arrancar el servidor Express
    app.listen(PUERTO, '127.0.0.1', () => {
        console.log('');

        console.log(' SOLITARIO - Servidor Express.js + MySQL');

        console.log(`Servidor escuchando en http://127.0.0.1:${PUERTO}/`);
        console.log('');
        console.log('Endpoints API:');
        console.log(`  GET  http://127.0.0.1:${PUERTO}/api/estado`);
        console.log(`  GET  http://127.0.0.1:${PUERTO}/api/ranking`);
        console.log(`  POST http://127.0.0.1:${PUERTO}/api/puntuacion`);
        console.log('');
        console.log('Para parar el servidor: Ctrl + C');

    });
}

// Ejecutar la funcion de inicio
iniciar();