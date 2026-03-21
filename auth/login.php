<?php 
require_once __DIR__ . '/../includes/auth.php';

//Si el usuario ya inicio sesión, no tiene sentido mostrar el login
if(isLoggedIn()){
  header('Location: /Ticket_Flow/dashboard/dashboard.php');
  exit;
}

//Capturamos un posible msj de error envi<do por URL

$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title>Login | Ticket Flow</title>
</head>
<body>
  <div class="login-container">
    <h1>Ticket Flow</h1>
    <p>Inicia sesión para acceder al sistema</p>

    <?php if($error === 'credenciales'):?> 
      <div class="error-box">
        El correo o la contraseña no son correctos.
      </div>
    <?php  endif; ?>
    
    <form action="/Ticket_Flow/auth/login_process.php" method="POST">
      <div class="form-group">
        <label for="email">Correo electrónico</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="ejemplo@correo.com" 
                    required
                >
      </div>
      <div class="form-group">
                <label for="password">Contraseña</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Introduce tu contraseña" 
                    required
                >
            </div>
            <button class="btn-login" type="submit">Entrar</button>
    </form>
    <div class="extra-links">
            ¿No tienes cuenta?
            <a href="/Ticket_Flow/auth/registro.php">Registrarse</a>
   </div>


  </div>
  
</body>
</html>