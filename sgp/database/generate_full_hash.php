<?php
$password = 'Sgp123*';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Hash completo: " . $hash . "\n";
echo "Longitud: " . strlen($hash) . "\n";
echo "\nSQL:\n";
echo "UPDATE usuarios SET password = '$hash' WHERE correo IN ('admin@sgp.local', 'tutor@sgp.local', 'pasante@sgp.local');\n";
