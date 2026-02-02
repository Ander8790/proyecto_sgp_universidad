<?php
/**
 * Script para insertar preguntas de seguridad en la base de datos
 * Ejecutar una sola vez: http://localhost/proyecto_sgp/sgp/public/seed_questions.php
 */

require_once '../app/config/config.php';
require_once '../app/core/Database.php';

try {
    $db = new Database();
    
    // Verificar si ya existen preguntas
    $db->query("SELECT COUNT(*) as total FROM preguntas_seguridad");
    $result = $db->single();
    
    if ($result['total'] > 0) {
        echo "✅ Ya existen {$result['total']} preguntas en la base de datos.<br>";
        echo "<br><strong>Preguntas actuales:</strong><br>";
        
        $db->query("SELECT * FROM preguntas_seguridad");
        $preguntas = $db->resultSet();
        
        foreach ($preguntas as $p) {
            echo "ID {$p['id']}: {$p['pregunta']}<br>";
        }
        
        exit;
    }
    
    // Insertar preguntas de seguridad
    $preguntas = [
        '¿Cuál es el nombre de tu primera mascota?',
        '¿En qué ciudad naciste?',
        '¿Cuál es tu comida favorita?',
        '¿Cómo se llama tu mejor amigo de la infancia?',
        '¿Cuál fue la marca de tu primer carro?',
        '¿Cuál es el segundo nombre de tu madre?',
        '¿En qué escuela estudiaste la primaria?',
        '¿Cuál es tu película favorita?',
        '¿Cuál es tu color favorito?',
        '¿Cuál es el nombre de tu abuela materna?'
    ];
    
    $db->beginTransaction();
    
    foreach ($preguntas as $pregunta) {
        $db->query("INSERT INTO preguntas_seguridad (pregunta) VALUES (:pregunta)");
        $db->bind(':pregunta', $pregunta);
        $db->execute();
    }
    
    $db->commit();
    
    echo "✅ Se insertaron " . count($preguntas) . " preguntas de seguridad correctamente.<br><br>";
    echo "<strong>Preguntas insertadas:</strong><br>";
    
    $db->query("SELECT * FROM preguntas_seguridad");
    $resultado = $db->resultSet();
    
    foreach ($resultado as $p) {
        echo "ID {$p['id']}: {$p['pregunta']}<br>";
    }
    
    echo "<br><br>";
    echo "🎉 <strong>¡Listo!</strong> Ahora puedes probar el registro en: <a href='/proyecto_sgp/sgp/public/auth/register'>Ir al Registro</a>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
