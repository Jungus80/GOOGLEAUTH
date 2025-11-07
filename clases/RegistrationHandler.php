<?php
require_once 'mysql.inc.php';
require_once 'SanitizarEntrada.php';
require_once 'SonataGoogleAuthenticator/GoogleAuthenticator.php';

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

class RegistrationHandler
{
    private $db;
    private $g;
    private $errors = [];

    public function __construct(mod_db $db)
    {
        $this->db = $db;
        $this->g = new GoogleAuthenticator();
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function addError($message)
    {
        $this->errors[] = $message;
    }

    public function validateInput(array $formData)
    {
        $nombre = SanitizarEntrada::limpiarCadena($formData['nombre'] ?? '');
        $apellido = SanitizarEntrada::limpiarCadena($formData['apellido'] ?? '');
        $correo = SanitizarEntrada::limpiarEmail($formData['correo'] ?? '');
        $contrasena = $formData['contrasena'] ?? '';
        $confirm_contrasena = $formData['confirm_contrasena'] ?? '';
        $sexo = SanitizarEntrada::limpiarCadena($formData['sexo'] ?? '');

        if (empty($nombre) || empty($apellido) || empty($correo) || empty($contrasena) || empty($confirm_contrasena) || empty($sexo)) {
            $this->addError("Todos los campos son obligatorios.");
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->addError("El formato del correo electrónico no es válido.");
        }

        if ($contrasena !== $confirm_contrasena) {
            $this->addError("Las contraseñas no coinciden.");
        }

        if (strlen($contrasena) < 6) {
            $this->addError("La contraseña debe tener al menos 6 caracteres.");
        }

        return empty($this->errors);
    }

    public function checkDuplicateEmail($correo)
    {
        $stmt = $this->db->getConexion()->prepare("SELECT id FROM usuarios WHERE correo = :correo");
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $this->addError("El correo electrónico ya está registrado.");
            return true;
        }
        return false;
    }

    public function hashPassword($contrasena)
    {
        $options = ['cost' => 13];
        return password_hash($contrasena, PASSWORD_BCRYPT, $options);
    }

    public function generate2faSecret()
    {
        return $this->g->generateSecret();
    }

    public function registerUser(array $userData)
    {
        return $this->db->insertSeguro("usuarios", $userData);
    }
}
