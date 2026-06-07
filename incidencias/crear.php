<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('cliente');

$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';

// Cargar categorías desde la BD
$categorias = [];
$resCat = $conn->query("SELECT id_categoria, nombre_categoria FROM categorias ORDER BY nombre_categoria ASC");
if ($resCat) {
    while ($cat = $resCat->fetch_assoc()) {
        $categorias[] = $cat;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear incidencia | Ticket Flow</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page create-incident-page">

    <div class="dashboard-container">
        <div class="create-layout">
            <header class="dashboard-header create-header">
                <h1>Nueva incidencia</h1>
                <p>Completa el formulario para registrar una nueva incidencia.</p>
            </header>

            <section class="dashboard-card create-card">

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

                <form action="guardar.php" method="POST" class="incident-form">
                    <?php echo csrfField(); ?>
                    <div class="form-group">
                        <label for="titulo">Título</label>
                        <input class="titulo-incidencia"
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
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo (int)$cat['id_categoria']; ?>">
                                    <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn-login">Crear incidencia</button>
                </form>
            </section>

            <section class="dashboard-actions create-actions">
                <a href="../dashboard/dashboard.php" class="btn-logout">Volver al dashboard</a>
            </section>
        </div>
    </div>

</body>
</html>

?>
