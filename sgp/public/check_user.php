<?php
define('APPROOT', dirname(dirname(__FILE__)) . '/app');
require_once '../app/config/config.php';
require_once '../app/core/Database.php';

$config = require '../app/config/config.php';
$db = new Database($config['db']);

$stmt = $db->query("SELECT * FROM users WHERE email = 'admin@sgp.com'");
$user = $db->single();

if ($user) {
    echo "User found: " . $user->name . " | Role ID: " . $user->role_id . "\n";
} else {
    echo "User NOT found. Attempting to seed...\n";
    // Seed admin
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $db->query("INSERT INTO users (role_id, email, password, name) VALUES (1, 'admin@sgp.com', '$pass', 'Administrador Principal')");
    $db->execute();
    echo "Admin seeded.\n";
}
