<?php
// PROCESS.PHP Procesamiento del Formulario (Version POO)

// Refactorizado para usar clases (POO)

// ANTES (programacion estructurada):
//   Todo el codigo estaba suelto en funciones y variables globales.
//   Las consultas preparadas se repetian en cada archivo.

// AHORA (POO):
//   Usamos las clases Usuario y Puntuacion.
//   Cada clase encapsula su logica y reutiliza el codigo del padre (Modelo).
//   Los objetos se crean con "new" y se accede a sus metodos con "->"

header('Content-Type: application/json');

// 1. INCLUIR CONFIGURACION Y CLASES
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/clases/Usuario.php';
require_once __DIR__ . '/clases/Puntuacion.php';


// 2. VERIFICAR METODO POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Metodo no permitido. Use POST.']);
    exit;
}


// 3. RECIBIR DATOS JSON
$datosJSON = file_get_contents('php://input');
$datos = json_decode($datosJSON, true);

if ($datos === null) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => 'Error: formato JSON invalido.']);
    exit;
}


// 4. EXTRAER DATOS
$nombre = trim($datos['nombre'] ?? '');
$email = trim($datos['email'] ?? '');
$password = $datos['password'] ?? '';
$fichasRestantes = intval($datos['fichas_restantes'] ?? 14);
$movimientos_partida = intval($datos['movimientos'] ?? 0);
$tiempoSegundos = intval($datos['tiempo_segundos'] ?? 0);


// 5. VALIDACION EN EL SERVIDOR
$errores = [];

if (empty($nombre) || strlen($nombre) < 2 || strlen($nombre) > 50) {
    $errores[] = 'Nombre invalido (2-50 caracteres).';
}
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'Formato de email invalido.';
}
if (empty($password) || strlen($password) < 6) {
    $errores[] = 'Contrasena invalida (minimo 6 caracteres).';
} elseif (!preg_match('/[0-9]/', $password)) {
    $errores[] = 'La contrasena debe contener al menos un numero.';
}

if (!empty($errores)) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => 'Errores de validacion.', 'errores' => $errores]);
    exit;
}


// 6. CREAR OBJETOS (POO)

// Aqui es donde aplicamos la POO:
// Creamos objetos de las clases Usuario y Puntuacion
// usando el operador "new"

// $usuario = new Usuario($conexion);
//   -> Crea un objeto de tipo Usuario
//   -> El constructor recibe la conexion a la BD
//   -> Internamente llama a parent::__construct() (herencia)

$usuario = new Usuario($conexion);
$puntuacionObj = new Puntuacion($conexion);


// 7. BUSCAR O CREAR USUARIO
//
// Antes: Teniamos 30+ lineas de consultas preparadas sueltas
// Ahora: Una sola linea con el metodo del objeto
// $usuario->buscarPorNombre(): Usa el operador flecha (->) para
// acceder al metodo del objeto (UD5 seccion 3.4)

$usuarioExistente = $usuario->buscarPorNombre($nombre);
$usuarioId = null;

if ($usuarioExistente) {
    // Verificar contraseña usando el metodo del objeto
    if (!$usuario->verificarPassword($password, $usuarioExistente['password'])) {
        http_response_code(401);
        echo json_encode(['exito' => false, 'mensaje' => 'Contrasena incorrecta.']);
        exit;
    }
    $usuarioId = $usuarioExistente['id'];

} else {
    // Crear usuario nuevo
    // Usuario::hashPassword() es un metodo ESTATICO (se llama con :: en vez de ->)
    $passwordHash = Usuario::hashPassword($password);
    $usuarioId = $usuario->crear($nombre, $email, $passwordHash);

    if (!$usuarioId) {
        http_response_code(500);
        echo json_encode(['exito' => false, 'mensaje' => 'Error al crear el usuario.']);
        exit;
    }
}


// 8. GUARDAR PUNTUACION

// $puntuacionObj->guardar(): Calcula la puntuacion y la inserta en la BD
// Todo encapsulado en un solo metodo

$resultado = $puntuacionObj->guardar($usuarioId, $fichasRestantes, $movimientos_partida, $tiempoSegundos);

if ($resultado) {
    echo json_encode([
        'exito' => true,
        'mensaje' => 'Puntuacion guardada correctamente!',
        'datos' => [
            'nombre' => $nombre,
            'puntuacion' => $resultado['puntuacion'],
            'fichas_restantes' => $resultado['fichas_restantes'],
            'movimientos' => $resultado['movimientos'],
            'tiempo_segundos' => $resultado['tiempo_segundos']
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error al guardar la puntuacion.']);
}

mysqli_close($conexion);