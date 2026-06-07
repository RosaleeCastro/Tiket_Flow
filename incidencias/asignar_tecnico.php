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

    $tecnicoId    = (int)($_POST['tecnico_id'] ?? 0);
    $incIdPost    = (int)($_POST['incidencia_id'] ?? 0);

    if ($incIdPost <= 0) {
        header('Location: listar.php');
        exit;
    }

    // tecnico_id puede ser 0 para desasignar
    $stmtUpd = $conn->prepare("UPDATE incidencias SET tecnico_id = ? WHERE id_incidencia = ?");
    $tecnicoParam = $tecnicoId > 0 ? $tecnicoId : null;
    $stmtUpd->bind_param('ii', $tecnicoParam, $incIdPost);

    if ($stmtUpd->execute()) {
        if ($tecnicoId > 0) {
            $resTec = $conn->prepare("SELECT CONCAT(nombre,' ',apellidos) AS nombre FROM usuarios WHERE id_usuario = ?");
            $resTec->bind_param('i', $tecnicoId);
            $resTec->execute();
            $resTec->bind_result($nombreTec);
            $resTec->fetch();
            $resTec->close();
            $desc = 'El administrador asignó el ticket al técnico ' . $nombreTec . '.';
        } else {
            $desc = 'El administrador desasignó el técnico del ticket.';
        }
        registrarMovimiento($conn, $incIdPost, $adminId, 'asignar_tecnico', $desc);
        header('Location: detalle.php?id=' . $incIdPost . '&ok=1');
    } else {
        header('Location: asignar_tecnico.php?id=' . $incIdPost . '&error=bd');
    }
    exit;
}

// ── Cargar incidencia ─────────────────────────────────────────────────────────
$stmtInc = $conn->prepare(
    "SELECT i.id_incidencia, i.codigo, i.titulo, i.tecnico_id,
            CONCAT(u.nombre,' ',u.apellidos) AS tecnico_actual
     FROM incidencias i
     LEFT JOIN usuarios u ON i.tecnico_id = u.id_usuario
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

// ── Cargar técnicos disponibles ───────────────────────────────────────────────
$tecnicos = $conn->query(
    "SELECT u.id_usuario, CONCAT(u.nombre,' ',u.apellidos) AS nombre_completo
     FROM usuarios u
     INNER JOIN roles r ON u.rol_id = r.id_rol
     WHERE r.nombre_rol = 'tecnico' AND u.activo = 1
     ORDER BY u.nombre ASC"
)->fetch_all(MYSQLI_ASSOC);

$msgError = match ($_GET['error'] ?? '') {
    'bd' => 'Error al guardar en la base de datos.',
    default => null,
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar técnico | Ticket Flow</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">
<div class="dashboard-container">

    <header class="dashboard-header">
        <h1>Asignar técnico</h1>
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
        <p><strong>Técnico actual:</strong> <?php echo htmlspecialchars($incidencia['tecnico_actual'] ?: 'Sin asignar'); ?></p>

        <form method="POST" action="asignar_tecnico.php" class="incident-form">
            <?php echo csrfField(); ?>
            <input type="hidden" name="incidencia_id" value="<?php echo $incidenciaId; ?>">

            <div class="form-group">
                <label for="tecnico_id">Selecciona un técnico</label>
                <select name="tecnico_id" id="tecnico_id">
                    <option value="0">— Sin asignar —</option>
                    <?php foreach ($tecnicos as $tec): ?>
                        <option value="<?php echo (int)$tec['id_usuario']; ?>"
                            <?php echo (int)$incidencia['tecnico_id'] === (int)$tec['id_usuario'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tec['nombre_completo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn-login">Guardar asignación</button>
        </form>
    </section>

</div>
</body>
</html>
