<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('tecnico');

$usuario   = currentUser();
$tecnicoId = (int)($usuario['id'] ?? 0);

$incidencias = [];
$sql = "SELECT
            i.id_incidencia,
            i.codigo,
            i.titulo,
            i.fecha_creacion,
            e.nombre_estado,
            p.nombre_prioridad,
            c.nombre_categoria
        FROM incidencias i
        INNER JOIN estados    e ON i.estado_id    = e.id_estado
        INNER JOIN prioridades p ON i.prioridad_id = p.id_prioridad
        INNER JOIN categorias  c ON i.categoria_id = c.id_categoria
        WHERE i.tecnico_id = ?
        ORDER BY i.fecha_creacion DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param('i', $tecnicoId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($fila = $result->fetch_assoc()) {
        $incidencias[] = $fila;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis asignadas | Ticket Flow</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Mis incidencias asignadas</h1>
            <p>Tickets que tienes asignados para resolver.</p>
        </header>

        <section class="dashboard-actions actions-inline">
            <a href="../dashboard/dashboard.php" class="btn-logout">Volver al panel</a>
        </section>

        <section class="dashboard-card">
            <?php if (empty($incidencias)): ?>
                <p>No tienes incidencias asignadas en este momento.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="tabla-incidencias">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Titulo</th>
                                <th>Categoria</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Fecha</th>
                                <th>Accion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidencias as $inc): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($inc['codigo'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($inc['titulo']); ?></td>
                                    <td><?php echo htmlspecialchars($inc['nombre_categoria']); ?></td>
                                    <td><?php echo htmlspecialchars($inc['nombre_estado']); ?></td>
                                    <td><?php echo htmlspecialchars($inc['nombre_prioridad']); ?></td>
                                    <td><?php echo htmlspecialchars($inc['fecha_creacion']); ?></td>
                                    <td>
                                        <a href="detalle.php?id=<?php echo $inc['id_incidencia']; ?>" class="btn-ver">
                                            Ver detalle
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
