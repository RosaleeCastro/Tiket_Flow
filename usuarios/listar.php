<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole('admin');

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Error de conexión.');
}

$adminId = (int)(currentUser()['id'] ?? 0);

// ── Acción de activar/desactivar via POST ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $accion    = $_POST['accion'] ?? '';
    $usuarioId = (int)($_POST['usuario_id'] ?? 0);

    if ($usuarioId > 0 && $usuarioId !== $adminId) {
        if ($accion === 'activar') {
            $stmtToggle = $conn->prepare("UPDATE usuarios SET activo = 1 WHERE id_usuario = ?");
            $stmtToggle->bind_param('i', $usuarioId);
            $stmtToggle->execute();
            $stmtToggle->close();
        } elseif ($accion === 'desactivar') {
            $stmtToggle = $conn->prepare("UPDATE usuarios SET activo = 0 WHERE id_usuario = ?");
            $stmtToggle->bind_param('i', $usuarioId);
            $stmtToggle->execute();
            $stmtToggle->close();
        }
    }

    header('Location: listar.php?ok=1');
    exit;
}

// ── Filtros ───────────────────────────────────────────────────────────────────
$filtroRol    = (int)($_GET['rol'] ?? 0);
$filtroActivo = $_GET['activo'] ?? '';
$filtroBusqueda = trim($_GET['q'] ?? '');

$condiciones = [];
$params      = [];
$tipos       = '';

if ($filtroRol > 0) {
    $condiciones[] = 'u.rol_id = ?';
    $params[]      = $filtroRol;
    $tipos        .= 'i';
}
if ($filtroActivo !== '') {
    $condiciones[] = 'u.activo = ?';
    $params[]      = (int)$filtroActivo;
    $tipos        .= 'i';
}
if ($filtroBusqueda !== '') {
    $condiciones[] = '(u.nombre LIKE ? OR u.apellidos LIKE ? OR u.email LIKE ?)';
    $like          = '%' . $filtroBusqueda . '%';
    $params[]      = $like;
    $params[]      = $like;
    $params[]      = $like;
    $tipos        .= 'sss';
}

$where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';

// ── Paginación ────────────────────────────────────────────────────────────────
$porPagina    = 15;
$paginaActual = max(1, (int)($_GET['pagina'] ?? 1));
$offset       = ($paginaActual - 1) * $porPagina;

$stmtCount = $conn->prepare("SELECT COUNT(*) FROM usuarios u $where");
if ($params) { $stmtCount->bind_param($tipos, ...$params); }
$stmtCount->execute();
$stmtCount->bind_result($totalRegistros);
$stmtCount->fetch();
$stmtCount->close();
$totalPaginas = (int)ceil($totalRegistros / $porPagina);

// ── Listado ───────────────────────────────────────────────────────────────────
$sql = "SELECT
            u.id_usuario,
            u.nombre,
            u.apellidos,
            u.email,
            u.empresa,
            u.activo,
            r.nombre_rol
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id_rol
        $where
        ORDER BY u.nombre ASC
        LIMIT ? OFFSET ?";

$stmtList = $conn->prepare($sql);
$paramsConPag = array_merge($params, [$porPagina, $offset]);
$tiposConPag  = $tipos . 'ii';
$stmtList->bind_param($tiposConPag, ...$paramsConPag);
$stmtList->execute();
$resultado = $stmtList->get_result();

// ── Roles para filtro ─────────────────────────────────────────────────────────
$roles = $conn->query("SELECT id_rol, nombre_rol FROM roles ORDER BY id_rol")->fetch_all(MYSQLI_ASSOC);

$queryParams = array_filter([
    'rol'    => $filtroRol      ?: null,
    'activo' => $filtroActivo !== '' ? $filtroActivo : null,
    'q'      => $filtroBusqueda ?: null,
]);
$queryBase = $queryParams ? '&' . http_build_query($queryParams) : '';

$msgOk = isset($_GET['ok']) ? 'Usuario actualizado correctamente.' : null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de usuarios | Ticket Flow</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">
<div class="dashboard-container">

    <header class="dashboard-header">
        <h1>Gestión de usuarios</h1>
        <p><?php echo $totalRegistros; ?> usuario<?php echo $totalRegistros !== 1 ? 's' : ''; ?> en el sistema.</p>
    </header>

    <section class="dashboard-actions actions-inline">
        <a href="../dashboard/dashboard.php" class="btn-logout">Volver al panel</a>
        <a href="crear.php" class="btn-login">Nuevo usuario</a>
    </section>

    <?php if ($msgOk): ?>
        <div class="success-box"><?php echo htmlspecialchars($msgOk); ?></div>
    <?php endif; ?>

    <!-- Filtros -->
    <section class="dashboard-card">
        <form method="GET" action="listar.php" class="filtros-form">
            <div class="filtros-row">
                <input type="text" name="q" value="<?php echo htmlspecialchars($filtroBusqueda); ?>" placeholder="Buscar por nombre o email…" class="filtro-input">

                <select name="rol" class="filtro-select">
                    <option value="0">Todos los roles</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?php echo $r['id_rol']; ?>" <?php echo $filtroRol === (int)$r['id_rol'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars(ucfirst($r['nombre_rol'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="activo" class="filtro-select">
                    <option value="">Todos los estados</option>
                    <option value="1" <?php echo $filtroActivo === '1' ? 'selected' : ''; ?>>Activos</option>
                    <option value="0" <?php echo $filtroActivo === '0' ? 'selected' : ''; ?>>Inactivos</option>
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
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Empresa</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellidos']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo htmlspecialchars($u['empresa'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($u['nombre_rol'])); ?></td>
                                <td>
                                    <?php if ((int)$u['activo'] === 1): ?>
                                        <span class="badge-activo">Activo</span>
                                    <?php else: ?>
                                        <span class="badge-inactivo">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="acciones-td">
                                    <a href="editar.php?id=<?php echo (int)$u['id_usuario']; ?>" class="btn-accion">Editar</a>

                                    <?php if ((int)$u['id_usuario'] !== $adminId): ?>
                                        <form method="POST" action="listar.php" style="display:inline;">
                                            <?php echo csrfField(); ?>
                                            <input type="hidden" name="usuario_id" value="<?php echo (int)$u['id_usuario']; ?>">
                                            <?php if ((int)$u['activo'] === 1): ?>
                                                <input type="hidden" name="accion" value="desactivar">
                                                <button type="submit" class="btn-peligro" onclick="return confirm('¿Desactivar este usuario?')">Desactivar</button>
                                            <?php else: ?>
                                                <input type="hidden" name="accion" value="activar">
                                                <button type="submit" class="btn-login">Activar</button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No se encontraron usuarios con los filtros seleccionados.</p>
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
