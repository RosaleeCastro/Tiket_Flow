<?php
require_once __DIR__ . '/includes/auth.php';


// Si el usuario ya inicio sesión, va al dashboard
if(isLoggedIn()){
  header('Location: /Ticket_Flow/dashboard/dashboard.php');
  exit;
}

//si no ha iniciado sesion, va al login
header('Location: /Ticket_Flow/auth/login.php');
exit;
?>