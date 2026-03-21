<?php 
//cargamos el archivo de autenticacion
require_once __DIR__ . '/../includes/auth.php';

//cargamos la conexion a la base de datos
require_once __DIR__ . '/../config/database.php';

/**
 * ************************************
 * 1. Comprobar que la conexión existe 
 * ***********************************
 * La variable de conexion se llma $conn 
 */
if(!isset($conn) || !($conn instanceof mysqli)){
  die('Error : la conexión  $conn no está disponible en config/database.php');
}
/**
 * ************************************
 * 2.Permitir solo peticiones POST
 * ***********************************
 * Solo permitiremos recibir datos desde el formulario  por URL,
 * si alguine entrs por url,  lo devolvemos al login 
 */
if($_SERVER['REQUEST_METHOD'] !== 'POST '){
  header('Location: login.php');
  exit;
}
/**
 * *********************************************
 * 3. Recoger y limpiar datos del formulario 
 */


?>


