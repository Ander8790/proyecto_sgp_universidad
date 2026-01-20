<?php
// Script to create role-specific tables
$mysqli = new mysqli('localhost', 'root', '', 'sgp_v1');

if ($mysqli->connect_error) {
    die("Error de conexión: " . $mysqli->connect_error);
}

echo "==============================================\n";
echo "CREANDO TABLAS DE PERFIL POR ROL\n";
echo "==============================================\n\n";

// Check if tables exist
$result = $mysqli->query("SHOW TABLES LIKE 'datos_%'");
if ($result->num_rows > 0) {
    echo "⚠️  Tablas existentes encontradas. Eliminando...\n";
    $mysqli->query("DROP TABLE IF EXISTS datos_pasante");
    $mysqli->query("DROP TABLE IF EXISTS datos_tutor");
    echo "✅ Tablas antiguas eliminadas\n\n";
}

// Create datos_pasante table
echo "Creando tabla: datos_pasante\n";
$sql_pasante = "
CREATE TABLE datos_pasante (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NOT NULL UNIQUE,
    
    institucion_procedencia VARCHAR(150) NOT NULL COMMENT 'Nombre del Liceo',
    grado_anio VARCHAR(20) NOT NULL COMMENT 'Ej: 6to Año',
    mencion VARCHAR(50) DEFAULT 'Informática',
    periodo_pasantias VARCHAR(50),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($mysqli->query($sql_pasante)) {
    echo "✅ Tabla datos_pasante creada\n\n";
} else {
    echo "❌ Error: " . $mysqli->error . "\n\n";
}

// Create datos_tutor table
echo "Creando tabla: datos_tutor\n";
$sql_tutor = "
CREATE TABLE datos_tutor (
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

if ($mysqli->query($sql_tutor)) {
    echo "✅ Tabla datos_tutor creada\n\n";
} else {
    echo "❌ Error: " . $mysqli->error . "\n\n";
}

// Verify tables
echo "==============================================\n";
echo "VERIFICACIÓN\n";
echo "==============================================\n\n";

$result = $mysqli->query("SHOW TABLES LIKE 'datos_%'");
echo "Tablas creadas:\n";
while ($row = $result->fetch_array()) {
    echo "  - " . $row[0] . "\n";
}

echo "\n✅ Migración completada exitosamente\n";

$mysqli->close();
