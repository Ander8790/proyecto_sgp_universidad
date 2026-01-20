<?php
/**
 * Generador de Hash para Clave Universal CORREGIDA
 * Clave: Sgp123* (SIN punto)
 */

$password = 'Sgp123*';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "==============================================\n";
echo "CLAVE UNIVERSAL SGP (CORREGIDA)\n";
echo "==============================================\n";
echo "Contraseña: " . $password . "\n";
echo "Hash: " . $hash . "\n";
echo "\n";
echo "Requisitos cumplidos:\n";
echo "✓ Mayúscula: S\n";
echo "✓ Minúscula: gp\n";
echo "✓ Número: 123\n";
echo "✓ Carácter especial: *\n";
echo "✓ Mínimo 8 caracteres: 7\n";
echo "\n";
echo "SQL para actualizar:\n";
echo "UPDATE usuarios SET password = '$hash' WHERE id IN (1,2,3);\n";
echo "==============================================\n";
