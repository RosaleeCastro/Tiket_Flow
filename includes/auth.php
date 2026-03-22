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

function requireLogin(): void
{
  if(!isLoggedIn()){
    header('Location: /Ticket_Flow/auth/login.php');
    exit;
  }
}

/**
 * Devuelve el rol del usaurio actual 
 */
function currentUserRole(): ?string
{
    if (!isset($_SESSION['usuario']['rol'])) {
        return null;
    }

    return strtolower(trim($_SESSION['usuario']['rol']));
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
  requireLogin();
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
  requireLogin();
  $rolActual = currentUserRol();
  if(!in_array($rolActual, $roles, true)){
    echo "Acceso denegado. No tienes permisos ´para entrar.";
    exit;
  }
}
/**
 * Comprueba si el usaurio es administrador 
 */
function isAdmin(): bool
{
  return hasRole('admin');
}
/**
 * Comprueba si el usuario es técnico
 */
function isTecnico(): bool
{
    return hasRole('tecnico');
}

/**
 * Comprueba si el usuario es cliente
 */
function isCliente(): bool
{
    return hasRole('cliente');
}
?>