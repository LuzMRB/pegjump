// SERVER_BASICO.JS - Servidor HTTP Basico con Node.js

// Este archivo demuestra como crear un servidor web basico
// usando SOLO el modulo http nativo de Node.js (sin Express).

// Es el mismo concepto que el ejemplo de la UD5:
//   const http = require('http');
//   const server = http.createServer((req, res) => { ... });
//   server.listen(3000, '127.0.0.1', () => { ... });

// COMO EJECUTAR:
//   1. Abrir terminal en la carpeta nodejs/
//   2. Ejecutar: node server_basico.js
//   3. Abrir navegador en: http://127.0.0.1:3000
//   4. Para parar el servidor: Ctrl + C
//
// NOTA: Este servidor NO necesita npm install (usa modulos nativos)



// PASO 1: Importar el modulo http

// require('http') carga el modulo HTTP nativo de Node.js
// No necesita instalacion, viene incluido con Node.js
// Es similar a "include" o "require" en PHP

const http = require('http');



// PASO 2: Definir el puerto y host

//
// Puerto 3000: Donde el servidor escuchara peticiones
// Host 127.0.0.1: Solo accesible desde nuestra maquina (localhost)

// IMPORTANTE: Usamos puerto 3000 para NO chocar con Apache/XAMPP
// que ya usa el puerto 80 para PHP

const PUERTO = 3000;
const HOST = '127.0.0.1';


// PASO 3: Crear el servidor con createServer()

// http.createServer() recibe una FUNCION FLECHA (=>) como argumento
// (UD3 seccion 4.3: Funciones flecha en JavaScript)

// Esta funcion se ejecuta CADA VEZ que un cliente hace una peticion.

// Parametros:
//   req (request): Objeto con info de la peticion del cliente
//     - req.url: La URL solicitada (ej: '/', '/api/ranking')
//     - req.method: El metodo HTTP (GET, POST, etc.)
//     - req.on('data'): Para leer datos enviados por POST

//   res (response): Objeto para enviar la respuesta al cliente
//     - res.writeHead(): Establece cabeceras HTTP
//     - res.end(): Envia el cuerpo de la respuesta y termina

const server = http.createServer((req, res) => {

    // Obtener la URL y el metodo de la peticion
    const url = req.url;
    const metodo = req.method;

    // Mostrar en consola cada peticion recibida (para debugging)
    console.log(`[${metodo}] ${url}`);


    // RUTAS: Respondemos diferente segun la URL solicitada


    // RUTA 1: Pagina principal (/)
    if (url === '/' && metodo === 'GET') {
        // writeHead(codigo, cabeceras)
        //   200 = OK (peticion exitosa)
        //   Content-Type: Tipo de contenido que enviamos
        res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });

        // end() envia el cuerpo de la respuesta
        // Aqui enviamos HTML basico
        res.end(`
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Solitario - Servidor Node.js</title>
                <style>
                    body { font-family: Arial, sans-serif; max-width: 700px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
                    h1 { color: #5d4037; }
                    .info { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 15px 0; }
                    code { background: #e8e8e8; padding: 2px 6px; border-radius: 3px; font-size: 14px; }
                    .exito { color: #2e7d32; font-weight: bold; }
                    a { color: #8b5e3c; }
                </style>
            </head>
            <body>
                <h1>Solitario Triangular - Node.js</h1>
                <p class="exito">El servidor Node.js esta funcionando correctamente!</p>

                <div class="info">
                    <h3>Informacion del servidor:</h3>
                    <p>Host: <code>${HOST}</code></p>
                    <p>Puerto: <code>${PUERTO}</code></p>
                    <p>Fecha/hora del servidor: <code>${new Date().toLocaleString('es-ES')}</code></p>
                    <p>Version de Node.js: <code>${process.version}</code></p>
                </div>

                <div class="info">
                    <h3>Rutas disponibles:</h3>
                    <p><a href="/">GET /</a> - Esta pagina</p>
                    <p><a href="/api/estado">GET /api/estado</a> - Estado del servidor (JSON)</p>
                    <p><a href="/api/ranking">GET /api/ranking</a> - Ranking (ejemplo JSON)</p>
                    <p>POST /api/puntuacion - Guardar puntuacion (requiere enviar datos)</p>
                </div>

                <div class="info">
                    <h3>Comparacion PHP vs Node.js:</h3>
                    <p>PHP (XAMPP): <code>http://localhost/Full_stack_proyect/</code> (puerto 80)</p>
                    <p>Node.js: <code>http://127.0.0.1:${PUERTO}/</code> (puerto ${PUERTO})</p>
                    <p>Ambos servidores pueden funcionar al mismo tiempo sin conflicto.</p>
                </div>
            </body>
            </html>
        `);
    }


    // RUTA 2: API estado del servidor (JSON)
    else if (url === '/api/estado' && metodo === 'GET') {
        // Devolvemos JSON (como hace PHP con json_encode)
        res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });

        // JSON.stringify() es el equivalente a json_encode() de PHP
        const respuesta = {
            exito: true,
            mensaje: 'Servidor Node.js funcionando',
            servidor: 'Node.js ' + process.version,
            fecha: new Date().toISOString(),
            puerto: PUERTO
        };

        res.end(JSON.stringify(respuesta, null, 2));
    }


    // RUTA 3: API ranking de ejemplo (GET)
    else if (url === '/api/ranking' && metodo === 'GET') {
        res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });

        // Datos de ejemplo (sin BD - solo para demostrar la estructura)
        // En server.js usaremos Express + MySQL para datos reales
        const ranking = {
            exito: true,
            mensaje: 'Ranking de ejemplo (servidor basico sin BD)',
            ranking: [
                { nombre: 'Ejemplo1', puntuacion: 1500, fichas_restantes: 1, movimientos: 13, tiempo: '02:30' },
                { nombre: 'Ejemplo2', puntuacion: 1200, fichas_restantes: 2, movimientos: 15, tiempo: '03:10' },
                { nombre: 'Ejemplo3', puntuacion: 900, fichas_restantes: 3, movimientos: 18, tiempo: '04:00' }
            ]
        };

        res.end(JSON.stringify(ranking, null, 2));
    }


    // RUTA 4: API guardar puntuacion (POST)
    // Demuestra como leer datos POST con req.on('data') y req.on('end')
    // (UD5 seccion 4.1 - segundo ejemplo)
    else if (url === '/api/puntuacion' && metodo === 'POST') {
        let cuerpo = '';

        // req.on('data'): Se ejecuta cuando llegan datos del cliente
        // Los datos pueden llegar en trozos (chunks), asi que los concatenamos
        req.on('data', (chunk) => {
            cuerpo += chunk.toString();
        });

        // req.on('end'): Se ejecuta cuando ya llegaron TODOS los datos
        req.on('end', () => {
            try {
                // JSON.parse() es el equivalente a json_decode() de PHP
                const datos = JSON.parse(cuerpo);

                console.log('Datos recibidos:', datos);

                res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });
                res.end(JSON.stringify({
                    exito: true,
                    mensaje: 'Datos recibidos correctamente (servidor basico sin BD)',
                    datos_recibidos: datos
                }, null, 2));

            } catch (error) {
                res.writeHead(400, { 'Content-Type': 'application/json; charset=utf-8' });
                res.end(JSON.stringify({
                    exito: false,
                    mensaje: 'Error: formato JSON invalido'
                }));
            }
        });
    }


    // RUTA POR DEFECTO: 404 No encontrado
    else {
        res.writeHead(404, { 'Content-Type': 'application/json; charset=utf-8' });
        res.end(JSON.stringify({
            exito: false,
            mensaje: `Ruta no encontrada: ${metodo} ${url}`,
            rutas_disponibles: ['GET /', 'GET /api/estado', 'GET /api/ranking', 'POST /api/puntuacion']
        }, null, 2));
    }
});



// PASO 4: Poner el servidor a escuchar

// server.listen(puerto, host, callback)
//   - puerto: Donde escuchar (3000)
//   - host: Desde donde aceptar conexiones ('127.0.0.1' = solo local)
//   - callback: Funcion que se ejecuta cuando el servidor arranca

// Una vez ejecutado, el servidor queda funcionando hasta que
// lo paremos con Ctrl+C en la terminal

server.listen(PUERTO, HOST, () => {

    console.log(' SOLITARIO - Servidor Node.js Basico');

    console.log(`Servidor escuchando en http://${HOST}:${PUERTO}/`);
    console.log('');
    console.log('Rutas disponibles:');
    console.log(`  GET  http://${HOST}:${PUERTO}/`);
    console.log(`  GET  http://${HOST}:${PUERTO}/api/estado`);
    console.log(`  GET  http://${HOST}:${PUERTO}/api/ranking`);
    console.log(`  POST http://${HOST}:${PUERTO}/api/puntuacion`);
    console.log('');
    console.log('Para parar el servidor: Ctrl + C');

});
