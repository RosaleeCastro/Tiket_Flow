<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('admin');

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Error de conexión.');
}

// ── Filtros desde GET ─────────────────────────────────────────────────────────
$filtroEstado    = (int)($_GET['estado'] ?? 0);
$filtroPrioridad = (int)($_GET['prioridad'] ?? 0);
$filtroCategoria = (int)($_GET['categoria'] ?? 0);
$filtroBusqueda  = trim($_GET['q'] ?? '');

// ── Paginación ────────────────────────────────────────────────────────────────
$porPagina    = 15;
$paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
$offset       = ($paginaActual - 1) * $porPagina;

// ── Construir WHERE dinámico ──────────────────────────────────────────────────
$condiciones = [];
$params      = [];
$tipos       = '';

if ($filtroEstado > 0) {
    $condiciones[] = 'i.estado_id = ?';
    $params[]      = $filtroEstado;
    $tipos        .= 'i';
}
if ($filtroPrioridad > 0) {
    $condiciones[] = 'i.prioridad_id = ?';
    $params[]      = $filtroPrioridad;
    $tipos        .= 'i';
}
if ($filtroCategoria > 0) {
    $condiciones[] = 'i.categoria_id = ?';
    $params[]      = $filtroCategoria;
    $tipos        .= 'i';
}
if ($filtroBusqueda !== '') {
    $condiciones[] = '(i.titulo LIKE ? OR i.descripcion LIKE ? OR i.codigo LIKE ?)';
    $likeTerm      = '%' . $filtroBusqueda . '%';
    $params[]      = $likeTerm;
    $params[]      = $likeTerm;
    $params[]      = $likeTerm;
    $tipos        .= 'sss';
}

$where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';

// ── Total para paginación ─────────────────────────────────────────────────────
$sqlCount  = "SELECT COUNT(*) FROM incidencias i $where";
$stmtCount = $conn->prepare($sqlCount);
if ($params) {
    $stmtCount->bind_param($tipos, ...$params);
}
$stmtCount->execute();
$stmtCount->bind_result($totalRegistros);
$stmtCount->fetch();
$stmtCount->close();
$totalPaginas = (int)ceil($totalRegistros / $porPagina);

// ── Listado paginado ──────────────────────────────────────────────────────────
$sql = "SELECT
            i.id_incidencia,
            i.codigo,
            i.titulo,
            i.fecha_creacion,
            c.nombre_categoria,
            e.nombre_estado,
            p.nombre_prioridad,
            CONCAT(cli.nombre, ' ', cli.apellidos) AS cliente_nombre,
            CONCAT(tec.nombre, ' ', tec.apellidos) AS tecnico_nombre
        FROM incidencias i
        INNER JOIN categorias  c   ON i.categoria_id = c.id_categoria
        INNER JOIN estados     e   ON i.estado_id    = e.id_estado
        INNER JOIN prioridades p   ON i.prioridad_id = p.id_prioridad
        INNER JOIN usuarios    cli ON i.cliente_id   = cli.id_usuario
        LEFT  JOIN usuarios    tec ON i.tecnico_id   = tec.id_usuario
        $where
        ORDER BY i.fecha_creacion DESC
        LIMIT ? OFFSET ?";

$stmtList = $conn->prepare($sql);
$paramsConPag = array_merge($params, [$porPagina, $offset]);
$tiposConPag  = $tipos . 'ii';
$stmtList->bind_param($tiposConPag, ...$paramsConPag);
$stmtList->execute();
$resultado = $stmtList->get_result();

// ── Opciones de filtros ───────────────────────────────────────────────────────
$estados    = $conn->query("SELECT id_estado, nombre_estado FROM estados ORDER BY id_estado")->fetch_all(MYSQLI_ASSOC);
$prioridades = $conn->query("SELECT id_prioridad, nombre_prioridad FROM prioridades ORDER BY id_prioridad")->fetch_all(MYSQLI_ASSOC);
$categorias = $conn->query("SELECT id_categoria, nombre_categoria FROM categorias ORDER BY nombre_categoria")->fetch_all(MYSQLI_ASSOC);

// URL base para mantener filtros en la paginación
$queryParams = array_filter([
    'estado'    => $filtroEstado    ?: null,
    'prioridad' => $filtroPrioridad ?: null,
    'categoria' => $filtroCategoria ?: null,
    'q'         => $filtroBusqueda  ?: null,
]);
$queryBase = $queryParams ? '&' . http_build_query($queryParams) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todas las incidencias | Ticket Flow</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">
<div class="dashboard-container">

    <header class="dashboard-header">
        <h1>Todas las incidencias</h1>
        <p>Panel de administración — <?php echo $totalRegistros; ?> incidencia<?php echo $totalRegistros !== 1 ? 's' : ''; ?> encontrada<?php echo $totalRegistros !== 1 ? 's' : ''; ?>.</p>
    </header>

    <section class="dashboard-actions actions-inline">
        <a href="../dashboard/dashboard.php" class="btn-logout">Volver al panel</a>
    </section>

    <!-- Filtros -->
    <section class="dashboard-card">
        <form method="GET" action="listar.php" class="filtros-form">
            <div class="filtros-row">
                <input type="text" name="q" value="<?php echo htmlspecialchars($filtroBusqueda); ?>" placeholder="Buscar por código, título…" class="filtro-input">

                <select name="estado" class="filtro-select">
                    <option value="0">Todos los estados</option>
                    <?php foreach ($estados as $e): ?>
                        <option value="<?php echo $e['id_estado']; ?>" <?php echo $filtroEstado === (int)$e['id_estado'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($e['nombre_estado']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="prioridad" class="filtro-select">
                    <option value="0">Todas las prioridades</option>
                    <?php foreach ($prioridades as $p): ?>
                        <option value="<?php echo $p['id_prioridad']; ?>" <?php echo $filtroPrioridad === (int)$p['id_prioridad'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['nombre_prioridad']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="categoria" class="filtro-select">
                    <option value="0">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id_categoria']; ?>" <?php echo $filtroCategoria === (int)$cat['id_categoria'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn-login">Filtrar</button>
                <a href="listar.php" class="btn-logout">Limpiar</a>
            </div>
        </form>
    </section>

    <!-- Tabla -->
    <section class="dashboard-card">
        <?php if ($resultado->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="tabla-incidencias">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Título</th>
                            <th>Cliente</th>
                            <th>Técnico</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Prioridad</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['codigo'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($fila['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($fila['cliente_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($fila['tecnico_nombre'] ?: 'Sin asignar'); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre_categoria']); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre_estado']); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre_prioridad']); ?></td>
                                <td><?php echo htmlspecialchars($fila['fecha_creacion']); ?></td>
                                <td class="acciones-td">
                                    <a href="detalle.php?id=<?php echo (int)$fila['id_incidencia']; ?>" class="btn-ver">Ver</a>
                                    <a href="asignar_tecnico.php?id=<?php echo (int)$fila['id_incidencia']; ?>" class="btn-accion">Asignar</a>
                                    <a href="cambiar_prioridad.php?id=<?php echo (int)$fila['id_incidencia']; ?>" class="btn-accion">Prioridad</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No se encontraron incidencias con los filtros seleccionados.</p>
        <?php endif; ?>

        <?php if ($totalPaginas > 1): ?>
            <nav class="paginacion">
                <?php if ($paginaActual > 1): ?>
                    <a href="?pagina=<?php echo $paginaActual - 1; ?><?php echo $queryBase; ?>" class="btn-pag">&laquo; Anterior</a>
                <?php endif; ?>
                <span>Página <?php echo $paginaActual; ?> de <?php echo $totalPaginas; ?></span>
                <?php if ($paginaActual < $totalPaginas): ?>
                    <a href="?pagina=<?php echo $paginaActual + 1; ?><?php echo $queryBase; ?>" class="btn-pag">Siguiente &raquo;</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </section>

</div>
</body>
</html>
