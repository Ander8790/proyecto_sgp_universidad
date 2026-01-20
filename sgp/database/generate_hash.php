<?php
/**
 * Generador de Hash para Contraseña Válida
 * Genera hash para: Admin123!
 * Cumple requisitos: Mayúscula, minúscula, número, carácter especial
 */

$password = 'Admin123!';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "==============================================\n";
echo "NUEVA CONTRASEÑA DE ADMINISTRADOR\n";
echo "==============================================\n";
echo "Contraseña: " . $password . "\n";
echo "Hash: " . $hash . "\n";
echo "\n";
echo "Requisitos cumplidos:\n";
echo "✓ Mayúscula: A\n";
echo "✓ Minúscula: dmin\n";
echo "✓ Número: 123\n";
echo "✓ Carácter especial: !\n";
echo "✓ Mínimo 8 caracteres\n";
echo "\n";
echo "SQL para actualizar:\n";
echo "UPDATE usuarios SET password = '$hash' WHERE correo = 'admin@sgp.local';\n";
echo "==============================================\n";
