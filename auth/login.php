<?php
// Cargamos el archivo de autenticación para poder usar funciones como isLoggedIn()
require_once __DIR__ . '/../includes/auth.php';

// Si el usuario ya ha iniciado sesión, no debe quedarse en login.
// Lo redirigimos al dashboard.
if (isLoggedIn()) {
    header('Location: /ticket_flow/dashboard/dashboard.php');
    exit;
}

// Recogemos un posible parámetro de error enviado por la URL.
// Si no existe, guardamos una cadena vacía.
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <!-- Hace que la web se adapte mejor en móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | Ticket Flow</title>

    <!-- Enlace a tu archivo CSS externo -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">

    <!-- Contenedor principal del formulario de login -->
    <div class="login-container">
        <h1>Ticket Flow</h1>
        <p>Inicia sesión para acceder al sistema</p>

        <!--
            Si en la URL viene ?error=credenciales
            mostramos un mensaje indicando que el correo o contraseña son incorrectos
        -->
        <?php if ($error === 'credenciales'): ?>
            <div class="error-box">
                El correo o la contraseña no son correctos.
            </div>
        <?php endif; ?>

        <!--
            Si en la URL viene ?error=campos
            mostramos un mensaje indicando que faltan datos por completar
        -->
        <?php if ($error === 'campos'): ?>
            <div class="error-box">
                Debes completar todos los campos.
            </div>
        <?php endif; ?>

        <?php if ($error === 'inactivo'): ?>
           <div class="error-box">
                 Tu usuario está inactivo. Contacta con el administrador.
         </div>
        <?php endif; ?>

        <!--
            Formulario de inicio de sesión.
            Los datos se enviarán por POST a login_process.php
        -->
        <form action="/ticket_flow/auth/login_process.php" method="POST">

            <!-- Grupo del campo correo -->
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

            <!-- Grupo del campo contraseña -->
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

            <!-- Botón para enviar el formulario -->
            <button type="submit" class="btn-login">Entrar</button>
        </form>

        <!-- Enlace para ir al registro de usuario -->
        <div class="extra-links">
            ¿No tienes cuenta?
            <a href="/ticket_flow/auth/registro.php">Registrarse</a>
        </div>
    </div>

</body>
</html>