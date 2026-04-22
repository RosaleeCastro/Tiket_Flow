<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requiereAnyRole(['cliente', 'tecnico']);

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Error: la conexion $conn no esta disponible en config/database.php');
}

$usuario      = currentUser();
$usuarioId    = (int)($usuario['id'] ?? 0);
$rol          = currentUserRole();
$incidenciaId = (int)($_GET['id'] ?? 0);

if ($incidenciaId <= 0 || $usuarioId <= 0) {
    header('Location: ' . ($rol === 'tecnico' ? 'mis_asignadas.php' : 'mis_incidencias.php'));
    exit;
}

/*
|--------------------------------------------------------------------------
| 1. Cargar detalle según rol
|--------------------------------------------------------------------------
*/
$sqlBase = "SELECT
                i.id_incidencia,
                i.codigo,
                i.titulo,
                i.descripcion,
                i.fecha_creacion,
                i.estado_id,
                c.nombre_categoria,
                e.nombre_estado,
                p.nombre_prioridad,
                CONCAT(u.nombre, ' ', u.apellidos) AS tecnico_asignado
            FROM incidencias i
            INNER JOIN categorias  c ON i.categoria_id = c.id_categoria
            INNER JOIN estados     e ON i.estado_id    = e.id_estado
            INNER JOIN prioridades p ON i.prioridad_id = p.id_prioridad
            LEFT  JOIN usuarios    u ON i.tecnico_id   = u.id_usuario
            WHERE i.id_incidencia = ?";

if ($rol === 'tecnico') {
    $sqlBase .= " AND i.tecnico_id = ? LIMIT 1";
} else {
    $sqlBase .= " AND i.cliente_id = ? LIMIT 1";
}

$stmtDetalle = $conn->prepare($sqlBase);
if (!$stmtDetalle) {
    die('Error al preparar la consulta: ' . $conn->error);
}
$stmtDetalle->bind_param('ii', $incidenciaId, $usuarioId);
$stmtDetalle->execute();
$resultDetalle = $stmtDetalle->get_result();

if ($resultDetalle->num_rows !== 1) {
    $stmtDetalle->close();
    header('Location: ' . ($rol === 'tecnico' ? 'mis_asignadas.php' : 'mis_incidencias.php'));
    exit;
}

$incidencia = $resultDetalle->fetch_assoc();
$stmtDetalle->close();

/*
|--------------------------------------------------------------------------
| 2. Cargar estados disponibles (solo necesario para técnico)
|--------------------------------------------------------------------------
*/
$estados = [];
if ($rol === 'tecnico') {
    $stmtEstados = $conn->prepare("SELECT id_estado, nombre_estado FROM estados ORDER BY id_estado ASC");
    if ($stmtEstados) {
        $stmtEstados->execute();
        $resEstados = $stmtEstados->get_result();
        while ($e = $resEstados->fetch_assoc()) {
            $estados[] = $e;
        }
        $stmtEstados->close();
    }
}

/*
|--------------------------------------------------------------------------
| 3. Cargar historial
|--------------------------------------------------------------------------
*/
$historial = [];
$sqlHistorial = "SELECT
                    h.fecha_creacion,
                    h.accion,
                    h.descripcion,
                    CONCAT(u.nombre, ' ', u.apellidos) AS usuario_nombre
                 FROM historial_acciones h
                 LEFT JOIN usuarios u ON h.usuario_id = u.id_usuario
                 WHERE h.incidencia_id = ?
                 ORDER BY h.fecha_creacion DESC, h.id_historial DESC";

$stmtHistorial = $conn->prepare($sqlHistorial);
if ($stmtHistorial) {
    $stmtHistorial->bind_param('i', $incidenciaId);
    $stmtHistorial->execute();
    $resHistorial = $stmtHistorial->get_result();
    while ($fila = $resHistorial->fetch_assoc()) {
        $historial[] = $fila;
    }
    $stmtHistorial->close();
}

/*
|--------------------------------------------------------------------------
| 4. Cargar comentarios
|--------------------------------------------------------------------------
*/
$comentarios = [];
$sqlComentarios = "SELECT
                      c.fecha_creacion,
                      c.comentario,
                      CONCAT(u.nombre, ' ', u.apellidos) AS usuario_nombre
                   FROM comentarios c
                   INNER JOIN usuarios u ON c.usuario_id = u.id_usuario
                   WHERE c.incidencia_id = ?
                   ORDER BY c.fecha_creacion ASC, c.id_comentario ASC";

$stmtComentarios = $conn->prepare($sqlComentarios);
if ($stmtComentarios) {
    $stmtComentarios->bind_param('i', $incidenciaId);
    $stmtComentarios->execute();
    $resComentarios = $stmtComentarios->get_result();
    while ($fila = $resComentarios->fetch_assoc()) {
        $comentarios[] = $fila;
    }
    $stmtComentarios->close();
}

$urlVolver  = $rol === 'tecnico' ? 'mis_asignadas.php' : 'mis_incidencias.php';
$textoVolver = $rol === 'tecnico' ? 'Volver a mis asignadas' : 'Volver a mis incidencias';

$msgOk    = isset($_GET['ok'])    ? 'Estado actualizado correctamente.' : null;
$msgError = match ($_GET['error'] ?? '') {
    'permiso' => 'No tienes permiso para modificar esta incidencia.',
    'estado'  => 'El estado seleccionado no es valido.',
    'datos'   => 'Datos incompletos. Intenta de nuevo.',
    'bd'      => 'Error al guardar en la base de datos.',
    default   => null,
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de incidencia | Ticket Flow</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Detalle de incidencia</h1>
            <p>Informacion completa del ticket seleccionado.</p>
        </header>

        <section class="dashboard-actions actions-inline">
            <a href="<?php echo $urlVolver; ?>" class="btn-logout"><?php echo $textoVolver; ?></a>
        </section>

        <?php if ($msgOk): ?>
            <div class="success-box"><?php echo htmlspecialchars($msgOk); ?></div>
        <?php endif; ?>

        <?php if ($msgError): ?>
            <div class="error-box"><?php echo htmlspecialchars($msgError); ?></div>
        <?php endif; ?>

        <section class="dashboard-card detail-card">
            <h2><?php echo htmlspecialchars($incidencia['titulo']); ?></h2>
            <p><strong>Codigo:</strong> <?php echo htmlspecialchars($incidencia['codigo'] ?? 'Sin codigo'); ?></p>
            <p><strong>Categoria:</strong> <?php echo htmlspecialchars($incidencia['nombre_categoria']); ?></p>
            <p><strong>Estado:</strong> <?php echo htmlspecialchars($incidencia['nombre_estado']); ?></p>
            <p><strong>Prioridad:</strong> <?php echo htmlspecialchars($incidencia['nombre_prioridad']); ?></p>
            <p><strong>Tecnico asignado:</strong> <?php echo htmlspecialchars($incidencia['tecnico_asignado'] ?: 'Sin asignar'); ?></p>
            <p><strong>Fecha de creacion:</strong> <?php echo htmlspecialchars($incidencia['fecha_creacion']); ?></p>
            <hr>
            <p><strong>Descripcion</strong></p>
            <p><?php echo nl2br(htmlspecialchars($incidencia['descripcion'])); ?></p>
        </section>

        <?php if ($rol === 'tecnico' && !empty($estados)): ?>
        <section class="dashboard-card detail-card">
            <h3>Cambiar estado</h3>
            <form method="POST" action="actualizar.php" class="incident-form">
                <input type="hidden" name="incidencia_id" value="<?php echo $incidencia['id_incidencia']; ?>">
                <div class="form-group">
                    <label for="estado_id">Nuevo estado</label>
                    <select name="estado_id" id="estado_id">
                        <?php foreach ($estados as $e): ?>
                            <option
                                value="<?php echo $e['id_estado']; ?>"
                                <?php echo (int)$e['id_estado'] === (int)$incidencia['estado_id'] ? 'selected' : ''; ?>
                            >
                                <?php echo htmlspecialchars($e['nombre_estado']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-login">Guardar estado</button>
            </form>
        </section>
        <?php endif; ?>

        <section class="dashboard-card detail-card">
            <h3>Historial</h3>
            <?php if (!empty($historial)): ?>
                <div class="table-responsive">
                    <table class="tabla-incidencias">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Accion</th>
                                <th>Descripcion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['fecha_creacion']); ?></td>
                                    <td><?php echo htmlspecialchars($item['usuario_nombre'] ?: 'Sistema'); ?></td>
                                    <td><?php echo htmlspecialchars($item['accion']); ?></td>
                                    <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No hay historial registrado para esta incidencia.</p>
            <?php endif; ?>
        </section>

        <section class="dashboard-card">
            <h3>Comentarios</h3>
            <?php if (!empty($comentarios)): ?>
                <?php foreach ($comentarios as $item): ?>
                    <div class="comment-item">
                        <p><strong><?php echo htmlspecialchars($item['usuario_nombre']); ?></strong></p>
                        <p><?php echo nl2br(htmlspecialchars($item['comentario'])); ?></p>
                        <small><?php echo htmlspecialchars($item['fecha_creacion']); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay comentarios todavia para esta incidencia.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
