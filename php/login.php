<?php
// LOGIN.PHP  Inicio de Sesion (Version POO)

// CLASE 9: Refactorizado para usar la clase Usuario

// ANTES: Consultas preparadas sueltas, password_verify directo
// AHORA: $usuario->buscarPorNombre(), $usuario->verificarPassword(), etc.

session_start();

if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/clases/Usuario.php';

$error = '';
$nombre = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'] ?? '';

    if (empty($nombre) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } else {

        // CREAR OBJETO USUARIO (POO)
        // new Usuario($conexion): Crea el objeto
        // El constructor llama a parent::__construct() (herencia de Modelo)
        $usuario = new Usuario($conexion);

        // BUSCAR USUARIO
        // $usuario->buscarPorNombre(): Metodo de la clase Usuario
        // Internamente usa consultaPreparada() heredada de Modelo
        $usuarioEncontrado = $usuario->buscarPorNombre($nombre);

        if ($usuarioEncontrado) {

            // VERIFICAR CONTRASENA
            // $usuario->verificarPassword(): Encapsula password_verify()
            if ($usuario->verificarPassword($password, $usuarioEncontrado['password'])) {

                // INICIAR SESION
                // $usuario->iniciarSesion(): Encapsula session_regenerate_id + $_SESSION
                $usuario->iniciarSesion($usuarioEncontrado);

                header('Location: ../index.php?login=exitoso');
                exit;

            } else {
                $error = 'Nombre de usuario o contrasena incorrectos.';
            }
        } else {
            $error = 'Nombre de usuario o contrasena incorrectos.';
        }
    }
}

mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesion - Solitario</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .auth-container { max-width: 500px; margin: 40px auto; padding: 30px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .auth-container h2 { text-align: center; color: #5d4037; margin-bottom: 25px; }
        .auth-link { text-align: center; margin-top: 20px; }
        .auth-link a { color: #8b5e3c; text-decoration: none; font-weight: bold; }
        .auth-link a:hover { text-decoration: underline; }
        .error-msg { background-color: #ffebee; border-left: 4px solid #d32f2f; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px; color: #d32f2f; }
        .volver-juego { text-align: center; margin-top: 15px; }
        .volver-juego a { color: #555; text-decoration: none; }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>Solitario</h1>
            <p class="subtitulo">Inicio de sesion</p>
        </div>
    </header>
    <main>
        <div class="auth-container">
            <h2>Iniciar Sesion</h2>
            <?php if (!empty($error)): ?>
                <div class="error-msg">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <form action="login.php" method="POST" class="formulario">
                <div class="form-grupo">
                    <label for="nombre">Nombre de usuario:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tu nombre de usuario" required>
                </div>
                <div class="form-grupo">
                    <label for="password">Contrasena:</label>
                    <input type="password" id="password" name="password" placeholder="Tu contrasena" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Iniciar Sesion</button>
            </form>
            <div class="auth-link">
                <p>No tienes cuenta? <a href="registro.php">Registrate</a></p>
            </div>
            <div class="volver-juego">
                <p><a href="../index.php">Volver al juego</a></p>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; 2025 Solitario - Luz Rubio Bolger - UAX</p>
    </footer>
</body>
</html>