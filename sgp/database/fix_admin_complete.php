<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "\n";
echo "==============================================\n";
echo "DIAGNÓSTICO Y REPARACIÓN DE CREDENCIALES ADMIN\n";
echo "==============================================\n\n";

// Conectar a la base de datos
$mysqli = new mysqli('localhost', 'root', '', 'sgp_v1');

if ($mysqli->connect_error) {
    die("❌ Error de conexión: " . $mysqli->connect_error . "\n");
}

echo "✅ Conexión a base de datos exitosa\n\n";

// Paso 1: Listar todos los usuarios
echo "PASO 1: Usuarios en la base de datos\n";
echo "----------------------------------------------\n";
$result = $mysqli->query("SELECT id, correo, rol_id, estado FROM usuarios ORDER BY id");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo sprintf("ID: %-3d | Email: %-30s | Rol: %d | Estado: %s\n", 
            $row['id'], $row['correo'], $row['rol_id'], $row['estado']);
    }
    echo "\n";
} else {
    echo "❌ No se encontraron usuarios.\n\n";
}

// Paso 2: Buscar usuario admin
echo "PASO 2: Verificando usuario admin@sgp.local\n";
echo "----------------------------------------------\n";
$result = $mysqli->query("SELECT id, correo, rol_id, estado FROM usuarios WHERE correo = 'admin@sgp.local'");

if ($result && $result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "✅ Usuario admin encontrado:\n";
    echo "   ID: " . $admin['id'] . "\n";
    echo "   Email: " . $admin['correo'] . "\n";
    echo "   Rol ID: " . $admin['rol_id'] . "\n";
    echo "   Estado: " . $admin['estado'] . "\n\n";
    
    // Paso 3: Actualizar contraseña
    echo "PASO 3: Actualizando contraseña\n";
    echo "----------------------------------------------\n";
    $password = 'Admin123!';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $mysqli->prepare("UPDATE usuarios SET password = ? WHERE correo = 'admin@sgp.local'");
    $stmt->bind_param("s", $hash);
    
    if ($stmt->execute()) {
        echo "✅ Contraseña actualizada exitosamente\n\n";
        
        // Paso 4: Verificar contraseña
        echo "PASO 4: Verificando contraseña\n";
        echo "----------------------------------------------\n";
        $result = $mysqli->query("SELECT password FROM usuarios WHERE correo = 'admin@sgp.local'");
        $row = $result->fetch_assoc();
        
        if (password_verify($password, $row['password'])) {
            echo "✅ Verificación exitosa\n\n";
            echo "==============================================\n";
            echo "CREDENCIALES LISTAS PARA USAR:\n";
            echo "==============================================\n";
            echo "📧 Email: admin@sgp.local\n";
            echo "🔒 Contraseña: Admin123!\n";
            echo "==============================================\n\n";
            echo "✅ Puedes usar estas credenciales para iniciar sesión\n\n";
        } else {
            echo "❌ Error: La verificación de contraseña falló\n\n";
        }
    } else {
        echo "❌ Error al actualizar: " . $stmt->error . "\n\n";
    }
} else {
    echo "❌ Usuario admin@sgp.local NO encontrado\n";
    echo "   Creando usuario admin...\n\n";
    
    // Crear usuario admin
    $password = 'Admin123!';
    $hash = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $mysqli->prepare("INSERT INTO usuarios (correo, password, rol_id, estado, requiere_cambio_clave) VALUES ('admin@sgp.local', ?, 1, 'activo', 0)");
    $stmt->bind_param("s", $hash);
    
    if ($stmt->execute()) {
        echo "✅ Usuario admin creado exitosamente\n\n";
        echo "==============================================\n";
        echo "CREDENCIALES CREADAS:\n";
        echo "==============================================\n";
        echo "📧 Email: admin@sgp.local\n";
        echo "🔒 Contraseña: Admin123!\n";
        echo "==============================================\n\n";
    } else {
        echo "❌ Error al crear usuario: " . $stmt->error . "\n\n";
    }
}

$mysqli->close();
