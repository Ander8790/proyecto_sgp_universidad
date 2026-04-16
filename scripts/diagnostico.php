<?php
/**
 * diagnostico.php
 * Script de diagnóstico del estado de la tabla asistencias.
 * Ejecutar desde CLI: php scripts/diagnostico.php
 * O desde el navegador apuntando directamente al archivo (solo entorno local).
 */

declare(strict_types=1);

// ── Conexión directa (sin bootstrapping del framework) ──────────────────────

$config = [
    'host'    => $_ENV['MYSQLHOST']     ?? 'localhost',
    'name'    => $_ENV['MYSQLDATABASE'] ?? 'proyecto_sgp',
    'user'    => $_ENV['MYSQLUSER']     ?? 'root',
    'pass'    => $_ENV['MYSQLPASSWORD'] ?? '',
    'port'    => $_ENV['MYSQLPORT']     ?? '3306',
    'charset' => 'utf8mb4',
];

try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['host'],
        $config['port'],
        $config['name'],
        $config['charset']
    );
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci",
    ]);
    $pdo->exec("SET time_zone = '-04:00'");
} catch (PDOException $e) {
    die("ERROR DE CONEXION: " . $e->getMessage() . PHP_EOL);
}

// ── Helpers ──────────────────────────────────────────────────────────────────

$isCli = PHP_SAPI === 'cli';

function titulo(string $texto): void
{
    global $isCli;
    $linea = str_repeat('=', 70);
    if ($isCli) {
        echo PHP_EOL . $linea . PHP_EOL . "  {$texto}" . PHP_EOL . $linea . PHP_EOL;
    } else {
        echo "<h2 style='font-family:monospace;border-bottom:2px solid #333'>{$texto}</h2>";
    }
}

function fila(string $etiqueta, $valor): void
{
    global $isCli;
    if ($isCli) {
        printf("  %-45s %s%s", $etiqueta, $valor, PHP_EOL);
    } else {
        echo "<p style='font-family:monospace'><b>{$etiqueta}</b> {$valor}</p>";
    }
}

function tabla_cli(array $filas, array $columnas): void
{
    global $isCli;
    if (empty($filas)) {
        echo "  (sin resultados)" . PHP_EOL;
        return;
    }

    if ($isCli) {
        // Calcular anchos
        $anchos = [];
        foreach ($columnas as $col) {
            $anchos[$col] = strlen($col);
        }
        foreach ($filas as $f) {
            foreach ($columnas as $col) {
                $v = (string)($f[$col] ?? '');
                if (strlen($v) > $anchos[$col]) {
                    $anchos[$col] = strlen($v);
                }
            }
        }
        // Cabecera
        $sep = '  +';
        foreach ($columnas as $col) {
            $sep .= str_repeat('-', $anchos[$col] + 2) . '+';
        }
        echo $sep . PHP_EOL;
        $cabecera = '  |';
        foreach ($columnas as $col) {
            $cabecera .= ' ' . str_pad($col, $anchos[$col]) . ' |';
        }
        echo $cabecera . PHP_EOL . $sep . PHP_EOL;
        // Filas
        foreach ($filas as $f) {
            $linea = '  |';
            foreach ($columnas as $col) {
                $linea .= ' ' . str_pad((string)($f[$col] ?? ''), $anchos[$col]) . ' |';
            }
            echo $linea . PHP_EOL;
        }
        echo $sep . PHP_EOL;
    } else {
        echo "<table border='1' cellpadding='4' style='font-family:monospace;border-collapse:collapse'>";
        echo "<tr style='background:#ddd'>";
        foreach ($columnas as $col) {
            echo "<th>{$col}</th>";
        }
        echo "</tr>";
        foreach ($filas as $f) {
            echo "<tr>";
            foreach ($columnas as $col) {
                echo "<td>" . htmlspecialchars((string)($f[$col] ?? '')) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    }
}

// ── A) Estados legacy ────────────────────────────────────────────────────────

titulo("A) ESTADOS LEGACY EN LA TABLA asistencias");

$stmt = $pdo->query(
    "SELECT estado, COUNT(*) AS total
     FROM asistencias
     GROUP BY estado
     ORDER BY total DESC"
);
$estados = $stmt->fetchAll();

if (empty($estados)) {
    fila("Resultado:", "La tabla asistencias está vacía o no existe.");
} else {
    $legacyStates = ['abierto', 'cerrado'];
    $hayLegacy    = false;
    fila("Distribución actual de estados:", "");
    tabla_cli($estados, ['estado', 'total']);

    foreach ($estados as $row) {
        if (in_array(strtolower($row['estado']), $legacyStates, true)) {
            $hayLegacy = true;
            break;
        }
    }
    fila("¿Contiene estados legacy (abierto/cerrado)?", $hayLegacy ? "SI - requiere migración" : "No");
}

// ── B) Top 10 pasantes con más días vacíos ───────────────────────────────────

titulo("B) TOP 10 PASANTES CON MÁS DÍAS SIN REGISTRO (entre fecha_inicio y ayer)");

// Obtenemos pasantes activos con su fecha_inicio
$stmtPasantes = $pdo->query(
    "SELECT p.id, p.nombre, p.apellido, p.fecha_inicio
     FROM pasantes p
     WHERE p.estatus = 'activo'
       AND p.fecha_inicio IS NOT NULL
     ORDER BY p.id"
);
$pasantes = $stmtPasantes->fetchAll();

$hoy  = new DateTimeImmutable('today');
$ayer = $hoy->modify('-1 day');

$diasVaciosPorPasante = [];

foreach ($pasantes as $p) {
    $inicio = new DateTimeImmutable($p['fecha_inicio']);
    if ($inicio > $ayer) {
        // El pasante comenzó hoy o en el futuro, no hay días que evaluar
        continue;
    }
    $inicio = max($inicio, new DateTimeImmutable('2000-01-01')); // sanidad

    // Días hábiles (lunes-viernes) entre fecha_inicio y ayer
    $periodo   = new DatePeriod($inicio, new DateInterval('P1D'), $ayer->modify('+1 day'));
    $diasHabiles = 0;
    foreach ($periodo as $dia) {
        $dow = (int)$dia->format('N');
        if ($dow >= 1 && $dow <= 5) {
            $diasHabiles++;
        }
    }

    // Registros existentes en ese rango
    $stmtCount = $pdo->prepare(
        "SELECT COUNT(DISTINCT DATE(fecha_registro)) AS dias_con_registro
         FROM asistencias
         WHERE pasante_id = :pid
           AND DATE(fecha_registro) >= :inicio
           AND DATE(fecha_registro) <= :fin"
    );
    $stmtCount->execute([
        ':pid'   => $p['id'],
        ':inicio' => $inicio->format('Y-m-d'),
        ':fin'    => $ayer->format('Y-m-d'),
    ]);
    $conRegistro = (int)($stmtCount->fetch()['dias_con_registro'] ?? 0);

    $diasVaciosPorPasante[] = [
        'id'            => $p['id'],
        'nombre'        => $p['nombre'] . ' ' . $p['apellido'],
        'fecha_inicio'  => $p['fecha_inicio'],
        'dias_habiles'  => $diasHabiles,
        'dias_con_reg'  => $conRegistro,
        'dias_vacios'   => max(0, $diasHabiles - $conRegistro),
    ];
}

// Ordenar por días vacíos desc y tomar top 10
usort($diasVaciosPorPasante, fn($a, $b) => $b['dias_vacios'] - $a['dias_vacios']);
$top10 = array_slice($diasVaciosPorPasante, 0, 10);

if (empty($top10)) {
    fila("Resultado:", "No hay pasantes activos con fecha de inicio definida.");
} else {
    tabla_cli($top10, ['id', 'nombre', 'fecha_inicio', 'dias_habiles', 'dias_con_reg', 'dias_vacios']);
}

// ── C) Registros fuera de horario ────────────────────────────────────────────

titulo("C) REGISTROS FUERA DE HORARIO (hora < 07:00 o hora >= 17:00)");

$stmtFuera = $pdo->query(
    "SELECT
         a.id,
         a.pasante_id,
         CONCAT(p.nombre, ' ', p.apellido) AS pasante,
         a.fecha_registro,
         TIME(a.fecha_registro)            AS hora,
         a.estado
     FROM asistencias a
     LEFT JOIN pasantes p ON p.id = a.pasante_id
     WHERE HOUR(a.fecha_registro) < 7
        OR HOUR(a.fecha_registro) >= 17
     ORDER BY a.fecha_registro DESC
     LIMIT 50"
);
$fueraHorario = $stmtFuera->fetchAll();

$stmtFueraCount = $pdo->query(
    "SELECT COUNT(*) AS total FROM asistencias
     WHERE HOUR(fecha_registro) < 7 OR HOUR(fecha_registro) >= 17"
);
$totalFuera = (int)($stmtFueraCount->fetch()['total'] ?? 0);

fila("Total registros fuera de horario:", $totalFuera);
if ($totalFuera > 0) {
    fila("(Mostrando hasta 50 más recientes)", "");
    tabla_cli($fueraHorario, ['id', 'pasante_id', 'pasante', 'fecha_registro', 'hora', 'estado']);
}

// ── D) Resumen general ───────────────────────────────────────────────────────

titulo("D) RESUMEN GENERAL");

// Total registros
$totalReg = (int)$pdo->query("SELECT COUNT(*) AS n FROM asistencias")->fetch()['n'];
fila("Total de registros en asistencias:", $totalReg);

// Pasantes activos
$totalActivos = (int)$pdo->query(
    "SELECT COUNT(*) AS n FROM pasantes WHERE estatus = 'activo'"
)->fetch()['n'];
fila("Pasantes activos:", $totalActivos);

// Total feriados (si existe la tabla)
try {
    $totalFeriados = (int)$pdo->query(
        "SELECT COUNT(*) AS n FROM feriados"
    )->fetch()['n'];
    fila("Total de feriados registrados:", $totalFeriados);
} catch (PDOException $e) {
    fila("Tabla feriados:", "no existe o no es accesible (" . $e->getMessage() . ")");
}

// Verificar columna es_retardo
$stmtCol = $pdo->prepare(
    "SELECT COUNT(*) AS existe
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'asistencias'
       AND COLUMN_NAME  = 'es_retardo'"
);
$stmtCol->execute();
$existeRetardo = (int)($stmtCol->fetch()['existe'] ?? 0);
fila("¿Columna es_retardo existe en asistencias?", $existeRetardo ? "SI" : "NO - ejecutar migration_001.php");

// Rango de fechas en asistencias
if ($totalReg > 0) {
    $rango = $pdo->query(
        "SELECT MIN(DATE(fecha_registro)) AS desde, MAX(DATE(fecha_registro)) AS hasta
         FROM asistencias"
    )->fetch();
    fila("Rango de registros:", $rango['desde'] . " → " . $rango['hasta']);
}

// Timestamp del reporte
fila("Reporte generado:", (new DateTimeImmutable())->format('Y-m-d H:i:s'));

if (!$isCli) {
    echo "</body></html>";
}
echo PHP_EOL;
