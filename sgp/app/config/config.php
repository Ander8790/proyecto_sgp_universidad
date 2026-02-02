<?php
return [
    'db' => [
        // Configuración Híbrida: Railway (Nube) vs Localhost (Tu PC)
        'host' => $_ENV['MYSQLHOST'] ?? 'localhost',
        'name' => $_ENV['MYSQLDATABASE'] ?? 'proyecto_sgp',
        'user' => $_ENV['MYSQLUSER'] ?? 'root',
        'pass' => $_ENV['MYSQLPASSWORD'] ?? '',
        'port' => $_ENV['MYSQLPORT'] ?? '3306',
        'charset' => 'utf8mb4'
    ],
    'app' => [
        // Esto detecta automáticamente si es http o https y la ruta correcta
        'base_url' => ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])))
    ]
];