<?php
// GET_RANKING.PHP — Obtener Ranking de Jugadores
// Este script consulta la base de datos y devuelve el ranking
// de las mejores puntuaciones en formato JSON.
// CONCEPTOS  APLICADOS:
//   - SELECT con JOIN: Combina datos de dos tablas 
//   - ORDER BY: Ordena los resultados 
//   - LIMIT: Limita el número de resultados
//   - mysqli_query(): Ejecuta una consulta
//   - mysqli_fetch_assoc(): Obtiene filas como arrays
//   - json_encode(): Convierte datos PHP a JSON
//
// FLUJO:
//   1. Conectar a la BD
//   2. Ejecutar consulta SELECT con JOIN
//   3. Recorrer los resultados con un bucle while
//   4. Devolver los datos en formato JSON


// Cabecera: la respuesta es JSON
header('Content-Type: application/json');

// Incluir configuración de la BD
require_once __DIR__ . '/config.php';


// 1. CONSULTA SQL CON JOIN

// Aquí combinamos:
//   - puntuaciones (p): tiene los datos de la partida
//   - usuarios (u): tiene el nombre del jugador
//
// La relación es: p.usuario_id = u.id
//
// ORDER BY p.puntuacion DESC: Ordena de mayor a menor puntuación
// LIMIT 10: Solo devuelve los 10 mejores


$sql = "SELECT 
            u.nombre,
            p.fichas_restantes,
            p.movimientos,
            p.tiempo_segundos,
            p.puntuacion,
            p.fecha
        FROM puntuaciones p
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.puntuacion DESC
        LIMIT 10";

// mysqli_query(): Ejecuta la consulta SQL 
// Recibe la conexión y la sentencia SQL
$resultado = mysqli_query($conexion, $sql);



// 2. VERIFICAR QUE LA CONSULTA FUE EXITOSA
// mysqli_error(): Devuelve el error de la última operación 

if (!$resultado) {
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error al obtener el ranking.',
        'error' => mysqli_error($conexion) // Solo en desarrollo
    ]);
    exit;
}


// 3. RECORRER LOS RESULTADOS

// mysqli_fetch_assoc(): Devuelve una fila como array asociativo.
// Cada columna se convierte en una clave del array.

// Se usa dentro de un bucle while porque cada llamada a
// mysqli_fetch_assoc() devuelve la SIGUIENTE fila.
// Cuando no quedan más filas, devuelve NULL y el bucle termina.

// Ejemplo de una fila devuelta:
// $fila = [
//     'nombre' => 'Luz',
//     'fichas_restantes' => 1,
//     'movimientos' => 13,
//     'tiempo_segundos' => 150,
//     'puntuacion' => 1850,
//     'fecha' => '2025-06-01 15:30:00'


$ranking = [];

// Bucle while: se ejecuta mientras haya filas por leer
while ($fila = mysqli_fetch_assoc($resultado)) {
    // Formateamos el tiempo de segundos a MM:SS para el frontend
    $minutos = floor($fila['tiempo_segundos'] / 60);
    $segundos = $fila['tiempo_segundos'] % 60;
    $tiempoFormateado = sprintf('%02d:%02d', $minutos, $segundos);

    // Añadimos la fila al array de ranking
    $ranking[] = [
        'nombre' => $fila['nombre'],
        'fichas_restantes' => intval($fila['fichas_restantes']),
        'movimientos' => intval($fila['movimientos']),
        'tiempo' => $tiempoFormateado,
        'puntuacion' => intval($fila['puntuacion']),
        'fecha' => $fila['fecha']
    ];
}



// 4. DEVOLVER EL RANKING EN FORMATO JSON

// json_encode(): Convierte el array PHP a una cadena JSON.
// El frontend lo recibirá con fetch() y lo pintará en la tabla.


echo json_encode([
    'exito' => true,
    'ranking' => $ranking
]);



// 5. LIBERAR RECURSOS Y CERRAR CONEXIÓN

// mysqli_free_result(): Libera la memoria del resultado
// mysqli_close(): Cierra la conexión con la BD


mysqli_free_result($resultado);
mysqli_close($conexion);

?>