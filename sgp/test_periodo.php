<?php $db = new PDO('mysql:host=localhost;dbname=proyecto_sgp', 'root', ''); $stmt = $db->query('SELECT * FROM periodos_academicos'); print_r($stmt->fetchAll(PDO::FETCH_ASSOC)); ?>
