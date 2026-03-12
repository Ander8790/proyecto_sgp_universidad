<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=proyecto_sgp', 'root', '');
    $stmt = $pdo->query("SELECT u.id, u.correo, u.estado as u_estado, u.rol_id, dp.nombres, dp.apellidos, dpa.estado_pasantia, dpa.fecha_fin_estimada, u.created_at FROM usuarios u LEFT JOIN datos_personales dp ON u.id = dp.usuario_id LEFT JOIN datos_pasante dpa ON u.id = dpa.usuario_id WHERE u.rol_id = 3 ORDER BY u.id DESC LIMIT 5");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    file_put_contents('test_db_output.json', json_encode($data, JSON_PRETTY_PRINT));
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
