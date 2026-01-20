<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mysqli = new mysqli('localhost', 'root', '', 'sgp_v1');

if ($mysqli->connect_error) {
    die("❌ Error de conexión: " . $mysqli->connect_error . "\n");
}

echo "\n==============================================\n";
echo "VERIFICANDO ESTRUCTURA ACTUAL\n";
echo "==============================================\n\n";

// Check all tables
$result = $mysqli->query("SHOW TABLES");
echo "Tablas existentes en sgp_v1:\n";
while ($row = $result->fetch_array()) {
    echo "  - " . $row[0] . "\n";
}

echo "\n";

// Check usuarios table structure
echo "Estructura de la tabla 'usuarios':\n";
$result = $mysqli->query("DESCRIBE usuarios");
while ($row = $result->fetch_assoc()) {
    echo sprintf("  %-25s %-20s %s\n", $row['Field'], $row['Type'], $row['Key']);
}

$mysqli->close();
