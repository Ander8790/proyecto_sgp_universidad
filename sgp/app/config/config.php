<?php
return [
    'db' => [
        // conexion a la base de datos 
        'host' => $_ENV['MYSQLHOST'] ?? 'localhost',
        'name' => $_ENV['MYSQLDATABASE'] ?? 'proyecto_sgp', // Tu base de datos local
        'user' => $_ENV['MYSQLUSER'] ?? 'root',            // Tu usuario local
        'pass' => $_ENV['MYSQLPASSWORD'] ?? '',             // Tu contraseña local (vacía)
        'port' => $_ENV['MYSQLPORT'] ?? '3306',
        'charset' => 'utf8mb4'
    ],
    ];