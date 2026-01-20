<?php
$mysqli = new mysqli('localhost', 'root', '', 'sgp_v1');

if ($mysqli->connect_error) {
    die("Error: " . $mysqli->connect_error);
}

echo "==============================================\n";
echo "USUARIOS EN LA BASE DE DATOS\n";
echo "==============================================\n\n";

$result = $mysqli->query("SELECT id, correo, rol_id, estado FROM usuarios ORDER BY id");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . "\n";
        echo "Email: " . $row['correo'] . "\n";
        echo "Rol ID: " . $row['rol_id'] . "\n";
        echo "Estado: " . $row['estado'] . "\n";
        echo "---\n";
    }
} else {
    echo "No se encontraron usuarios.\n";
}

$mysqli->close();
