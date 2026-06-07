<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../historial/registrar_movimiento.php';

requireRole('admin');

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Error de conexión.');
}

$adminId      = (int)(currentUser()['id'] ?? 0);
$incidenciaId = (int)($_GET['id'] ?? 0);

if ($incidenciaId <= 0) {
    header('Location: listar.php');
    exit;
}

// ── Procesar formulario POST ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $prioridadId = (int)($_POST['prioridad_id'] ?? 0);
    $incIdPost   = (int)($_POST['incidencia_id'] ?? 0);

    if ($incIdPost <= 0 || $prioridadId <= 0) {
        header('Location: listar.php');
        exit;
    }

    // Verificar que la prioridad existe
    $stmtP = $conn->prepare("SELECT nombre_prioridad FROM prioridades WHERE id_prioridad = ? LIMIT 1");
    $stmtP->bind_param('i', $prioridadId);
    $stmtP->execute();
    $stmtP->bind_result($nombrePrioridad);
    if (!$stmtP->fetch()) {
        $stmtP->close();
        header('Location: cambiar_prioridad.php?id=' . $incIdPost . '&error=prioridad');
        exit;
    }
    $stmtP->close();

    $stmtUpd = $conn->prepare("UPDATE incidencias SET prioridad_id = ? WHERE id_incidencia = ?");
    $stmtUpd->bind_param('ii', $prioridadId, $incIdPost);

    if ($stmtUpd->execute()) {
        $desc = 'El administrador cambió la prioridad a "' . $nombrePrioridad . '".';
        registrarMovimiento($conn, $incIdPost, $adminId, 'cambiar_prioridad', $desc);
        header('Location: detalle.php?id=' . $incIdPost . '&ok=1');
    } else {
        header('Location: cambiar_prioridad.php?id=' . $incIdPost . '&error=bd');
    }
    exit;
}

// ── Cargar incidencia ─────────────────────────────────────────────────────────
$stmtInc = $conn->prepare(
    "SELECT i.id_incidencia, i.codigo, i.titulo, i.prioridad_id,
            p.nombre_prioridad AS prioridad_actual
     FROM incidencias i
     INNER JOIN prioridades p ON i.prioridad_id = p.id_prioridad
     WHERE i.id_incidencia = ? LIMIT 1"
);
$stmtInc->bind_param('i', $incidenciaId);
$stmtInc->execute();
$incidencia = $stmtInc->get_result()->fetch_assoc();
$stmtInc->close();

if (!$incidencia) {
    header('Location: listar.php');
    exit;
}

// ── Cargar prioridades ────────────────────────────────────────────────────────
$prioridades = $conn->query(
    "SELECT id_prioridad, nombre_prioridad FROM prioridades ORDER BY id_prioridad ASC"
)->fetch_all(MYSQLI_ASSOC);

$msgError = match ($_GET['error'] ?? '') {
    'bd'        => 'Error al guardar en la base de datos.',
    'prioridad' => 'La prioridad seleccionada no es válida.',
    default     => null,
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar prioridad | Ticket Flow</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">
<div class="dashboard-container">

    <header class="dashboard-header">
        <h1>Cambiar prioridad</h1>
        <p>Ticket: <strong><?php echo htmlspecialchars($incidencia['codigo'] ?? '-'); ?></strong> — <?php echo htmlspecialchars($incidencia['titulo']); ?></p>
    </header>

    <section class="dashboard-actions actions-inline">
        <a href="listar.php" class="btn-logout">Volver al listado</a>
        <a href="detalle.php?id=<?php echo $incidenciaId; ?>" class="btn-logout">Ver detalle</a>
    </section>

    <?php if ($msgError): ?>
        <div class="error-box"><?php echo htmlspecialchars($msgError); ?></div>
    <?php endif; ?>

    <section class="dashboard-card">
        <p><strong>Prioridad actual:</strong> <?php echo htmlspecialchars($incidencia['prioridad_actual']); ?></p>

        <form method="POST" action="cambiar_prioridad.php" class="incident-form">
            <?php echo csrfField(); ?>
            <input type="hidden" name="incidencia_id" value="<?php echo $incidenciaId; ?>">

            <div class="form-group">
                <label for="prioridad_id">Nueva prioridad</label>
                <select name="prioridad_id" id="prioridad_id">
                    <?php foreach ($prioridades as $p): ?>
                        <option value="<?php echo (int)$p['id_prioridad']; ?>"
                            <?php echo (int)$incidencia['prioridad_id'] === (int)$p['id_prioridad'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['nombre_prioridad']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-login">Guardar prioridad</button>
        </form>
    </section>

</div>
</body>
</html>
