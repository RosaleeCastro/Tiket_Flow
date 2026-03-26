<?php

  require_once __DIR__ . '/../includes/auth.php';
  require_once __DIR__ . '/../config/database.php';


  // Solo puede entrar cliente autenticado 
  requireRole('cliente');

  //Verificamos conexión

  if(!isset($conn) || !($conn instanceof mysqli)){
    dir('Error: la conexión $conn no está disponible en config/database.php');
}
  
//Obtenemos el usuario actual desde la sesion 
$usuario = currentUser();
$clienteId = (int)($usuario['id'] ?? 0);

// Si no hay el id del cliente, detenemos 

if($clienteId <= 0){
  die('Error: no se pudo identificar al cliente autenticado.');
}
/*
|--------------------------------------------------------------------------
| Consulta del listado de incidencias del cliente
|--------------------------------------------------------------------------
| Traemos:
| - código
| - título
| - categoría
| - estado
| - prioridad
| - técnico asignado (si existe)
| - fecha de creación
*/
$sql = "SELECT
            i.id_incidencia,
            i.codigo,
            i.titulo,
            i.fecha_creacion,
            c.nombre_categoria,
            e.nombre_estado,
            p.nombre_prioridad,
            CONCAT(u.nombre, ' ', u.apellidos) AS tecnico_asignado
        FROM incidencias i
        INNER JOIN categorias c ON i.categoria_id = c.id_categoria
        INNER JOIN estados e ON i.estado_id = e.id_estado
        INNER JOIN prioridades p ON i.prioridad_id = p.id_prioridad
        LEFT JOIN usuarios u ON i.tecnico_id = u.id_usuario
        WHERE i.cliente_id = ?
        ORDER BY i.fecha_creacion DESC";

$stmt = $conn->prepare($sql);

if(!$stmt){
  die('Error al preparar la consulta: ' . $conn->error);
}

$stmt->bind_param('i', $clienteId);
$stmt->execute();
$resultado = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis incidencias | Ticket Flow</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1>Mis incidencias</h1>
            <p>Aquí puedes ver todas las incidencias que has creado.</p>
        </header>

        <section class="dashboard-actions actions-inline">
            <a href="crear.php" class="btn-login">
                Crear nueva incidencia
            </a>
            <a href="../dashboard/dashboard.php" class="btn-logout">
                Volver al dashboard
            </a>
        </section>

        <section class="dashboard-card">
            <?php if ($resultado->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="tabla-incidencias">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Título</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Técnico asignado</th>
                                <th>Fecha</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = $resultado->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($fila['codigo'] ?? 'Sin código'); ?></td>
                                    <td><?php echo htmlspecialchars($fila['titulo']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['nombre_categoria']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['nombre_estado']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['nombre_prioridad']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($fila['tecnico_asignado'] ?: 'Sin asignar'); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($fila['fecha_creacion']); ?></td>
                                    <td>
                                        <a href="detalle.php?id=<?php echo (int)$fila['id_incidencia']; ?>">
                                            Ver detalle
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No tienes incidencias registradas todavía.</p>
            <?php endif; ?>
        </section>
    </div>

</body>
</html>

?>
