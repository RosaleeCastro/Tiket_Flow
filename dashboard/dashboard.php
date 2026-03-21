<?php
// Cargamos el sistema de autenticación
require_once __DIR__ . '/../includes/auth.php';

// Esta página solo puede verla un usuario con sesión iniciada
requireLogin();

// Obtenemos los datos del usuario actual desde la sesión
$usuario = currentUser();

// Guardamos nombre y rol en variables para usarlas en el HTML
$nombre = $usuario['nombre'] ?? 'Usuario';
$apellidos = $usuario['apellidos'] ?? '';
$rol = $usuario['rol'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <!-- Para adaptar mejor la vista en móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/style.css">

    <title>Dashboard | Ticket Flow</title>

    <!-- Tu archivo CSS externo -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">

    <!-- Contenedor principal del dashboard -->
    <div class="dashboard-container">

        <!-- Encabezado de bienvenida -->
        <header class="dashboard-header">
            <h1>Panel principal</h1>
            <p>
                Bienvenida, 
                <strong><?php echo htmlspecialchars($nombre . ' ' . $apellidos); ?></strong>
            </p>
            <p>
                Rol actual: 
                <strong><?php echo htmlspecialchars($rol); ?></strong>
            </p>
        </header>

        <!-- Bloque de navegación principal -->
        <section class="dashboard-menu">
            <h2>Opciones disponibles</h2>

            <!-- Opciones para ADMIN -->
            <?php if (isAdmin()): ?>
                <div class="dashboard-card">
                    <h3>Administrador</h3>
                    <ul>
                        <li><a href="../usuarios/listar.php">Gestionar usuarios</a></li>
                        <li><a href="../incidencias/listar.php">Ver todas las incidencias</a></li>
                        <li><a href="../historial/listar.php">Ver historial del sistema</a></li>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Opciones para TÉCNICO -->
            <?php if (isTecnico()): ?>
                <div class="dashboard-card">
                    <h3>Técnico</h3>
                    <ul>
                        <li><a href="../incidencias/mis_asignadas.php">Mis incidencias asignadas</a></li>
                        <li><a href="../historial/listar.php">Consultar historial</a></li>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Opciones para CLIENTE -->
            <?php if (isCliente()): ?>
                <div class="dashboard-card">
                    <h3>Cliente</h3>
                    <ul>
                        <li><a href="../incidencias/crear.php">Crear incidencia</a></li>
                        <li><a href="../incidencias/mis_incidencias.php">Ver mis incidencias</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </section>

        <!-- Acción común para todos -->
        <section class="dashboard-actions">
            <a href="../auth/logout.php" class="btn-logout">Cerrar sesión</a>
        </section>

    </div>

</body>
</html>