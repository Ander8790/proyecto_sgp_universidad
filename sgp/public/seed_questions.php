<?php
// Load config
$config = require_once '../app/config/config.php';
require_once '../app/core/Database.php';

echo "Conectando a la base de datos...\n";

try {
    $db = new Database($config['db']);
    
    // Check if empty
    $db->query("SELECT count(*) as count FROM security_questions");
    $row = $db->single();
    
    if ($row->count > 0) {
        echo "La tabla security_questions ya tiene datos ({$row->count} registros).\n";
    } else {
        echo "Tabla vacía. Insertando preguntas...\n";
        
        $questions = [
            '¿Cuál es el nombre de tu primera mascota?',
            '¿Cuál es el apellido de soltera de tu madre?',
            '¿Cuál es la ciudad donde naciste?',
            '¿Cuál es tu libro favorito?',
            '¿Cuál fue tu primer vehículo?'
        ];
        
        foreach ($questions as $q) {
            $db->query("INSERT INTO security_questions (question) VALUES (:q)");
            $db->bind(':q', $q);
            $db->execute();
            echo " - Insertada: $q\n";
        }
        echo "¡Sembrado completado exitosamente!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
