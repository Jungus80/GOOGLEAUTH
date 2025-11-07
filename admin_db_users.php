<?php
require_once 'clases/mysql.inc.php';

$db = new mod_db();
$conexion = $db->getConexion();

echo "<h2>Gestión de Usuarios de Base de Datos</h2>";

// --- 1. Crear Usuario con Privilegios Mínimos ---
echo "<h3>1. Crear Usuario 'app_user' con Privilegios Mínimos</h3>";
$createUserSQL = "CREATE USER 'app_user'@'localhost' IDENTIFIED BY 'secure_password';";
$grantPrivilegesSQL = "GRANT SELECT, INSERT, UPDATE, DELETE ON `prueba69`.* TO 'app_user'@'localhost';";
$flushPrivilegesSQL = "FLUSH PRIVILEGES;";

echo "<p><strong>Comando para crear el usuario:</strong><br><code>" . htmlspecialchars($createUserSQL) . "</code></p>";
echo "<p><strong>Comando para otorgar privilegios:</strong><br><code>" . htmlspecialchars($grantPrivilegesSQL) . "</code></p>";
echo "<p><strong>Comando para recargar privilegios:</strong><br><code>" . htmlspecialchars($flushPrivilegesSQL) . "</code></p>";

try {
    // It's generally not recommended to execute CREATE USER and GRANT statements directly from a web application
    // for security reasons. These operations are typically performed by a database administrator.
    // For demonstration purposes, we'll show the commands.
    // If you uncomment the lines below, ensure you have appropriate permissions and understand the security implications.

    // $conexion->exec($createUserSQL);
    // $conexion->exec($grantPrivilegesSQL);
    // $conexion->exec($flushPrivilegesSQL);
    echo "<p style='color: green;'>Los comandos para crear el usuario y otorgar privilegios han sido mostrados. Para ejecutarlos, descomente las líneas correspondientes en este archivo y asegúrese de tener los permisos adecuados.</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error al intentar ejecutar comandos de creación/otorgamiento de privilegios: " . $e->getMessage() . "</p>";
}

// --- 2. Mostrar Privilegios del Usuario Creado ---
echo "<h3>2. Mostrar Privilegios del Usuario 'app_user'</h3>";
$showGrantsSQL = "SHOW GRANTS FOR 'app_user'@'localhost';";
echo "<p><strong>Comando para ver los privilegios concedidos:</strong><br><code>" . htmlspecialchars($showGrantsSQL) . "</code></p>";

try {
    $stmt = $conexion->query($showGrantsSQL);
    $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($grants)) {
        echo "<h4>Privilegios de 'app_user'@'localhost':</h4>";
        echo "<ul>";
        foreach ($grants as $grant) {
            echo "<li><code>" . htmlspecialchars($grant) . "</code></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No se encontraron privilegios para 'app_user'@'localhost'. Asegúrese de que el usuario fue creado y los privilegios otorgados.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Error al mostrar privilegios: " . $e->getMessage() . "</p>";
}

$db->disconnect();
?>
