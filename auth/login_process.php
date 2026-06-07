<?php
// Cargamos el archivo de autenticación para trabajar con sesiones
require_once __DIR__ . '/../includes/auth.php';

// Cargamos la conexión a la base de datos
require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| 1. Comprobar que la conexión exista
|--------------------------------------------------------------------------
| En tu proyecto la variable de conexión se llama $conn.
*/
if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Error: la conexión $conn no está disponible en config/database.php');
}

/*
|--------------------------------------------------------------------------
| 2. Permitir solo peticiones POST
|--------------------------------------------------------------------------
| Este archivo solo debe recibir datos desde el formulario.
| Si alguien entra directamente por URL, lo devolvemos al login.
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| 3. Recoger y limpiar los datos del formulario
|--------------------------------------------------------------------------
*/
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

/*
|--------------------------------------------------------------------------
| 4. Validar campos vacíos
|--------------------------------------------------------------------------
*/
if ($email === '' || $password === '') {
    header('Location: login.php?error=campos');
    exit;
}

/*
|--------------------------------------------------------------------------
| 5. Buscar el usuario junto con su rol
|--------------------------------------------------------------------------
| Importante:
| - usuarios.rol_id conecta con roles.id_rol
| - el nombre real del rol está en roles.nombre_rol
| - también comprobamos si el usuario está activo
*/
$sql = "SELECT 
         u.id_usuario,
         u.nombre,
         u.apellidos,
         u.email,
         u.password,
         u.activo,
         u.rol_id,
         r.nombre_rol
        FROM usuarios u
        INNER JOIN roles r ON u.rol_id = r.id_rol
        WHERE u.email = ?
        LIMIT 1";

try {
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        header('Location: login.php?error=db');
        exit;
    }

    // Asociamos el parámetro email
    $stmt->bind_param('s', $email);

    // Ejecutamos la consulta
    $stmt->execute();

    // Obtenemos el resultado
    $resultado = $stmt->get_result();
} catch (mysqli_sql_exception $e) {
    header('Location: login.php?error=db');
    exit;
}

/*
|--------------------------------------------------------------------------
| 6. Verificar si el usuario existe
|--------------------------------------------------------------------------
*/
if ($resultado->num_rows !== 1) {
    $stmt->close();
    header('Location: login.php?error=credenciales');
    exit;
}

// Recuperamos al usuario encontrado
$usuario = $resultado->fetch_assoc();

// Cerramos la consulta
$stmt->close();

/*
|--------------------------------------------------------------------------
| 7. Verificar si el usuario está activo
|--------------------------------------------------------------------------
*/
if ((int)$usuario['activo'] !== 1) {
    header('Location: login.php?error=inactivo');
    exit;
}

/*
|--------------------------------------------------------------------------
| 8. Validar la contraseña
|--------------------------------------------------------------------------
| OJO:
| En tu base actual las contraseñas están guardadas en texto plano.
| Por eso aquí comparamos directamente con ===
|
| Más adelante cambiaremos esto a password_hash() y password_verify()
*/
if (!password_verify($password, $usuario['password'])) {
    header('Location: login.php?error=credenciales');
    exit;
}

/*
|--------------------------------------------------------------------------
| 9. Regenerar el ID de sesión
|--------------------------------------------------------------------------
| Buena práctica de seguridad al iniciar sesión
*/
session_regenerate_id(true);

/*
|--------------------------------------------------------------------------
| 10. Guardar datos del usuario en sesión
|--------------------------------------------------------------------------
| Guardamos el rol como texto: admin, tecnico o cliente
| porque eso es lo que espera tu includes/auth.php
*/
$_SESSION['usuario'] = [
    'id'        => (int)$usuario['id_usuario'],
    'nombre'    => trim($usuario['nombre']),
    'apellidos' => trim($usuario['apellidos']),
    'email'     => trim($usuario['email']),
    'rol_id'    => (int)$usuario['rol_id'],
    'rol'       => strtolower(trim($usuario['nombre_rol']))
];

/*
|--------------------------------------------------------------------------
| 11. Redirigir al dashboard
|--------------------------------------------------------------------------
*/
header('Location: ../dashboard/dashboard.php');
exit;
?>



