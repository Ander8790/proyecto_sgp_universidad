<?php
// Conectar a la base de datos
$mysqli = new mysqli('localhost', 'root', '', 'sgp_v1');

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

// Contraseña que cumple requisitos
$password = 'Admin123!';
$hash = password_hash($password, PASSWORD_BCRYPT);

// Actualizar el usuario admin
$stmt = $mysqli->prepare("UPDATE usuarios SET password = ? WHERE correo = 'admin@sgp.local'");
$stmt->bind_param("s", $hash);

if ($stmt->execute()) {
    echo "✅ Contraseña actualizada exitosamente\n\n";
    echo "==============================================\n";
    echo "CREDENCIALES ACTUALIZADAS:\n";
    echo "==============================================\n";
    echo "Email: admin@sgp.local\n";
    echo "Contraseña: Admin123!\n";
    echo "\n";
    echo "Requisitos cumplidos:\n";
    echo "✓ Mayúscula (A)\n";
    echo "✓ Minúscula (dmin)\n";
    echo "✓ Número (123)\n";
    echo "✓ Carácter especial (!)\n";
    echo "✓ Mínimo 8 caracteres\n";
    echo "==============================================\n";
} else {
    echo "❌ Error al actualizar: " . $stmt->error . "\n";
}

$stmt->close();
$mysqli->close();
