<?php
// Cargamos el archivo de autenticación para asegurarnos de tener acceso a la sesión
require_once __DIR__ . '/../includes/auth.php';

/*
|--------------------------------------------------------------------------
| 1. Vaciar todas las variables de sesión
|--------------------------------------------------------------------------
| Esto elimina los datos guardados en $_SESSION
*/
$_SESSION = [];

/*
|--------------------------------------------------------------------------
| 2. Destruir la sesión
|--------------------------------------------------------------------------
| Esto invalida la sesión actual en el servidor
*/
session_destroy();

/*
|--------------------------------------------------------------------------
| 3. Redirigir al login
|--------------------------------------------------------------------------
| Después de cerrar sesión, enviamos al usuario al formulario de acceso
*/
header('Location: login.php');
exit;
?>