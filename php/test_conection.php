<?php
// TEST_CONEXION.PHP — Verificar Conexión a la Base de Datos
// Script de prueba para confirmar que la conexión a MySQL
// funciona correctamente.
// NOTA: Este archivo es solo para desarrollo/pruebas.

 ?> 

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de Conexión - Solitario</title>
    <style>
        /* Estilos inline para este archivo de test */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff4cc;
            color: #333;
        }
        h1 { color: #5d4037; text-align: center; }
        .test { 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 8px; 
            border-left: 4px solid #ccc;
        }
        .exito { 
            background-color: #e8f5e9; 
            border-left-color: #4CAF50; 
        }
        .error { 
            background-color: #ffebee; 
            border-left-color: #f44336; 
        }
        .info {
            background-color: #e3f2fd;
            border-left-color: #2196F3;
        }
        code { 
            background-color: #f5f5f5; 
            padding: 2px 6px; 
            border-radius: 3px;
            font-size: 0.9em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 8px 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th { background-color: #8b5e3c; color: white; }
    </style>
</head>
<body>

<h1>🔧 Test de Conexión a la Base de Datos</h1>

<?php
// TEST 1: Conexión a MySQL

echo '<div class="test">';
echo '<h3>Test 1: Conexión a MySQL</h3>';

// Datos de conexión (mismos que en config.php)
$servidor = "localhost";
$usuario = "root";
$clave = "";
$baseDatos = "solitario_db";

// Intentar la conexión
$conexion = mysqli_connect($servidor, $usuario, $clave, $baseDatos);

if ($conexion) {
    echo '<div class="exito">';
    echo '<strong>Conexión exitosa</strong> a la base de datos <code>' . $baseDatos . '</code>';
    echo '</div>';
} else {
    echo '<div class="error">';
    echo '<strong>Error de conexión:</strong> ' . mysqli_connect_error();
    echo '<br><br>';
    echo '<strong>Posibles soluciones:</strong><br>';
    echo '1. Verifica que XAMPP/WAMP está ejecutando MySQL<br>';
    echo '2. Verifica que la base de datos <code>solitario_db</code> existe<br>';
    echo '3. Ejecuta el script <code>sql/database.sql</code> en phpMyAdmin<br>';
    echo '</div>';
    echo '</div>';
    echo '</body></html>';
    exit;
}

echo '</div>';


// TEST 2: Verificar que las tablas existen


echo '<div class="test">';
echo '<h3>Test 2: Tablas en la base de datos</h3>';

// SHOW TABLES: Muestra todas las tablas de la BD actual
$resultado = mysqli_query($conexion, "SHOW TABLES");

if ($resultado && mysqli_num_rows($resultado) > 0) {
    echo '<div class="exito">';
    echo ' <strong>Tablas encontradas:</strong><br>';
    while ($fila = mysqli_fetch_assoc($resultado)) {
        // La clave del array es "Tables_in_nombre_bd"
        $nombreTabla = array_values($fila)[0];
        echo '&nbsp;&nbsp;&nbsp;📋 <code>' . $nombreTabla . '</code><br>';
    }
    echo '</div>';
} else {
    echo '<div class="error">';
    echo '❌ No se encontraron tablas. Ejecuta <code>sql/database.sql</code> en phpMyAdmin.';
    echo '</div>';
}

echo '</div>';


// TEST 3: Verificar estructura de la tabla usuarios

echo '<div class="test">';
echo '<h3>Test 3: Estructura de la tabla <code>usuarios</code></h3>';

// DESCRIBE: Muestra la estructura de una tabla (columnas, tipos, etc.)
$resultado = mysqli_query($conexion, "DESCRIBE usuarios");

if ($resultado && mysqli_num_rows($resultado) > 0) {
    echo '<div class="exito">';
    echo '<strong>Tabla usuarios:</strong>';
    echo '<table>';
    echo '<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>';
    while ($fila = mysqli_fetch_assoc($resultado)) {
        echo '<tr>';
        echo '<td><code>' . $fila['Field'] . '</code></td>';
        echo '<td>' . $fila['Type'] . '</td>';
        echo '<td>' . $fila['Null'] . '</td>';
        echo '<td>' . $fila['Key'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
} else {
    echo '<div class="error">';
    echo ' La tabla <code>usuarios</code> no existe o está vacía.';
    echo '</div>';
}

echo '</div>';

// TEST 4: Verificar estructura de la tabla puntuaciones

echo '<div class="test">';
echo '<h3>Test 4: Estructura de la tabla <code>puntuaciones</code></h3>';

$resultado = mysqli_query($conexion, "DESCRIBE puntuaciones");

if ($resultado && mysqli_num_rows($resultado) > 0) {
    echo '<div class="exito">';
    echo '<strong>Tabla puntuaciones:</strong>';
    echo '<table>';
    echo '<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th></tr>';
    while ($fila = mysqli_fetch_assoc($resultado)) {
        echo '<tr>';
        echo '<td><code>' . $fila['Field'] . '</code></td>';
        echo '<td>' . $fila['Type'] . '</td>';
        echo '<td>' . $fila['Null'] . '</td>';
        echo '<td>' . $fila['Key'] . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
} else {
    echo '<div class="error">';
    echo ' La tabla <code>puntuaciones</code> no existe.';
    echo '</div>';
}

echo '</div>';



// TEST 5: Consulta de ejemplo (datos del ranking)


echo '<div class="test">';
echo '<h3>Test 5: Datos del ranking (SELECT con JOIN)</h3>';

$sql = "SELECT u.nombre, p.fichas_restantes, p.movimientos, p.tiempo_segundos, p.puntuacion
        FROM puntuaciones p
        JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.puntuacion DESC
        LIMIT 5";

$resultado = mysqli_query($conexion, $sql);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    echo '<div class="exito">';
    echo '<strong>Ranking actual:</strong>';
    echo '<table>';
    echo '<tr><th>Jugador</th><th>Fichas</th><th>Movimientos</th><th>Tiempo</th><th>Puntuación</th></tr>';
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $min = floor($fila['tiempo_segundos'] / 60);
        $seg = $fila['tiempo_segundos'] % 60;
        echo '<tr>';
        echo '<td>' . htmlspecialchars($fila['nombre']) . '</td>';
        echo '<td>' . $fila['fichas_restantes'] . '</td>';
        echo '<td>' . $fila['movimientos'] . '</td>';
        echo '<td>' . sprintf('%02d:%02d', $min, $seg) . '</td>';
        echo '<td><strong>' . $fila['puntuacion'] . '</strong></td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
} else {
    echo '<div class="info">';
    echo ' No hay puntuaciones todavía. ¡Juega una partida para ver datos aquí!';
    echo '</div>';
}

echo '</div>';



// RESUMEN FINAL


echo '<div class="test info">';
echo '<h3> Resumen de la configuración</h3>';
echo '<p><strong>Servidor:</strong> <code>' . $servidor . '</code></p>';
echo '<p><strong>Base de datos:</strong> <code>' . $baseDatos . '</code></p>';
echo '<p><strong>Versión MySQL:</strong> <code>' . mysqli_get_server_info($conexion) . '</code></p>';
echo '<p><strong>Charset:</strong> <code>' . mysqli_character_set_name($conexion) . '</code></p>';
echo '</div>';

// Cerrar la conexión
mysqli_close($conexion);
?>

</body>
</html>
