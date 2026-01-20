<?php
$mysqli = new mysqli('localhost', 'root', '', 'sgp_v1');

if ($mysqli->connect_error) {
    die("❌ Error: " . $mysqli->connect_error . "\n");
}

echo "\n✅ Conectado a sgp_v1\n\n";

// Drop existing tables if they exist
echo "Eliminando tablas antiguas (si existen)...\n";
$mysqli->query("DROP TABLE IF EXISTS datos_pasante");
$mysqli->query("DROP TABLE IF EXISTS datos_tutor");
echo "✅ Listo\n\n";

// Create datos_pasante
echo "Creando tabla: datos_pasante\n";
$sql1 = "CREATE TABLE datos_pasante (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL UNIQUE,
    institucion_procedencia VARCHAR(150) NOT NULL,
    grado_anio VARCHAR(20) NOT NULL,
    mencion VARCHAR(50) DEFAULT 'Informática',
    periodo_pasantias VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($mysqli->query($sql1)) {
    echo "✅ datos_pasante creada\n\n";
} else {
    echo "❌ Error: " . $mysqli->error . "\n\n";
    exit(1);
}

// Create datos_tutor
echo "Creando tabla: datos_tutor\n";
$sql2 = "CREATE TABLE datos_tutor (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL UNIQUE,
    departamento_id INT UNSIGNED NULL,
    cargo VARCHAR(100) NOT NULL,
    extension_telefonica VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($mysqli->query($sql2)) {
    echo "✅ datos_tutor creada\n\n";
} else {
    echo "❌ Error: " . $mysqli->error . "\n\n";
    exit(1);
}

// Verify
echo "==============================================\n";
echo "VERIFICACIÓN FINAL\n";
echo "==============================================\n\n";

$result = $mysqli->query("SHOW TABLES LIKE 'datos_%'");
while ($row = $result->fetch_array()) {
    echo "✅ " . $row[0] . "\n";
}

echo "\n🎉 Migración completada exitosamente!\n\n";

$mysqli->close();
