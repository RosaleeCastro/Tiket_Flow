<?php
//cargamos la autenticacion

require_once __DIR__ . '/../includes/auth.php';

//Solo puede entrar un usuario con sesión iniciada y rol cliente 
requireRole('cliente');

//Recogemos posibles mensajes enviados por URL
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear incidencia | Ticket Flow</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Nueva incidencia</h1>
            <p>Completa el formulario para registrar una nueva incidencia.</p>
        </header>

        <section class="dashboard-card">

            <?php if ($error === 'campos'): ?>
                <div class="error-box">
                    Debes completar todos los campos obligatorios.
                </div>
            <?php endif; ?>

            <?php if ($success === '1'): ?>
                <div class="success-box">
                    La incidencia se registró correctamente.
                </div>
            <?php endif; ?>

            <form action="guardar.php" method="POST">
                <div class="form-group">
                    <label for="titulo">Título</label>
                    <input
                        type="text"
                        id="titulo"
                        name="titulo"
                        placeholder="Ejemplo: No puedo acceder al sistema"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea
                        id="descripcion"
                        name="descripcion"
                        rows="6"
                        placeholder="Explica con detalle qué problema estás teniendo"
                        required
                    ></textarea>
                </div>

                <div class="form-group">
                    <label for="categoria_id">Categoría</label>
                    <select id="categoria_id" name="categoria_id" required>
                        <option value="">Selecciona una categoría</option>
                        <option value="1">Hardware</option>
                        <option value="2">Software</option>
                        <option value="3">Red</option>
                        <option value="4">Accesos</option>
                        <option value="5">Impresoras</option>
                    </select>
                </div>

                <button type="submit" class="btn-login">Crear incidencia</button>
            </form>
        </section>

        <section class="dashboard-actions">
            <a href="../dashboard/dashboard.php" class="btn-logout">Volver al dashboard</a>
        </section>
    </div>

</body>
</html>

?>