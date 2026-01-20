<?php
// Verificar usuario admin en la base de datos
$mysqli = new mysqli('localhost', 'root', '', 'sgp_v1');

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Buscar usuario admin
$result = $mysqli->query("SELECT id, correo, nombre, rol_id, estado FROM usuarios WHERE correo = 'admin@sgp.local'");

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "✅ Usuario encontrado:\n\n";
    echo "==============================================\n";
    echo "ID: " . $user['id'] . "\n";
    echo "Email: " . $user['correo'] . "\n";
    echo "Nombre: " . $user['nombre'] . "\n";
    echo "Rol ID: " . $user['rol_id'] . "\n";
    echo "Estado: " . $user['estado'] . "\n";
    echo "==============================================\n\n";
    
    // Verificar contraseña
    $password = 'Admin123!';
    $stmt = $mysqli->prepare("SELECT password FROM usuarios WHERE correo = 'admin@sgp.local'");
    $stmt->execute();
    $stmt->bind_result($hash);
    $stmt->fetch();
    
    if (password_verify($password, $hash)) {
        echo "✅ Contraseña verificada correctamente\n";
        echo "\nCredenciales válidas:\n";
        echo "📧 Email: admin@sgp.local\n";
        echo "🔒 Contraseña: Admin123!\n";
    } else {
        echo "❌ La contraseña no coincide\n";
    }
} else {
    echo "❌ Usuario admin no encontrado en la base de datos\n";
}

$mysqli->close();
