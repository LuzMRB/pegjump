<?php
// REGISTRO.PHP — Registro de Usuarios (

// Este script demuestra el PROCESAMIENTO DE FORMULARIOS
// usando el método tradicional con $_POST
// CONCEPTOS CLASE 8 APLICADOS:
//   - $_POST: Recibir datos del formulario 
//   - $_SERVER['REQUEST_METHOD']: Verificar método HTTP
//   - Validación del lado del servidor 
//   - Consultas preparadas: mysqli_prepare, bind_param 
//   - password_hash(): Almacenamiento seguro de contraseñas
//   - htmlspecialchars(): Sanitización contra XSS 
//   - $_SESSION: Gestión de sesiones 
//
// FLUJO DEL SCRIPT:
//   1. Si el método es GET: Mostrar el formulario vacío
//   2. Si el método es POST: Procesar los datos:
//      a. Sanitizar las entradas (htmlspecialchars)
//      b. Validar en el servidor
//      c. Hashear la contraseña (password_hash)
//      d. Insertar en la BD con consulta preparada
//      e. Crear sesión ($_SESSION)
//      f. Redirigir al juego
//
// DIFERENCIA CON process.php:
//   - process.php recibe datos en JSON (desde fetch/JavaScript)
//   - registro.php recibe datos con $_POST (formulario HTML tradicional)
//   Ambos métodos son válidos; $_POST es el método clásico de PHP.


// session_start(): SIEMPRE debe ir al principio del script 
// Inicia o reanuda una sesión existente.
// $_SESSION permite almacenar datos del usuario entre páginas.
session_start();

// Incluir configuración de la BD
require_once __DIR__ . '/config.php';


// Variables para mensajes y valores del formulario
$errores = [];
$exito = false;
$nombre = '';
$email = '';



//  PROCESAMIENTO DEL FORMULARIO (solo si es POST)

// $_SERVER['REQUEST_METHOD']: Variable superglobal que contiene
// el método HTTP usado (GET, POST, PUT, DELETE).
// Cuando el usuario ENVÍA el formulario, el método es POST.
// Cuando CARGA la página, el método es GET.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    // PASO 1: SANITIZACIÓN DE ENTRADAS (Prevención XSS)

    // htmlspecialchars(): Convierte caracteres especiales en
    // entidades HTML para que NO se ejecuten como código.

    // Ejemplo: Si un atacante escribe como nombre:
    //   <script>alert('hackeado')</script>
    //
    // Sin htmlspecialchars(): El navegador ejecutaría ese script
    // Con htmlspecialchars(): Se convierte en texto inofensivo:
    //   &lt;script&gt;alert('hackeado')&lt;/script&gt;
    //
    // Parámetros:
    //   ENT_QUOTES: Convierte comillas simples Y dobles
    //   'UTF-8': Codificación de caracteres
    //
    // trim(): Elimina espacios en blanco al inicio y final.
    //
    // IMPORTANTE: 
    //   La sanitización se hace en el SERVIDOR porque un atacante
    //   podría desactivar JavaScript y enviar datos directamente.

    $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'] ?? '';
    $passwordConfirm = $_POST['password_confirm'] ?? '';

    // NOTA: NO aplicamos htmlspecialchars a la contraseña porque
    // se va a hashear, no se va a mostrar en HTML.
    // Si la sanitizáramos, cambiaría caracteres especiales
    // y el usuario no podría usar & o < en su contraseña.


   
    // PASO 2: VALIDACIÓN EN EL SERVIDOR

    // CONCEPTO CLAVE:
    //   NUNCA confiar solo en la validación del cliente (JavaScript).
    //   La validación del servidor es OBLIGATORIA porque:
    //   - El usuario puede desactivar JavaScript
    //   - Un atacante puede enviar peticiones HTTP directamente
    //   - Los datos del cliente pueden ser manipulados
    //
    // Funciones de validación:
    //   strlen(): Devuelve la longitud de una cadena
    //   empty(): Comprueba si una variable está vacía
    //   filter_var(): Valida formato (email, URL, IP, etc.)
    //   preg_match(): Comprueba expresiones regulares

    // Validar nombre
    if (empty($nombre)) {
        $errores[] = 'El nombre es obligatorio.';
    } elseif (strlen($nombre) < 2 || strlen($nombre) > 50) {
        $errores[] = 'El nombre debe tener entre 2 y 50 caracteres.';
    }

    // Validar email (opcional, pero si se da, debe ser válido)
    // filter_var() con FILTER_VALIDATE_EMAIL verifica el formato
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El formato del email no es válido.';
    }

    // Validar contraseña
    if (empty($password)) {
        $errores[] = 'La contraseña es obligatoria.';
    } elseif (strlen($password) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        // preg_match('/[0-9]/', ...): Busca al menos un dígito
        $errores[] = 'La contraseña debe contener al menos un número.';
    }

    // Validar que las contraseñas coincidan
    if ($password !== $passwordConfirm) {
        $errores[] = 'Las contraseñas no coinciden.';
    }


    // PASO 3: VERIFICAR SI EL USUARIO YA EXISTE
   
    
    // Usamos CONSULTA PREPARADA para prevenir inyección SQL.

    // ¿QUÉ ES LA INYECCIÓN SQL?
    //   Es un ataque donde el atacante inserta código SQL
    //   malicioso a través de los datos del formulario.
    //
    //   Ejemplo VULNERABLE (NUNCA hacer esto):
    //     $sql = "SELECT * FROM usuarios WHERE nombre = '$nombre'";
    //   Si $nombre = "' OR '1'='1' --", la consulta devolvería
    //   TODOS los usuarios de la base de datos.
    //
    //   SOLUCIÓN: Consultas preparadas (prepared statements)
    //   - Se separa la INSTRUCCIÓN SQL de los DATOS
    //   - Los datos NUNCA se interpretan como código SQL
    //   - PHP los trata como VALORES puros

    if (empty($errores)) {
        // mysqli_prepare(): Prepara la consulta con marcadores 
        // El ? es un placeholder donde irá el valor real
        $sqlBuscar = "SELECT id FROM usuarios WHERE nombre = ?";
        $stmtBuscar = mysqli_prepare($conexion, $sqlBuscar);

        // mysqli_stmt_bind_param(): Asocia valores a los marcadores
        // 's' indica que el parámetro es un String
        // Tipos: 's' = string, 'i' = integer, 'd' = double, 'b' = blob
        mysqli_stmt_bind_param($stmtBuscar, 's', $nombre);

        // mysqli_stmt_execute(): Ejecuta la consulta preparada
        mysqli_stmt_execute($stmtBuscar);

        $resultado = mysqli_stmt_get_result($stmtBuscar);

        if (mysqli_num_rows($resultado) > 0) {
            $errores[] = 'Este nombre de usuario ya está registrado.';
        }

        mysqli_stmt_close($stmtBuscar);
    }


    // PASO 4: CREAR EL USUARIO (si no hay errores)

    if (empty($errores)) {

        // password_hash(): Genera un hash seguro de la contraseña
    
        // ¿POR QUÉ HASHEAR? 
        //   NUNCA se almacenan contraseñas en texto plano.
        //   Si un atacante accede a la BD, solo verá hashes
        //   que NO se pueden revertir a la contraseña original.
        //
        // PASSWORD_DEFAULT: Usa el algoritmo bcrypt
        //   Bcrypt añade un "salt" (dato aleatorio) automáticamente,
        //   lo que hace que dos usuarios con la misma contraseña
        //   tengan hashes DIFERENTES.
        //
        // Ejemplo:
        //   Contraseña: "miClave123"
        //   Hash: "$2y$10$kJx5F.G7R8h..." (60+ caracteres)

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Consulta preparada para INSERT
        // 'sss' = 3 parámetros de tipo String
        $emailParaBD = !empty($email) ? $email : null;
        $sqlInsertar = "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)";
        $stmtInsertar = mysqli_prepare($conexion, $sqlInsertar);
        mysqli_stmt_bind_param($stmtInsertar, 'sss', $nombre, $emailParaBD, $passwordHash);

        if (mysqli_stmt_execute($stmtInsertar)) {
            // mysqli_insert_id(): Devuelve el ID del último INSERT
            $nuevoId = mysqli_insert_id($conexion);

            // PASO 5: CREAR SESIÓN ($_SESSION)
            // $_SESSION: Variable superglobal que almacena datos
            // del usuario que PERSISTEN entre páginas 
        
            // A diferencia de las variables normales (que se pierden
            // al cargar otra página), $_SESSION mantiene los datos
            // mientras la sesión esté activa.
        
            // Usos comunes:
            //   - Guardar si el usuario ha iniciado sesión
            //   - Almacenar el nombre del usuario
            //   - Controlar el acceso a páginas protegidas

            $_SESSION['usuario_id'] = $nuevoId;
            $_SESSION['usuario_nombre'] = $nombre;
            $_SESSION['logueado'] = true;

            $exito = true;

            // header('Location: ...'): Redirige al usuario a otra página
            // Debe llamarse ANTES de que se envíe cualquier HTML
            header('Location: ../index.php?registro=exitoso');
            exit;
        } else {
            $errores[] = 'Error al crear el usuario. Inténtalo de nuevo.';
        }

        mysqli_stmt_close($stmtInsertar);
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
    <title>Registro — Solitario</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Estilos específicos para la página de registro */
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
        .error-lista {
            background-color: #ffebee;
            border-left: 4px solid #d32f2f;
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .error-lista li {
            color: #d32f2f;
            margin: 5px 0;
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

    <!-- HEADER -->
    <header>
        <div class="container">
            <h1>Solitario</h1>
            <p class="subtitulo">Registro de usuario</p>
        </div>
    </header>

    <main>
        <div class="auth-container">
            <h2>Crear Cuenta</h2>

            <?php
            
            // MOSTRAR ERRORES DE VALIDACIÓN
            // Si hay errores, los mostramos usando PHP dentro del HTML.
            // htmlspecialchars() se aplica a CADA dato que se muestra
            // en la página para prevenir XSS.

            if (!empty($errores)) {
                echo '<ul class="error-lista">';
                foreach ($errores as $error) {
                    // htmlspecialchars() en la salida: SIEMPRE
                    echo '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
                }
                echo '</ul>';
            }
            ?>

            <!--
                FORMULARIO DE REGISTRO
                
                action="registro.php": Los datos se envían a ESTE MISMO archivo.
                method="POST": Método seguro para enviar datos sensibles.
                
                DIFERENCIA GET vs POST
                - GET: Los datos van en la URL (?nombre=Juan&email=...)
                  → VISIBLE en la barra de direcciones
                  → Se usa para búsquedas, filtros, navegación
                  → NUNCA para datos sensibles (contraseñas)
                
                - POST: Los datos van en el CUERPO de la petición HTTP
                  → NO visible en la URL
                  → Se usa para formularios con datos sensibles
                  → Es el método adecuado para registro/login
            -->
            <form action="registro.php" method="POST" class="formulario">

                <div class="form-grupo">
                    <label for="nombre">Nombre de usuario:</label>
                    <input type="text" id="nombre" name="nombre"
                           value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="Tu nombre de usuario"
                           required minlength="2" maxlength="50">
                    <!--
                        value="<?php echo htmlspecialchars(...); ?>":
                        Si hay un error de validación, el formulario se vuelve
                        a mostrar con los datos que el usuario ya escribió.
                        htmlspecialchars() SANITIZA el valor para prevenir XSS.
                    -->
                </div>

                <div class="form-grupo">
                    <label for="email">Email (opcional):</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                           placeholder="tu@email.com">
                </div>

                <div class="form-grupo">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password"
                           placeholder="Mínimo 6 caracteres, 1 número"
                           required minlength="6">
                    <!--
                        NOTA: type="password": El navegador oculta el texto
                        con puntos (•••••). El valor NUNCA se pre-rellena
                        por seguridad (no ponemos value="...").
                    -->
                </div>

                <div class="form-grupo">
                    <label for="password_confirm">Confirmar contraseña:</label>
                    <input type="password" id="password_confirm" name="password_confirm"
                           placeholder="Repite tu contraseña"
                           required minlength="6">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Registrarse
                </button>
            </form>

            <div class="auth-link">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
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