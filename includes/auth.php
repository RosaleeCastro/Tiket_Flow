<?php
//Iniciar session solo si todavia no esta iniciada
if (session_status() === PHP_SESSION_NONE) {
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
function hasRole(string $rol): bool
{
    if (!isLoggedIn()) {
        return false;
    }

    $rol = strtolower(trim($rol));

    // Primero intentamos validar por rol_id, que es más fiable
    $rolId = $_SESSION['usuario']['rol_id'] ?? null;

    $mapaRoles = [
        'admin' => 1,
        'tecnico' => 2,
        'cliente' => 3
    ];

    if ($rolId !== null && isset($mapaRoles[$rol])) {
        return (int)$rolId === $mapaRoles[$rol];
    }

    // Si no existe rol_id, validamos por nombre de rol
    return currentUserRole() === $rol;
}

/**
 * Restringe el acceso a un solo rol
 */

function requireRole(string $rol): void
{
  requireLogin();
  if(!hasRole($rol)){
    http_response_code(403);
    echo "Acceso denegado. No tienes permisos para acceder a esta página.";
    exit;
  }
}
/**
 * Restringe acceso a varios roles permitidos 
 */
function requiereAnyRole(array $roles): void
{
  requireLogin();
  $rolActual = currentUserRole();
  if(!in_array($rolActual, $roles, true)){
    http_response_code(403);
    echo "Acceso denegado. No tienes permisos para acceder a esta página.";
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

// ── CSRF ─────────────────────────────────────────────────────────────────────

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        die('Token CSRF inválido. Vuelve atrás e inténtalo de nuevo.');
    }
}
?>