<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../historial/registrar_movimiento.php';

requireRole('tecnico');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mis_asignadas.php');
    exit;
}

$usuario      = currentUser();
$tecnicoId    = (int)($usuario['id'] ?? 0);
$incidenciaId = (int)($_POST['incidencia_id'] ?? 0);
$nuevoEstadoId = (int)($_POST['estado_id'] ?? 0);

if ($incidenciaId <= 0 || $nuevoEstadoId <= 0 || $tecnicoId <= 0) {
    header("Location: detalle.php?id={$incidenciaId}&error=datos");
    exit;
}

// Verificar que el estado existe
$stmtEstado = $conn->prepare("SELECT id_estado, nombre_estado FROM estados WHERE id_estado = ? LIMIT 1");
$stmtEstado->bind_param('i', $nuevoEstadoId);
$stmtEstado->execute();
$resultEstado = $stmtEstado->get_result();
if ($resultEstado->num_rows !== 1) {
    $stmtEstado->close();
    header("Location: detalle.php?id={$incidenciaId}&error=estado");
    exit;
}
$estado = $resultEstado->fetch_assoc();
$stmtEstado->close();

// Verificar que la incidencia pertenece a este técnico
$stmtCheck = $conn->prepare(
    "SELECT id_incidencia, estado_id, codigo FROM incidencias WHERE id_incidencia = ? AND tecnico_id = ? LIMIT 1"
);
$stmtCheck->bind_param('ii', $incidenciaId, $tecnicoId);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();
if ($resultCheck->num_rows !== 1) {
    $stmtCheck->close();
    header("Location: detalle.php?id={$incidenciaId}&error=permiso");
    exit;
}
$incidencia = $resultCheck->fetch_assoc();
$stmtCheck->close();

// Si ya tiene ese estado no hacer nada
if ((int)$incidencia['estado_id'] === $nuevoEstadoId) {
    header("Location: detalle.php?id={$incidenciaId}&ok=1");
    exit;
}

// Actualizar estado
$stmtUpdate = $conn->prepare("UPDATE incidencias SET estado_id = ? WHERE id_incidencia = ?");
$stmtUpdate->bind_param('ii', $nuevoEstadoId, $incidenciaId);
if (!$stmtUpdate->execute()) {
    $stmtUpdate->close();
    header("Location: detalle.php?id={$incidenciaId}&error=bd");
    exit;
}
$stmtUpdate->close();

// Registrar en historial
$descripcion = 'El técnico cambió el estado a "' . $estado['nombre_estado'] . '" en ' . $incidencia['codigo'] . '.';
registrarMovimiento($conn, $incidenciaId, $tecnicoId, 'cambiar_estado', $descripcion);

header("Location: detalle.php?id={$incidenciaId}&ok=1");
exit;
