<?php
// Cargamos el archivo de autenticación para usar funciones de sesión
require_once __DIR__ . '/../includes/auth.php';

// Si el usuario ya inició sesión, no debe quedarse en el registro
if (isLoggedIn()) {
    header('Location: ../dashboard/dashboard.php');
    exit;
}

// Recogemos posibles mensajes enviados por la URL
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <!-- Hace que el formulario se adapte mejor en móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Registro | Ticket Flow</title>

    <!-- Enlace al CSS general del proyecto -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">

    <!-- Contenedor principal del formulario -->
    <div class="login-container">
        <h1>Crear cuenta</h1>
        <p>Regístrate para acceder al sistema como cliente</p>

        <!-- Mensaje si faltan campos -->
        <?php if ($error === 'campos'): ?>
            <div class="error-box">
                Debes completar todos los campos obligatorios.
            </div>
        <?php endif; ?>

        <!-- Mensaje si las contraseñas no coinciden -->
        <?php if ($error === 'password_mismatch'): ?>
            <div class="error-box">
                Las contraseñas no coinciden.
            </div>
        <?php endif; ?>

        <!-- Mensaje si el email ya existe -->
        <?php if ($error === 'email_exists'): ?>
            <div class="error-box">
                Ya existe una cuenta registrada con ese correo electrónico.
            </div>
        <?php endif; ?>

        <!-- Mensaje de error técnico -->
        <?php if ($error === 'db'): ?>
            <div class="error-box">
                Se produjo un error al registrar el usuario.
            </div>
        <?php endif; ?>

        <!-- Mensaje de éxito -->
        <?php if ($success === '1'): ?>
            <div class="success-box">
                Registro completado correctamente. Ya puedes iniciar sesión.
            </div>
        <?php endif; ?>

        <!-- Formulario de registro -->
        <form action="registro_process.php" method="POST">

            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    placeholder="Introduce tu nombre"
                    required
                >
            </div>

            <div class="form-group">
                <label for="apellidos">Apellidos</label>
                <input
                    type="text"
                    id="apellidos"
                    name="apellidos"
                    placeholder="Introduce tus apellidos"
                    required
                >
            </div>

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
                <label for="empresa">Empresa</label>
                <input
                    type="text"
                    id="empresa"
                    name="empresa"
                    placeholder="Nombre de tu empresa"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Crea una contraseña"
                    required
                >
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar contraseña</label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Repite tu contraseña"
                    required
                >
            </div>

            <button type="submit" class="btn-login">Registrarse</button>
        </form>

        <div class="extra-links">
            ¿Ya tienes cuenta?
            <a href="login.php">Iniciar sesión</a>
        </div>
    </div>

</body>
</html>