<?php
$mysqli = new mysqli('localhost', 'root', '', 'sgp_v1');

if ($mysqli->connect_error) {
    die("Error: " . $mysqli->connect_error);
}

echo "Conectado a sgp_v1\n\n";

// Drop tables
$mysqli->query("DROP TABLE IF EXISTS datos_pasante");
$mysqli->query("DROP TABLE IF EXISTS datos_tutor");

// Create datos_pasante
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
    echo "✅ datos_pasante creada\n";
} else {
    echo "❌ Error datos_pasante: " . $mysqli->error . "\n";
}

// Create datos_tutor
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
    echo "✅ datos_tutor creada\n";
} else {
    echo "❌ Error datos_tutor: " . $mysqli->error . "\n";
}

echo "\n✅ Migración completada\n";
$mysqli->close();
