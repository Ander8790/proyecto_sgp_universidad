<?php
/**
 * DEBUG: Diagnóstico del bug "Cédula no encontrada" en el Kiosco
 * Acceder como: http://localhost/proyecto_sgp/sgp/public/debug_kiosco.php?cedula=TU_CEDULA
 * BORRAR este archivo después de depurar.
 */

// Bootstrapping igual al index.php del proyecto
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
define('APP_PATH', ROOT . DS . 'app' . DS);

require_once ROOT . '/app/config/config.php';
require_once ROOT . '/app/core/Database.php';

$config = require ROOT . '/app/config/config.php';
$db = new Database($config['db']);

$cedula = trim($_GET['cedula'] ?? '');

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><style>body{font-family:monospace;padding:20px;background:#111;color:#0f0;} table{border-collapse:collapse;width:100%;margin-bottom:20px;} th,td{border:1px solid #333;padding:8px;text-align:left;font-size:13px;} th{background:#222;color:#fff;} .err{color:#f55;background:#300;} .ok{color:#5f5;} .warn{color:#fa0;} h2{color:#fff;} h3{color:#aaf;} h4{color:#8cf;} pre{background:#1a1a1a;padding:10px;border:1px solid #333;overflow:auto;}</style></head><body>";
echo "<h2>🔎 Debug Kiosco — Diagnóstico de Cédula</h2>";

if (!$cedula) {
    echo "<p class='warn'>⚠️ Tip: Pasa la cédula por URL para test específico: <code>?cedula=12345678</code></p>";
    
    echo "<h3>📋 Todos los pasantes (rol_id=3) en la BD — Formato EXACTO de cédula:</h3>";
    $db->query("
        SELECT u.id, u.cedula, u.estado, u.rol_id,
               IFNULL(dp.nombres, '⚠️ SIN DATOS') AS nombres,
               IFNULL(dp.apellidos, '⚠️ SIN DATOS') AS apellidos,
               COALESCE(dpa.estado_pasantia, '🔴 NULL') AS estado_pasantia,
               COALESCE(dpa.tipo_pasantia, 'NULL') AS tipo_pasantia,
               u.pin_asistencia IS NOT NULL AS tiene_pin
        FROM usuarios u
        LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
        LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
        WHERE u.rol_id = 3
        ORDER BY u.id DESC
        LIMIT 50
    ");
    $todos = $db->resultSet();
    
    if (!$todos) {
        echo "<p class='err'>❌ No hay pasantes (rol_id=3) en la BD</p>";
    } else {
        echo "<table>
            <tr>
                <th>ID</th>
                <th>⚠️ Cédula EXACTA en BD</th>
                <th>Estado Usuario</th>
                <th>Nombres</th>
                <th>Apellidos</th>
                <th>estado_pasantia</th>
                <th>Tipo</th>
                <th>Tiene PIN</th>
                <th>Link Debug</th>
            </tr>";
        foreach ($todos as $p) {
            $rowClass = ($p->estado !== 'activo' || $p->estado_pasantia !== 'Activo') ? 'class="err"' : '';
            $numericOnly = preg_replace('/[^0-9]/', '', $p->cedula);
            $cedulaDisplay = ($p->cedula !== $numericOnly)
                ? "<b style='color:#fa0'>{$p->cedula}</b> (numérico: {$numericOnly})"
                : "<b style='color:#5f5'>{$p->cedula}</b>";
            echo "<tr {$rowClass}>
                <td>{$p->id}</td>
                <td>{$cedulaDisplay}</td>
                <td>{$p->estado}</td>
                <td>" . htmlspecialchars($p->nombres) . "</td>
                <td>" . htmlspecialchars($p->apellidos) . "</td>
                <td>" . htmlspecialchars($p->estado_pasantia) . "</td>
                <td>" . htmlspecialchars($p->tipo_pasantia) . "</td>
                <td>" . ($p->tiene_pin ? '✅ Sí' : '❌ No') . "</td>
                <td><a href='?cedula={$numericOnly}' style='color:#5bf'>Test</a></td>
            </tr>";
        }
        echo "</table>";
    }

    // Verificar soporte de REGEXP_REPLACE
    echo "<h3>🔧 Verificar soporte REGEXP_REPLACE en MySQL:</h3>";
    try {
        $db->query("SELECT REGEXP_REPLACE('V-12345', '[^0-9]', '') AS test");
        $regexpTest = $db->single();
        if ($regexpTest && $regexpTest->test === '12345') {
            echo "<p class='ok'>✅ REGEXP_REPLACE disponible — MySQL 8.0+</p>";
        } else {
            echo "<p class='warn'>⚠️ REGEXP_REPLACE disponible pero resultado inesperado: " . htmlspecialchars($regexpTest->test ?? 'null') . "</p>";
        }
    } catch (Exception $e) {
        echo "<p class='err'>❌ REGEXP_REPLACE NO disponible — Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p class='warn'>Deberías usar una solución alternativa con REPLACE() múltiple.</p>";
    }

} else {
    // Test con cédula específica
    $cedulaNumeros = preg_replace('/[^0-9]/', '', $cedula);
    echo "<h3>Buscando: <b>" . htmlspecialchars($cedula) . "</b> → solo números: <b>{$cedulaNumeros}</b></h3>";
    echo "<p><a href='?' style='color:#5bf'>← Volver a lista completa</a></p>";

    // Test 1: Query EXACTO del Kiosco ANTERIOR (buggy)
    echo "<h4>Test 1 — Query ANTERIOR del Kiosco (exacto, potencialmente buggy):</h4>";
    $db->query("
        SELECT u.id, u.cedula, u.estado,
               COALESCE(dpa.estado_pasantia, 'Sin Asignar') AS estado_pasantia,
               dp.nombres, dp.apellidos
        FROM   usuarios u
        INNER JOIN datos_personales dp  ON dp.usuario_id = u.id
        LEFT  JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
        WHERE  u.cedula = :cedula AND u.rol_id = 3 AND u.estado = 'activo'
        LIMIT 1
    ");
    $db->bind(':cedula', $cedula);
    $r1 = $db->single();
    if ($r1) {
        echo "<p class='ok'>✅ La query ANTERIOR sí encuentra al pasante (la cédula ingresada coincide exactamente con BD)</p>";
    } else {
        echo "<p class='err'>❌ La query ANTERIOR NO encuentra nada → Este era el bug</p>";
    }

    // Test 2: Query NUEVO con REGEXP_REPLACE
    echo "<h4>Test 2 — Query NUEVO con REGEXP_REPLACE (el fix aplicado):</h4>";
    try {
        $db->query("
            SELECT u.id, u.cedula, u.estado,
                   COALESCE(dpa.estado_pasantia, 'Sin Asignar') AS estado_pasantia,
                   d.nombre AS departamento_nombre,
                   dp.nombres, dp.apellidos,
                   u.pin_asistencia IS NOT NULL AS tiene_pin
            FROM   usuarios u
            INNER JOIN datos_personales dp  ON dp.usuario_id  = u.id
            LEFT  JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT  JOIN departamentos    d   ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            WHERE  REGEXP_REPLACE(u.cedula, '[^0-9]', '') = :cedula_num
              AND  u.rol_id  = 3
              AND  LOWER(u.estado) = 'activo'
            LIMIT 1
        ");
        $db->bind(':cedula_num', $cedulaNumeros);
        $r2 = $db->single();
        if ($r2) {
            echo "<p class='ok'>✅ ENCONTRADO con el fix nuevo</p>";
            echo "<table><tr><th>Campo</th><th>Valor</th></tr>";
            echo "<tr><td>ID</td><td>{$r2->id}</td></tr>";
            echo "<tr><td>Cédula en BD</td><td><b>{$r2->cedula}</b></td></tr>";
            echo "<tr><td>Nombres</td><td>" . htmlspecialchars($r2->nombres) . "</td></tr>";
            echo "<tr><td>Apellidos</td><td>" . htmlspecialchars($r2->apellidos) . "</td></tr>";
            echo "<tr><td>Estado pasantía</td><td>" . htmlspecialchars($r2->estado_pasantia) . "</td></tr>";
            echo "<tr><td>Departamento</td><td>" . htmlspecialchars($r2->departamento_nombre ?? '❌ Sin asignar') . "</td></tr>";
            echo "<tr><td>Tiene PIN</td><td>" . ($r2->tiene_pin ? '✅ Sí' : '❌ No (debe asignar PIN)') . "</td></tr>";
            echo "</table>";

            // Verificar cada condición del Kiosco
            echo "<h4>Verificación de condiciones del Kiosco:</h4>";
            echo "<table><tr><th>Condición</th><th>Valor</th><th>Resultado</th></tr>";

            $eActivo = strtolower($r2->estado_pasantia) === 'activo';
            echo "<tr><td>estado_pasantia = 'Activo'</td><td>" . htmlspecialchars($r2->estado_pasantia) . "</td><td>" . ($eActivo ? '✅ OK' : '❌ BLOQUEADO') . "</td></tr>";

            $tieneDpto = !empty($r2->departamento_nombre);
            echo "<tr><td>Tiene departamento</td><td>" . htmlspecialchars($r2->departamento_nombre ?? 'NULL') . "</td><td>" . ($tieneDpto ? '✅ OK' : '❌ BLOQUEADO') . "</td></tr>";

            $tienePin = (bool)$r2->tiene_pin;
            echo "<tr><td>Tiene PIN configurado</td><td>-</td><td>" . ($tienePin ? '✅ OK' : '❌ BLOQUEADO — no tiene PIN') . "</td></tr>";

            echo "</table>";
        } else {
            echo "<p class='err'>❌ Tampoco el NUEVO query encuentra el pasante</p>";
            echo "<p class='warn'>Buscando sin filtros para ver si existe en BD...</p>";

            $db->query("SELECT u.id, u.cedula, u.estado, u.rol_id FROM usuarios WHERE cedula LIKE :like");
            $db->bind(':like', '%' . $cedulaNumeros . '%');
            $cualquier = $db->resultSet();
            if ($cualquier) {
                echo "<p class='warn'>⚠️ Se encontraron coincidencias parciales:</p>";
                echo "<table><tr><th>ID</th><th>Cédula BD</th><th>Estado</th><th>rol_id</th></tr>";
                foreach ($cualquier as $q) {
                    echo "<tr><td>{$q->id}</td><td><b>{$q->cedula}</b></td><td>{$q->estado}</td><td>{$q->rol_id}</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='err'>❌ La cédula '{$cedula}' no existe en absoluto en la tabla usuarios</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p class='err'>❌ Error REGEXP_REPLACE: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p>⚠️ Tu versión de MySQL no soporta REGEXP_REPLACE. Se necesita el fix alternativo.</p>";
    }
}

echo "</body></html>";
?>
