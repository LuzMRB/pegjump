<?php
// PROCESS.PHP — Procesamiento del Formulario
// Este script recibe los datos del formulario de puntuación
// enviados desde el frontend (JavaScript con fetch API).
// CONCEPTOS APLICADOS:
//   - Variables superglobales: $_POST, $_SERVER 
//   - Conexión a BD: mysqli_connect() 
//   - Consultas preparadas: Para prevenir inyección SQL 
//   - Funciones: Organizamos el código en funciones 
//   - password_hash(): Para hashear contraseñas de forma segura
//   - JSON: Formato de intercambio de datos con el frontend
//
// FLUJO DEL SCRIPT:
//   1. Recibir datos del formulario (POST)
//   2. Validar los datos en el servidor (NUNCA confiar solo en el cliente)
//   3. Hashear la contraseña
//   4. Comprobar si el usuario ya existe
//   5. Si no existe --> crear usuario nuevo (INSERT)
//   6. Guardar la puntuación (INSERT)
//   7. Devolver respuesta JSON al frontend

// 0. CABECERAS HTTP
//
// header(): Envía cabeceras HTTP al navegador.
// Content-Type: application/json: Le dice al navegador que
// la respuesta es JSON (no HTML).


header('Content-Type: application/json');


// 1. INCLUIR LA CONFIGURACIÓN DE LA BASE DE DATOS
//
// require_once: Incluye y ejecuta el archivo especificado.
// Si el archivo no existe, DETIENE la ejecución (error fatal).
// 'once' asegura que solo se incluye una vez aunque se llame varias.

// __DIR__ es una constante mágica de PHP que devuelve
// el directorio del archivo actual.

require_once __DIR__ . '/config.php';


// 2. VERIFICAR QUE EL MÉTODO ES POST
//
// $_SERVER: Variable superglobal que contiene información
// sobre el servidor y la petición HTTP 
//
// $_SERVER['REQUEST_METHOD']: Contiene el método HTTP
// usado (GET, POST, PUT, DELETE...).
//
// Solo aceptamos POST porque estamos recibiendo datos
// sensibles (contraseña) desde un formulario.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Si alguien intenta acceder con GET (poniendo la URL en el navegador)
    // devolvemos un error 405 (Método no permitido)
    http_response_code(405);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Método no permitido. Use POST.'
    ]);
    exit; // Detener la ejecución
}


// 3. RECIBIR Y LEER LOS DATOS DEL FORMULARIO
// Como enviamos los datos con fetch() en formato JSON,
// NO usamos $_POST directamente. En su lugar:
//
// file_get_contents('php://input'): Lee el cuerpo de la
// petición HTTP (los datos JSON que envía fetch).
//
// json_decode(): Convierte una cadena JSON en un objeto PHP.
// El segundo parámetro 'true' hace que devuelva un array
// asociativo en vez de un objeto.
$datosJSON = file_get_contents('php://input');
$datos = json_decode($datosJSON, true);

// Verificar que los datos se recibieron correctamente
if ($datos === null) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error al recibir los datos. Formato JSON inválido.'
    ]);
    exit;
}


// 4. EXTRAER LOS DATOS DEL ARRAY
//
// Operador ?? (null coalescing operator):
// Si la clave existe, usa su valor. Si no, usa el valor por defecto.
// Ejemplo: $datos['nombre'] ?? '': Si 'nombre' existe, lo usa;
//          si no, devuelve cadena vacía.
//
// trim(): Elimina espacios en blanco al inicio y final de la cadena.

$nombre = trim($datos['nombre'] ?? '');
$email = trim($datos['email'] ?? '');
$password = $datos['password'] ?? '';
$fichasRestantes = intval($datos['fichas_restantes'] ?? 14);
$movimientos_partida = intval($datos['movimientos'] ?? 0);
$tiempoSegundos = intval($datos['tiempo_segundos'] ?? 0);


// 5. VALIDACIÓN EN EL SERVIDOR
// IMPORTANTE (UD5 §3.6 - Seguridad):
// NUNCA confiar solo en la validación del cliente (JavaScript).
// El usuario podría desactivar JavaScript o modificar los datos.
// SIEMPRE validar también en el servidor.
//
// Funciones de validación de cadenas en PHP:
// - strlen(): Devuelve la longitud de una cadena
// - empty(): Comprueba si una variable está vacía
// - filter_var(): Valida/filtra una variable (ej: formato email)
// - preg_match(): Comprueba si una cadena cumple una expresión regular


// Array para almacenar errores de validación
$errores = [];

// Validar nombre (obligatorio, entre 2 y 50 caracteres)
if (empty($nombre)) {
    $errores[] = 'El nombre es obligatorio.';
} elseif (strlen($nombre) < 2) {
    $errores[] = 'El nombre debe tener al menos 2 caracteres.';
} elseif (strlen($nombre) > 50) {
    $errores[] = 'El nombre no puede superar los 50 caracteres.';
}

// Validar email (opcional, pero si se proporciona debe ser válido)
// filter_var() con FILTER_VALIDATE_EMAIL verifica el formato del email
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El formato del email no es válido.';
}

// Validar contraseña (obligatoria, mínimo 6 caracteres, al menos 1 número)
if (empty($password)) {
    $errores[] = 'La contraseña es obligatoria.';
} elseif (strlen($password) < 6) {
    $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
} elseif (!preg_match('/[0-9]/', $password)) {
    // preg_match(): Busca un patrón (regex) en una cadena
    // /[0-9]/: Busca al menos un dígito
    $errores[] = 'La contraseña debe contener al menos un número.';
}

// Si hay errores de validación, devolverlos al frontend
if (!empty($errores)) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Errores de validación.',
        'errores' => $errores
    ]);
    exit;
}


// 6. HASHEAR LA CONTRASEÑA

// password_hash(): Función de PHP que genera un hash seguro
// de la contraseña usando el algoritmo bcrypt.

// PASSWORD_DEFAULT: Usa el mejor algoritmo disponible.
// Actualmente es bcrypt, pero puede cambiar en futuras versiones.


$passwordHash = password_hash($password, PASSWORD_DEFAULT);


// 7. COMPROBAR SI EL USUARIO YA EXISTE
//
// Usamos una CONSULTA PREPARADA (prepared statement)
// para prevenir la inyección SQL 

// Pasos de una consulta preparada:
//   1. mysqli_prepare(): Prepara la consulta con marcadores 
//   2. mysqli_stmt_bind_param(): Asocia valores a los marcadores
//   3. mysqli_stmt_execute(): Ejecuta la consulta
//   4. mysqli_stmt_get_result(): Obtiene el resultado
//
// El marcador '?' indica dónde irá el valor real.
// 's' en bind_param indica que el parámetro es un String.

$sqlBuscar = "SELECT id, password FROM usuarios WHERE nombre = ?";
$stmtBuscar = mysqli_prepare($conexion, $sqlBuscar);

// Verificar que la preparación fue exitosa
if (!$stmtBuscar) {
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error interno del servidor.'
    ]);
    exit;
}

// Vincular el parámetro: 's' = string
mysqli_stmt_bind_param($stmtBuscar, 's', $nombre);

// Ejecutar la consulta
mysqli_stmt_execute($stmtBuscar);

// Obtener el resultado
$resultadoBuscar = mysqli_stmt_get_result($stmtBuscar);

// mysqli_fetch_assoc(): Obtiene una fila como array asociativo (UD5 §3.5)
$usuarioExistente = mysqli_fetch_assoc($resultadoBuscar);


// 8. CREAR USUARIO O VERIFICAR CONTRASEÑA


$usuarioId = null;

if ($usuarioExistente) {
    // El usuario YA existe → verificar la contraseña
    // password_verify(): Compara la contraseña en texto plano
    // con el hash almacenado en la BD.
    if (!password_verify($password, $usuarioExistente['password'])) {
        http_response_code(401);
        echo json_encode([
            'exito' => false,
            'mensaje' => 'Contraseña incorrecta para este nombre de usuario.'
        ]);
        exit;
    }
    // Contraseña correcta: usar el ID existente
    $usuarioId = $usuarioExistente['id'];

} else {
    // El usuario NO existe: crearlo (INSERT INTO)
    // Consulta preparada con múltiples parámetros
    // 'sss' = tres parámetros de tipo String

    // Si el email está vacío, guardamos NULL en la BD
    $emailParaBD = !empty($email) ? $email : null;

    $sqlInsertar = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
    $stmtInsertar = mysqli_prepare($conexion, $sqlInsertar);

    if (!$stmtInsertar) {
        http_response_code(500);
        echo json_encode([
            'exito' => false,
            'mensaje' => 'Error interno del servidor.'
        ]);
        exit;
    }

    // 'sss' = tres parámetros de tipo string
    mysqli_stmt_bind_param($stmtInsertar, 'sss', $nombre, $emailParaBD, $passwordHash);
    mysqli_stmt_execute($stmtInsertar);

    // mysqli_insert_id(): Devuelve el ID generado por AUTO_INCREMENT
    // del último INSERT exitoso.
    $usuarioId = mysqli_insert_id($conexion);

    // Verificar que se creó correctamente
    if ($usuarioId <= 0) {
        http_response_code(500);
        echo json_encode([
            'exito' => false,
            'mensaje' => 'Error al crear el usuario.'
        ]);
        exit;
    }

    // Cerrar el statement
    mysqli_stmt_close($stmtInsertar);
}

// Cerrar el statement de búsqueda
mysqli_stmt_close($stmtBuscar);


// 9. CALCULAR LA PUNTUACIÓN
//
// Fórmula de puntuación:
// - Menos fichas restantes = mejor (el ideal es 1)
// - Menos movimientos = mejor
// - Menos tiempo = mejor
//
// Puntuación = (15 - fichasRestantes) * 100
//              + max(0, 200 - movimientos * 5)
//              + max(0, 500 - tiempoSegundos * 2)

// Función para calcular la puntuación 
function calcularPuntuacion($fichas, $movs, $tiempo) {
    $puntosFichas = (15 - $fichas) * 100;
    $puntosMovimientos = max(0, 200 - $movs * 5);
    $puntosTiempo = max(0, 500 - $tiempo * 2);
    return $puntosFichas + $puntosMovimientos + $puntosTiempo;
}

$puntuacion = calcularPuntuacion($fichasRestantes, $movimientos_partida, $tiempoSegundos);



// 10. GUARDAR LA PUNTUACIÓN EN LA BASE DE DATOS

// INSERT INTO puntuaciones: Inserta una nueva fila
// con los datos de la partida.

// Consulta preparada con 5 parámetros:
// 'iiiii' = cinco parámetros de tipo Integer


$sqlPuntuacion = "INSERT INTO puntuaciones (usuario_id, fichas_restantes, movimientos, tiempo_segundos, puntuacion) 
                  VALUES (?, ?, ?, ?, ?)";
$stmtPuntuacion = mysqli_prepare($conexion, $sqlPuntuacion);

if (!$stmtPuntuacion) {
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error al guardar la puntuación.'
    ]);
    exit;
}

// 'iiiii' = cinco parámetros de tipo integer
mysqli_stmt_bind_param($stmtPuntuacion, 'iiiii',
    $usuarioId,
    $fichasRestantes,
    $movimientos_partida,
    $tiempoSegundos,
    $puntuacion
);

$resultadoInsercion = mysqli_stmt_execute($stmtPuntuacion);

// Cerrar el statement
mysqli_stmt_close($stmtPuntuacion);



// 11. DEVOLVER RESPUESTA AL FRONTEND
//
// json_encode(): Convierte un array PHP en una cadena JSON.
// El frontend (JavaScript) recibirá esta respuesta
// mediante fetch() y la procesará.


if ($resultadoInsercion) {
    echo json_encode([
        'exito' => true,
        'mensaje' => '¡Puntuación guardada correctamente!',
        'datos' => [
            'nombre' => $nombre,
            'puntuacion' => $puntuacion,
            'fichas_restantes' => $fichasRestantes,
            'movimientos' => $movimientos_partida,
            'tiempo_segundos' => $tiempoSegundos
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error al guardar la puntuación en la base de datos.'
    ]);
}


// 12. CERRAR LA CONEXIÓN
//
// mysqli_close(): Cierra la conexión con la base de datos.
// Buena práctica: liberar recursos cuando ya no se necesitan.


mysqli_close($conexion);

?>