<?php
//Iniciar session solo si todavia no esta iniciada
if(session_start()=== PHP_SESSION_NONE){
  session_start();
}
/*
* Comprobar si hay un usuario autenticado en sessión
*/

function isLoggedIn(): bool {
  return isset($_SESSION['usuario']) &&  !empty($_SESSION['usuario']);
}

/**
 * Devuelve los datos del usuario actual o null si no hay session
 */




?>