<?php
//Iniciar session solo si todavia no esta iniciada
if(session_start()=== PHP_SESSION_NONE){
  session_start();
}
/*
* Comprobar si hay un usuario autenticado en sessión
*/

function isLoggedIn(): bool 
{
  return isset($_SESSION['usuario']) &&  !empty($_SESSION['usuario']);
}

/**
 * Devuelve los datos del usuario actual o null si no hay session
 */

function currentUser(): ?array
{
  return $_SESSION['usuario'] ?? null;
}

/**
 * Fuerxa el acceso solo a usuarios autenticados
 * 
 */

function requiereLogin(): void
{
  if(!isLoggedIn()){
    header('Location: /Ticket_Flow/auth/login.php');
    exit;
  }
}

/**
 * Devuelve el rol del usaurio actual 
 */
function currentUserRol(): ?string
{
  return $_SESSION['USUARIO']['rol'] ?? null;
}
/**
 * Comprueba si el usaurio actual tiene un rol concreto
 */
function hasRole(string $rol):bool
{
  return isLoggedIn() && currentUser() === $rol;
}

/**
 * Restringe el acceso a un solo rol
 */

function requireRole(string $rol): void
{
  requiereLogin();
  if(!hasRole($rol)){
    echo "Acceso denegado. No tienes permisis para entrar";
    exit;
  }
}
/**
 * Restringe acceso a varios roles permitidos 
 */
function requiereAnyRole(array $roles): void
{
  requiereLogin();
  $rolActual = currentUserRol();
  if(!in_array($rolActual, $roles, true)){
    echo "Acceso denegado. No tienes permisos ´para entrar.";
    exit;
  }
}
?>