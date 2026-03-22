<?php
// Cargamos autenticación para tener la sesión disponible si hace falta
require_once __DIR__ . '/../includes/auth.php';

// Cargamos la conexión a la base de datos
require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| 1. Verificar conexión
|--------------------------------------------------------------------------
*/
if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Error: la conexión $conn no está disponible en config/database.php');
}

/*
|--------------------------------------------------------------------------
| 2. Permitir solo peticiones POST
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| 3. Recoger y limpiar datos
|--------------------------------------------------------------------------
*/
$nombre = trim($_POST['nombre'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$email = trim($_POST['email'] ?? '');
$empresa = trim($_POST['empresa'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirmPassword = trim($_POST['confirm_password'] ?? '');

/*
|--------------------------------------------------------------------------
| 4. Validar campos obligatorios
|--------------------------------------------------------------------------
*/
if (
    $nombre === '' ||
    $apellidos === '' ||
    $email === '' ||
    $empresa === '' ||
    $password === '' ||
    $confirmPassword === ''
) {
    header('Location: registro.php?error=campos');
    exit;
}

/*
|--------------------------------------------------------------------------
| 5. Validar formato de email
|--------------------------------------------------------------------------
*/
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: registro.php?error=campos');
    exit;
}

/*
|--------------------------------------------------------------------------
| 6. Validar coincidencia de contraseñas
|--------------------------------------------------------------------------
*/
if ($password !== $confirmPassword) {
    header('Location: registro.php?error=password_mismatch');
    exit;
}

/*
|--------------------------------------------------------------------------
| 7. Comprobar si el email ya existe
|--------------------------------------------------------------------------
*/
$sqlCheck = "SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1";
$stmtCheck = $conn->prepare($sqlCheck);

if (!$stmtCheck) {
    header('Location: registro.php?error=db');
    exit;
}

$stmtCheck->bind_param('s', $email);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if ($resultCheck->num_rows > 0) {
    $stmtCheck->close();
    header('Location: registro.php?error=email_exists');
    exit;
}

$stmtCheck->close();

/*
|--------------------------------------------------------------------------
| 8. Buscar el id del rol cliente
|--------------------------------------------------------------------------
| No lo hardcodeamos para aprender una solución más robusta.
*/
$sqlRol = "SELECT id_rol FROM roles WHERE nombre_rol = 'cliente' LIMIT 1";
$resultRol = $conn->query($sqlRol);

if (!$resultRol || $resultRol->num_rows !== 1) {
    header('Location: registro.php?error=db');
    exit;
}

$rolCliente = $resultRol->fetch_assoc();
$rolId = (int)$rolCliente['id_rol'];

/*
|--------------------------------------------------------------------------
| 9. Insertar el nuevo usuario
|--------------------------------------------------------------------------
| Por coherencia con tu login actual, guardamos la contraseña tal cual.
| Más adelante lo cambiaremos a password_hash().
*/
$sqlInsert = "INSERT INTO usuarios (
                nombre,
                apellidos,
                email,
                password,
                empresa,
                activo,
                rol_id
              ) VALUES (?, ?, ?, ?, ?, 1, ?)";

$stmtInsert = $conn->prepare($sqlInsert);

if (!$stmtInsert) {
    header('Location: registro.php?error=db');
    exit;
}

$stmtInsert->bind_param(
    'sssssi',
    $nombre,
    $apellidos,
    $email,
    $password,
    $empresa,
    $rolId
);

if (!$stmtInsert->execute()) {
    $stmtInsert->close();
    header('Location: registro.php?error=db');
    exit;
}

$stmtInsert->close();

/*
|--------------------------------------------------------------------------
| 10. Redirigir con éxito
|--------------------------------------------------------------------------
*/
header('Location: registro.php?success=1');
exit;
?>