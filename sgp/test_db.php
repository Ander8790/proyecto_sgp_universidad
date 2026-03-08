<?php
$db = new PDO('mysql:host=localhost;dbname=proyecto_sgp;charset=utf8mb4', 'root', '');
$stmt = $db->query('SELECT * FROM departamentos');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
