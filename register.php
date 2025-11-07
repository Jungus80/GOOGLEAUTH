<?PHP
session_start();
include("clases/mysql.inc.php");
include("clases/SanitizarEntrada.php");
include("clases/RegistrationHandler.php"); // Include the new RegistrationHandler class
include("comunes/loginfunciones.php"); // Assuming this has the redireccionar function

$db = new mod_db();
$registrationHandler = new RegistrationHandler($db);

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
    $formData = $_POST;

    // Validate input
    if (!$registrationHandler->validateInput($formData)) {
        $_SESSION["reg_msg"] = implode("<br>", $registrationHandler->getErrors());
        redireccionar("register_form.php");
        exit;
    }

    $correo = SanitizarEntrada::limpiarEmail($formData['correo'] ?? '');

    // Check for duplicate email
    if ($registrationHandler->checkDuplicateEmail($correo)) {
        $_SESSION["reg_msg"] = implode("<br>", $registrationHandler->getErrors());
        redireccionar("register_form.php");
        exit;
    }

    // Hash the password
    $hashedPassword = $registrationHandler->hashPassword($formData['contrasena']);

    // Generate 2FA secret
    $secret_2fa = $registrationHandler->generate2faSecret();

    // Prepare user data for insertion
    $data = array(
        "nombre" => SanitizarEntrada::limpiarCadena($formData['nombre'] ?? ''),
        "apellido" => SanitizarEntrada::limpiarCadena($formData['apellido'] ?? ''),
        "correo" => $correo,
        "HashMagic" => $hashedPassword,
        "sexo" => SanitizarEntrada::limpiarCadena($formData['sexo'] ?? ''),
        "secret_2fa" => $secret_2fa
    );

    // Insert new user into the database
    if ($registrationHandler->registerUser($data)) {
        $_SESSION["reg_msg"] = "¡Registro exitoso! Por favor, configure su 2FA.";
        $_SESSION['2fa_secret'] = $secret_2fa;
        $_SESSION['2fa_email'] = $correo;
        redireccionar("setup_2fa.php"); // Redirect to 2FA setup page
    } else {
        $_SESSION["reg_msg"] = "Error al registrar el usuario. Inténtelo de nuevo.";
        redireccionar("register_form.php");
    }

} else {
    $_SESSION["reg_msg"] = "Acceso no autorizado.";
    redireccionar("register_form.php");
}
?>
