<?PHP
session_start();
include("clases/mysql.inc.php");
include("clases/SanitizarEntrada.php");
include("comunes/loginfunciones.php"); // Assuming this has the redireccionar function

$db = new mod_db();

$tokenizado = false;

// Obtener tokens con seguridad y comprobar que existen
$token_enviado = $_POST['tolog'] ?? '';
$token_almacenado = $_SESSION['csrf_token'] ?? '';

// Verificar que ambos tokens no estén vacíos antes de comparar
if ($token_enviado !== '' && $token_almacenado !== '' && hash_equals($token_almacenado, $token_enviado)) {
    $tokenizado = true;
} else {
    $_SESSION["reg_msg"] = "Error de seguridad: Token CSRF inválido.";
    redireccionar("register_form.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenizado) {
    $nombre = SanitizarEntrada::limpiarCadena($_POST['nombre'] ?? '');
    $apellido = SanitizarEntrada::limpiarCadena($_POST['apellido'] ?? '');
    $correo = SanitizarEntrada::limpiarCadena($_POST['correo'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';
    $confirm_contrasena = $_POST['confirm_contrasena'] ?? '';
    $sexo = SanitizarEntrada::limpiarCadena($_POST['sexo'] ?? '');

    // Basic validation
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($contrasena) || empty($confirm_contrasena) || empty($sexo)) {
        $_SESSION["reg_msg"] = "Todos los campos son obligatorios.";
        redireccionar("register_form.php");
        exit;
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION["reg_msg"] = "El formato del correo electrónico no es válido.";
        redireccionar("register_form.php");
        exit;
    }

    if ($contrasena !== $confirm_contrasena) {
        $_SESSION["reg_msg"] = "Las contraseñas no coinciden.";
        redireccionar("register_form.php");
        exit;
    }

    if (strlen($contrasena) < 6) {
        $_SESSION["reg_msg"] = "La contraseña debe tener al menos 6 caracteres.";
        redireccionar("register_form.php");
        exit;
    }

    // Check if email already exists
    $stmt = $db->getConexion()->prepare("SELECT id FROM usuarios WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $_SESSION["reg_msg"] = "El correo electrónico ya está registrado.";
        redireccionar("register_form.php");
        exit;
    }

    // Hash the password
    $options = ['cost' => 13];
    $hashedPassword = password_hash($contrasena, PASSWORD_BCRYPT, $options);

    // Insert new user into the database
    $data = array(
        "nombre" => $nombre,
        "apellido" => $apellido,
        "correo" => $correo,
        "HashMagic" => $hashedPassword,
        "sexo" => $sexo,
        "secret_2fa" => null // Assuming 2FA is not implemented yet
    );

    if ($db->insertSeguro("usuarios", $data)) {
        $_SESSION["reg_msg"] = "¡Registro exitoso! Ahora puedes iniciar sesión.";
        redireccionar("login.php");
    } else {
        $_SESSION["reg_msg"] = "Error al registrar el usuario. Inténtelo de nuevo.";
        redireccionar("register_form.php");
    }

} else {
    $_SESSION["reg_msg"] = "Acceso no autorizado.";
    redireccionar("register_form.php");
}
?>
