<?php
/**
 * Script de Diagnóstico - Actualizar Perfil
 * 
 * PROPÓSITO: Probar la actualización de perfil directamente
 * para ver el error exacto sin interferencias del frontend.
 */

// Iniciar sesión de depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Diagnóstico de Actualización de Perfil</h1>";
echo "<pre>";

// Cargar bootstrap (esto carga todo lo necesario)
require_once dirname(__DIR__) . '/app/bootstrap.php';

Session::start();

// Simular datos de sesión (CAMBIAR POR TU USER_ID REAL)
$userId = Session::get('user_id') ?? 1; // Cambiar 1 por tu ID de usuario

echo "=== DIAGNÓSTICO INICIADO ===\n";
echo "User ID: $userId\n\n";

try {
    $db = new Database();
    
    // Datos de prueba
    $data = [
        'nombres' => 'Test',
        'apellidos' => 'Usuario',
        'telefono' => '0414-1234567',
        'direccion' => 'Dirección de prueba',
        'genero' => 'M',
        'fecha_nacimiento' => '1990-01-01',
        'cargo' => 'Cargo de prueba'
    ];
    
    echo "Datos a actualizar:\n";
    print_r($data);
    echo "\n";
    
    // Preparar query
    $query = "
        UPDATE datos_personales
        SET 
            nombres = :nombres,
            apellidos = :apellidos,
            telefono = :telefono,
            direccion = :direccion,
            genero = :genero,
            fecha_nacimiento = :fecha_nacimiento,
            cargo = :cargo
        WHERE usuario_id = :usuario_id
    ";
    
    echo "Query SQL:\n$query\n\n";
    
    $db->query($query);
    
    // Bind de parámetros
    $db->bind(':nombres', $data['nombres']);
    $db->bind(':apellidos', $data['apellidos']);
    $db->bind(':telefono', $data['telefono']);
    $db->bind(':direccion', $data['direccion'] ?: null);
    $db->bind(':genero', $data['genero'] ?: null);
    $db->bind(':fecha_nacimiento', $data['fecha_nacimiento'] ?: null);
    $db->bind(':cargo', $data['cargo'] ?: null);
    $db->bind(':usuario_id', $userId);
    
    echo "Parámetros vinculados correctamente\n\n";
    
    // Ejecutar
    echo "Ejecutando query...\n";
    $resultado = $db->execute();
    
    if ($resultado) {
        echo "✅ ÉXITO: Perfil actualizado correctamente\n";
        echo "Filas afectadas: " . $db->rowCount() . "\n";
    } else {
        echo "❌ ERROR: No se pudo actualizar\n";
        print_r($db->errorInfo ?? 'No error info disponible');
    }
    
} catch (PDOException $e) {
    echo "❌ ERROR PDO:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Código: " . $e->getCode() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack Trace:\n" . $e->getTraceAsString() . "\n";
} catch (Exception $e) {
    echo "❌ ERROR GENERAL:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== DIAGNÓSTICO FINALIZADO ===";
echo "</pre>";
?>
