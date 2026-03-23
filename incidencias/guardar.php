<?php
// Cargamos autenticación y conexión
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Solo puede crear incidencias un cliente autenticado
requireRole('cliente');

// Verificamos la conexión
if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Error: la conexión $conn no está disponible en config/database.php');
}

// Solo aceptamos peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: crear.php');
    exit;
}

// Recogemos y limpiamos los datos del formulario
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$categoriaId = (int)($_POST['categoria_id'] ?? 0);

// Recuperamos el usuario actual desde sesión
$usuario = currentUser();
$clienteId = (int)($usuario['id'] ?? 0);

// Valores por defecto para una incidencia nueva
$estadoId = 1;      // abierta
$prioridadId = 2;   // media

// Validamos campos obligatorios
if ($titulo === '' || $descripcion === '' || $categoriaId <= 0 || $clienteId <= 0) {
    header('Location: crear.php?error=campos');
    exit;
}

// Iniciamos transacción para mantener coherencia
$conn->begin_transaction();

try {
    /*
    |--------------------------------------------------------------------------
    | 1. Insertar la incidencia
    |--------------------------------------------------------------------------
    | codigo se inserta inicialmente como NULL y luego se actualiza con el id
    | generado, por ejemplo: INC-0005
    */
    $sqlInsert = "INSERT INTO incidencias (
                    codigo,
                    titulo,
                    descripcion,
                    cliente_id,
                    tecnico_id,
                    categoria_id,
                    estado_id,
                    prioridad_id
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $codigoTemporal = null;
    $tecnicoId = null;

    $stmtInsert = $conn->prepare($sqlInsert);

    if (!$stmtInsert) {
        throw new Exception('Error al preparar la inserción de la incidencia: ' . $conn->error);
    }

    $stmtInsert->bind_param(
        'sssiiiii',
        $codigoTemporal,
        $titulo,
        $descripcion,
        $clienteId,
        $tecnicoId,
        $categoriaId,
        $estadoId,
        $prioridadId
    );

    if (!$stmtInsert->execute()) {
        throw new Exception('Error al insertar la incidencia: ' . $stmtInsert->error);
    }

    // Obtenemos el id de la nueva incidencia
    $incidenciaId = (int)$conn->insert_id;

    $stmtInsert->close();

    /*
    |--------------------------------------------------------------------------
    | 2. Generar el código legible
    |--------------------------------------------------------------------------
    | Formato: INC-0001, INC-0002, etc.
    */
    $codigo = 'INC-' . str_pad((string)$incidenciaId, 4, '0', STR_PAD_LEFT);

    $sqlUpdateCodigo = "UPDATE incidencias SET codigo = ? WHERE id_incidencia = ?";
    $stmtCodigo = $conn->prepare($sqlUpdateCodigo);

    if (!$stmtCodigo) {
        throw new Exception('Error al preparar la actualización del código: ' . $conn->error);
    }

    $stmtCodigo->bind_param('si', $codigo, $incidenciaId);

    if (!$stmtCodigo->execute()) {
        throw new Exception('Error al actualizar el código de la incidencia: ' . $stmtCodigo->error);
    }

    $stmtCodigo->close();

    /*
    |--------------------------------------------------------------------------
    | 3. Registrar historial básico
    |--------------------------------------------------------------------------
    */
    $accion = 'crear_incidencia';
    $descripcionHistorial = 'El cliente creó la incidencia ' . $codigo . '.';

    $sqlHistorial = "INSERT INTO historial_acciones (
                        incidencia_id,
                        usuario_id,
                        accion,
                        descripcion
                     ) VALUES (?, ?, ?, ?)";

    $stmtHistorial = $conn->prepare($sqlHistorial);

    if (!$stmtHistorial) {
        throw new Exception('Error al preparar el historial: ' . $conn->error);
    }

    $stmtHistorial->bind_param(
        'iiss',
        $incidenciaId,
        $clienteId,
        $accion,
        $descripcionHistorial
    );

    if (!$stmtHistorial->execute()) {
        throw new Exception('Error al registrar el historial: ' . $stmtHistorial->error);
    }

    $stmtHistorial->close();

    // Confirmamos transacción
    $conn->commit();

    // Redirigimos con éxito
    header('Location: crear.php?success=1');
    exit;

} catch (Exception $e) {
    // Deshacemos cambios si algo falla
    $conn->rollback();

    // En local puedes mostrar el error técnico para depurar
    die('Error al guardar la incidencia: ' . $e->getMessage());
}
?>