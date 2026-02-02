<?php
// session_start(): OBLIGATORIO antes de usar $_SESSION (UD5 §3.3)
// Debe ir ANTES de cualquier salida HTML o require
session_start();

// INDEX.PHP Página Principal del Solitario 

// Este archivo es la versión PHP de index.html.
// Integra PHP con HTML para cargar datos dinámicos desde la BD.

//   - PHP se integra directamente con HTML (
//   - Las etiquetas <?php ... ?> permiten insertar código PHP
//     dentro del HTML.
//   - require_once: Incluye un archivo PHP una sola vez.
//   - htmlspecialchars(): Convierte caracteres especiales a 
//     entidades HTML (previene XSS).
// DIFERENCIA CON index.html:
//   - La tabla del ranking se rellena con datos REALES de la BD
//   - El formulario envía datos a process.php
//   - Se usa PHP para generar HTML dinámicamente
//   - $_SESSION permite saber si el usuario está logueado (Clase 8)


// Incluir la configuración de la BD para cargar el ranking
require_once __DIR__ . '/php/config.php';

// Comprobar si el usuario está logueado ($_SESSION)
// isset(): Comprueba si una variable existe y no es NULL
$estaLogueado = isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;
$nombreUsuario = $estaLogueado ? $_SESSION['usuario_nombre'] : '';

// CARGAR DATOS DEL RANKING DESDE LA BASE DE DATOS

// Consulta SQL con JOIN para combinar usuarios y puntuaciones.
// ORDER BY puntuacion DESC: De mayor a menor puntuación.
// LIMIT 10: Solo los 10 mejores.


$ranking = [];
$sqlRanking = "SELECT u.nombre, p.fichas_restantes, p.movimientos, 
                      p.tiempo_segundos, p.puntuacion
               FROM puntuaciones p
               JOIN usuarios u ON p.usuario_id = u.id
               ORDER BY p.puntuacion DESC
               LIMIT 10";

$resultadoRanking = mysqli_query($conexion, $sqlRanking);

if ($resultadoRanking) {
    // Recorrer los resultados con while + mysqli_fetch_assoc() (UD5 §3.5)
    while ($fila = mysqli_fetch_assoc($resultadoRanking)) {
        $ranking[] = $fila;
    }
    mysqli_free_result($resultadoRanking);
}

// Cerrar la conexión (ya tenemos los datos en $ranking)
mysqli_close($conexion);
?>
<!DOCTYPE html>
<!-- 
    DOCTYPE: Declara que este documento es HTML5.
    Ahora el archivo es .php pero genera HTML igualmente.
    PHP se ejecuta en el SERVIDOR y el navegador solo recibe HTML.
-->
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Juego del Solitario  - Proyecto Full Stack para Ingeniería Web I">
    <meta name="author" content="Luz Rubio Bolger">
    <title>Solitario | Luz Rubio Bolger</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- HEADER -->
    <header>
        <div class="container">
            <h1> Solitario </h1>
            <p class="subtitulo">¿Podrás resolver este puzle?</p>
            <nav>
                <ul class="nav-links">
                    <li><a href="#juego">Juego</a></li>
                    <li><a href="#reglas">Reglas</a></li>
                    <li><a href="#ranking">Ranking</a></li>
                    <?php if ($estaLogueado): ?>
                        <!--
                            Si el usuario está logueado, mostramos su nombre
                            y un enlace para cerrar sesión.
                            htmlspecialchars(): SIEMPRE sanitizar datos que
                            vienen de $_SESSION antes de mostrarlos (UD5 §3.6)
                        -->
                        <li><a href="php/logout.php">Cerrar sesión (<?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'); ?>)</a></li>
                    <?php else: ?>
                        <!-- Si NO está logueado, enlaces a registro y login -->
                        <li><a href="php/login.php">Login</a></li>
                        <li><a href="php/registro.php">Registro</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- MAIN -->
    <main>

        <!-- SECCIÓN 1: El Juego -->
        <section id="juego">
            <h2> Empieza a resolver</h2>
            <p>Haz clic en una ficha y luego en un hueco para saltar y eliminar fichas.</p>

            <div class="tablero-container">
                <div class="tablero" id="tablero">
                    <!-- Fila 1: 1 posición -->
                    <div class="fila">
                        <div class="posicion vacia" data-pos="0"></div>
                    </div>
                    <!-- Fila 2: 2 posiciones -->
                    <div class="fila">
                        <div class="posicion ficha" data-pos="1"></div>
                        <div class="posicion ficha" data-pos="2"></div>
                    </div>
                    <!-- Fila 3: 3 posiciones -->
                    <div class="fila">
                        <div class="posicion ficha" data-pos="3"></div>
                        <div class="posicion ficha" data-pos="4"></div>
                        <div class="posicion ficha" data-pos="5"></div>
                    </div>
                    <!-- Fila 4: 4 posiciones -->
                    <div class="fila">
                        <div class="posicion ficha" data-pos="6"></div>
                        <div class="posicion ficha" data-pos="7"></div>
                        <div class="posicion ficha" data-pos="8"></div>
                        <div class="posicion ficha" data-pos="9"></div>
                    </div>
                    <!-- Fila 5: 5 posiciones -->
                    <div class="fila">
                        <div class="posicion ficha" data-pos="10"></div>
                        <div class="posicion ficha" data-pos="11"></div>
                        <div class="posicion ficha" data-pos="12"></div>
                        <div class="posicion ficha" data-pos="13"></div>
                        <div class="posicion ficha" data-pos="14"></div>
                    </div>
                </div>
            </div>

            <!-- Panel de estadísticas -->
            <div class="stats-panel">
                <div class="stat">
                    <span class="stat-label">Fichas restantes:</span>
                    <span class="stat-valor" id="fichas-restantes">14</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Movimientos:</span>
                    <span class="stat-valor" id="movimientos">0</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Tiempo:</span>
                    <span class="stat-valor" id="tiempo">00:00</span>
                </div>
            </div>

            <!-- Botones de control -->
            <div class="controles">
                <button id="btn-reiniciar" class="btn btn-primary">Reiniciar</button>
                <button id="btn-deshacer" class="btn btn-secondary">Deshacer</button>
                <button id="btn-pista" class="btn btn-secondary">Pista</button>
            </div>
        </section>

        <!-- SECCIÓN 2: Reglas del Juego -->
        <section id="reglas">
            <h2>Reglas del Juego</h2>
            <div class="reglas-grid">
                <div class="regla-card">
                    <h3>1. Objetivo</h3>
                    <p>Eliminar todas las fichas del tablero hasta dejar solo una.</p>
                </div>
                <div class="regla-card">
                    <h3>2. Movimiento</h3>
                    <p>Una ficha salta sobre otra adyacente y aterriza en un hueco vacío.</p>
                </div>
                <div class="regla-card">
                    <h3>3. Captura</h3>
                    <p>La ficha saltada se elimina del tablero automáticamente.</p>
                </div>
                <div class="regla-card">
                    <h3>4. Fin</h3>
                    <p>La partida termina cuando no quedan movimientos posibles.</p>
                </div>
            </div>
        </section>

        <!-- SECCIÓN 3: Ranking de Puntuaciones -->
        <section id="ranking">
            <h2>Ranking de Jugadores</h2>

            <!-- 
                FORMULARIO:
                Ahora NO tiene action ni method en el HTML,
                porque se envía con JavaScript (fetch API).
                El JS enviará los datos a php/process.php
            -->
            <form id="form-puntuacion" class="formulario">
                <h3>Guardar tu puntuación</h3>

                <?php
               
                // INTEGRACIÓN CON SESIONES 
                // Si el usuario está logueado, mostramos un mensaje
                // de bienvenida y pre-rellenamos el nombre.
                // Esto mejora la experiencia del usuario.
             
                if ($estaLogueado):
                ?>
                <div style="background-color: #e8f5e9; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center;">
                    Jugando como <strong><?php echo htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
                <?php endif; ?>

                <div class="form-grupo">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre"
                        placeholder="Tu nombre" required minlength="2" maxlength="50"
                        value="<?php echo $estaLogueado ? htmlspecialchars($nombreUsuario, ENT_QUOTES, 'UTF-8') : ''; ?>"
                        <?php echo $estaLogueado ? 'readonly' : ''; ?>>
                    <!--
                        Si está logueado:
                        - value="nombre": Se pre-rellena con el nombre de la sesión
                        - readonly: No se puede editar (ya sabemos quién es)
                        - htmlspecialchars(): SIEMPRE sanitizar la salida
                    -->
                    <span class="mensaje-error" id="error-nombre"></span>
                </div>
                <div class="form-grupo">
                    <label for="email">Email (opcional):</label>
                    <input type="email" id="email" name="email" placeholder="tu@email.com">
                    <span class="mensaje-error" id="error-email"></span>
                </div>

                <div class="form-grupo">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" placeholder="Mínimo 6 caracteres, 1 número"
                        required minlength="6">
                    <span class="mensaje-error" id="error-password"></span>
                </div>

                <!-- Mensaje de éxito (se muestra con JS) -->
                <div id="mensaje-exito" class="mensaje-exito" style="display: none;">
                     ¡Puntuación guardada correctamente!
                </div>

                <!-- Mensaje de error del servidor (se muestra con JS) -->
                <div id="mensaje-servidor-error" class="mensaje-error" style="display: none; padding: 12px; background-color: #ffebee; border-radius: 6px; text-align: center; margin: 15px 0;">
                </div>

                <button type="submit" class="btn btn-primary">Guardar Puntuación</button>
            </form>

            <!-- 
                TABLA DE RANKING:
                
                Ahora el <tbody> se rellena de DOS formas:
                1. PHP (al cargar la página): Los datos ya vienen del servidor
                2. JavaScript (dinámicamente): Se actualiza sin recargar (fetch)
                
                Usamos PHP para generar las filas <tr> con un bucle foreach.
                htmlspecialchars(): Convierte caracteres especiales para prevenir XSS.
            -->
            <table class="tabla-ranking" id="tabla-ranking">
                <thead>
                    <tr>
                        <th>Pos.</th>
                        <th>Jugador</th>
                        <th>Fichas</th>
                        <th>Movimientos</th>
                        <th>Tiempo</th>
                        <th>Puntuación</th>
                    </tr>
                </thead>
                <tbody id="ranking-body">
                    <?php
                    
                    // GENERAR FILAS DEL RANKING CON PHP
                    
                    // foreach: Recorre cada elemento del array $ranking (UD5)
                    // $indice: Posición en el array (0, 1, 2...)
                    // $fila: Datos de cada puntuación (array asociativo)
                    //
                    // htmlspecialchars(): Convierte caracteres como < > " '
                    // a entidades HTML (&lt; &gt; etc.)
                    // Esto PREVIENE ataques XSS (Cross-Site Scripting)
                    // donde un atacante podría inyectar HTML/JS malicioso
                    // a través del nombre de usuario.
                 

                    if (count($ranking) > 0) {
                        foreach ($ranking as $indice => $fila) {
                            // Calcular tiempo formateado (MM:SS)
                            $minutos = floor($fila['tiempo_segundos'] / 60);
                            $segundos = $fila['tiempo_segundos'] % 60;
                            $tiempoFormateado = sprintf('%02d:%02d', $minutos, $segundos);
                    ?>
                    <tr>
                        <td><?php echo $indice + 1; ?></td>
                        <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                        <td><?php echo intval($fila['fichas_restantes']); ?></td>
                        <td><?php echo intval($fila['movimientos']); ?></td>
                        <td><?php echo $tiempoFormateado; ?></td>
                        <td><?php echo intval($fila['puntuacion']); ?></td>
                    </tr>
                    <?php
                        }
                    } else {
                    ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: #999;">
                            No hay puntuaciones todavía. ¡Sé el primero en jugar!
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>

    </main>

    <!-- FOOTER -->
    <footer>
        <p>&copy; 2025 Solitario — Luz Rubio Bolger — UAX</p>
        <p>Proyecto de Ingeniería Web I</p>
    </footer>

    <!-- JavaScript externo (se carga al final del body) -->
    <script src="js/script.js"></script>

</body>

</html>