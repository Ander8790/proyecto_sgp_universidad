<?php
/**
 * Database Backup Script
 * Genera un respaldo completo de la base de datos proyecto_sgp
 */

$backupDir = 'c:\\xampp\\htdocs\\proyecto_sgp_backup_2026-01-23_184746';
$backupFile = $backupDir . '\\database_backup.sql';

// Configuración de la base de datos
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'proyecto_sgp';

try {
    // Conectar a la base de datos
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Conexión exitosa a la base de datos\n";
    
    // Obtener todas las tablas
    $tables = [];
    $result = $pdo->query("SHOW TABLES");
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    echo "✓ Encontradas " . count($tables) . " tablas\n";
    
    // Abrir archivo de respaldo
    $handle = fopen($backupFile, 'w');
    
    // Escribir encabezado
    fwrite($handle, "-- =============================================\n");
    fwrite($handle, "-- Respaldo de Base de Datos: proyecto_sgp\n");
    fwrite($handle, "-- Fecha: " . date('Y-m-d H:i:s') . "\n");
    fwrite($handle, "-- Tablas: " . count($tables) . "\n");
    fwrite($handle, "-- =============================================\n\n");
    fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");
    
    // Respaldar cada tabla
    foreach ($tables as $table) {
        echo "  → Respaldando tabla: $table\n";
        
        // DROP TABLE
        fwrite($handle, "-- Tabla: $table\n");
        fwrite($handle, "DROP TABLE IF EXISTS `$table`;\n\n");
        
        // CREATE TABLE
        $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
        fwrite($handle, $createTable[1] . ";\n\n");
        
        // INSERT DATA
        $rows = $pdo->query("SELECT * FROM `$table`");
        $rowCount = 0;
        
        while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
            if ($rowCount == 0) {
                fwrite($handle, "INSERT INTO `$table` VALUES\n");
            }
            
            $values = array_map(function($value) use ($pdo) {
                return $value === null ? 'NULL' : $pdo->quote($value);
            }, array_values($row));
            
            fwrite($handle, "(" . implode(", ", $values) . ")");
            
            $rowCount++;
            fwrite($handle, ",\n");
        }
        
        if ($rowCount > 0) {
            // Remover última coma
            fseek($handle, -2, SEEK_CUR);
            fwrite($handle, ";\n\n");
        }
    }
    
    fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($handle);
    
    $fileSize = filesize($backupFile);
    echo "\n✓ Respaldo completado exitosamente\n";
    echo "✓ Archivo: $backupFile\n";
    echo "✓ Tamaño: " . number_format($fileSize / 1024, 2) . " KB\n";
    
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
