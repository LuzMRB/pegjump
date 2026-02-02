<?php
// LOGIN.PHP
// Este script demuestra:
//   - Procesamiento de formularios con $_POST 
//   - Verificación de contraseñas con password_verify()
//   - Gestión de sesiones con $_SESSION 
//   - Consultas preparadas para prevenir inyección SQL
//   - Sanitización con htmlspecialchars() contra XSS 

// FLUJO:
//   1. Usuario escribe nombre + contraseña
//   2. PHP busca el usuario en la BD (consulta preparada)
//   3. PHP verifica la contraseña con password_verify()
//   4. Si es correcta --> Crea sesión --> Redirige al juego
//   5. Si es incorrecta --> Muestra error


// Iniciar sesión (SIEMPRE al principio)
session_start();

// Si ya está logueado, redirigir al juego
if (isset($_SESSION['logueado']) && $_SESSION['logueado'] === true) {
    header('Location: ../index.php');
    exit;
}

// Incluir configuración de la BD
require_once __DIR__ . '/config.php';

$error = '';
$nombre = '';



//  PROCESAMIENTO DEL LOGIN (solo si es POST)


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // PASO 1: Sanitizar entradas con htmlspecialchars()
    // (Prevención XSS )
    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'] ?? '';

    // PASO 2: Validación del servidor
    if (empty($nombre) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } else {

        // PASO 3: Buscar usuario con CONSULTA PREPARADA

        // ¿POR QUÉ NO concatenar directamente? 
        //   VULNERABLE (NUNCA hacer):
        //     $sql = "SELECT * FROM usuarios WHERE nombre = '$nombre'";
        //     Un atacante podría escribir: ' OR '1'='1' --
        //     Y acceder sin contraseña.
    
        //   SEGURO (consulta preparada):
        //     $sql = "SELECT * FROM usuarios WHERE nombre = ?";
        //     El ? se reemplaza de forma segura por el valor real.
        //     PHP NUNCA interpreta el valor como código SQL.

        $sql = "SELECT id, nombre, password FROM usuarios WHERE nombre = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, 's', $nombre);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $usuario = mysqli_fetch_assoc($resultado);

        if ($usuario) {
            // PASO 4: VERIFICAR CONTRASEÑA con password_verify()
        
            // password_verify($contraseñaTextoPlano, $hashAlmacenado):
            //   Compara la contraseña que escribió el usuario
            //   con el hash que está guardado en la BD.
        
            //   IMPORTANTE: NO se puede comparar con == ni ===
            //   porque el hash es diferente cada vez (por el salt).
            //   password_verify() sabe cómo extraer el salt del hash
            //   y hacer la comparación correctamente.
        
            //   Ejemplo:
            //     $hash = "$2y$10$kJx5F.G7R8h..."
            //     password_verify("miClave123", $hash) --> true
            //     password_verify("otraClave",  $hash) --> false

            if (password_verify($password, $usuario['password'])) {

                // PASO 5: CREAR SESIÓN
                
                // session_regenerate_id(true): Genera un nuevo ID de sesión.
                // Esto previene ataques de "session fixation" donde un
                // atacante podría secuestrar la sesión del usuario.

                session_regenerate_id(true);

                // Almacenar datos en $_SESSION
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['logueado'] = true;

                // Redirigir al juego
                header('Location: ../index.php?login=exitoso');
                exit;

            } else {
                // Contraseña incorrecta
                // NOTA: No decimos "contraseña incorrecta" específicamente
                // para no dar pistas a un atacante sobre si el usuario existe
                $error = 'Nombre de usuario o contraseña incorrectos.';
            }
        } else {
            // Usuario no encontrado
            // Mismo mensaje genérico por seguridad
            $error = 'Nombre de usuario o contraseña incorrectos.';
        }

        mysqli_stmt_close($stmt);
    }
}

// Cerrar conexión
mysqli_close($conexion);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Solitario</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .auth-container h2 {
            text-align: center;
            color: #5d4037;
            margin-bottom: 25px;
        }
        .auth-link {
            text-align: center;
            margin-top: 20px;
        }
        .auth-link a {
            color: #8b5e3c;
            text-decoration: none;
            font-weight: bold;
        }
        .auth-link a:hover {
            text-decoration: underline;
        }
        .error-msg {
            background-color: #ffebee;
            border-left: 4px solid #d32f2f;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
            color: #d32f2f;
        }
        .volver-juego {
            text-align: center;
            margin-top: 15px;
        }
        .volver-juego a {
            color: #555;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <header>
        <div class="container">
            <h1>Solitario</h1>
            <p class="subtitulo">Inicio de sesión</p>
        </div>
    </header>

    <main>
        <div class="auth-container">
            <h2>Iniciar Sesión</h2>

            <?php if (!empty($error)): ?>
                <div class="error-msg">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <!--
                FORMULARIO DE LOGIN
                method="POST": Los datos van en el cuerpo HTTP (seguros)
                action="login.php": Se envía a este mismo archivo
            -->
            <form action="login.php" method="POST" class="formulario">

                <div class="form-grupo">
                    <label for="nombre">Nombre de usuario:</label>
                    <input type="text" id="nombre" name="nombre"
                           value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Tu nombre de usuario"
                           required>
                </div>

                <div class="form-grupo">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password"
                           placeholder="Tu contraseña"
                           required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Iniciar Sesión
                </button>
            </form>

            <div class="auth-link">
                <p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
            </div>

            <div class="volver-juego">
                <p><a href="../index.php">← Volver al juego</a></p>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Solitario — Luz Rubio Bolger — UAX</p>
    </footer>

</body>
</html>