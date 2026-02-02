<?php
// LOGOUT.PHP 
// Este script destruye la sesión del usuario.
// CONCEPTOS APLICADOS:
//   - session_start(): Reanuda la sesión existente
//   - session_unset(): Elimina todas las variables de sesión
//   - session_destroy(): Destruye la sesión completamente
//   - header('Location: ...'): Redirige a otra página

// FLUJO:
//   1. Iniciar/reanudar la sesión
//   2. Eliminar variables de sesión
//   3. Destruir la sesión
//   4. Redirigir al juego


// 1. Iniciar la sesión (necesario para poder destruirla)
session_start();

// 2. session_unset(): Elimina TODAS las variables de $_SESSION
//    Después de esto, $_SESSION está vacío pero la sesión sigue existiendo
session_unset();

// 3. session_destroy(): Destruye la sesión en el servidor
//    Después de esto, la sesión ya no existe
session_destroy();

// 4. Redirigir al juego con un mensaje
header('Location: ../index.php?logout=exitoso');
exit;
?>