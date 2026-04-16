<?php
/**
 * migration_001.php
 * Migración de la tabla asistencias:
 *   Paso 1 — ADD COLUMN es_retardo TINYINT(1) DEFAULT 0 (idempotente)
 *   Paso 2 — UPDATE estado 'abierto'/'cerrado' → 'Presente'
 *   Paso 3 — Marcar es_retardo = 1 donde hora_registro > 09:00 y estado = 'Presente'
 *
 * Ejecutar desde CLI: php scripts/migration_001.php
 * REQUIERE CLI para las confirmaciones interactivas (readline).
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    die("Este script solo puede ejecutarse desde la línea de comandos (CLI)." . PHP_EOL);
}

// ── Conexión ─────────────────────────────────────────────────────────────────

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
    echo "Conexion a la BD establecida correctamente." . PHP_EOL;
} catch (PDOException $e) {
    die("ERROR DE CONEXION: " . $e->getMessage() . PHP_EOL);
}

// ── Helpers ───────────────────────────────────────────────────────────────────

function titulo(string $texto): void
{
    $linea = str_repeat('=', 70);
    echo PHP_EOL . $linea . PHP_EOL . "  {$texto}" . PHP_EOL . $linea . PHP_EOL;
}

function confirmar(string $pregunta): bool
{
    echo PHP_EOL . $pregunta . " [s/N]: ";
    $respuesta = strtolower(trim(fgets(STDIN)));
    return $respuesta === 's' || $respuesta === 'si' || $respuesta === 'sí';
}

function ok(string $msg): void   { echo "  [OK]  " . $msg . PHP_EOL; }
function info(string $msg): void { echo "  [INFO] " . $msg . PHP_EOL; }
function warn(string $msg): void { echo "  [WARN] " . $msg . PHP_EOL; }
function err(string $msg): void  { echo "  [ERROR] " . $msg . PHP_EOL; }

// ── INICIO ────────────────────────────────────────────────────────────────────

echo PHP_EOL;
echo "╔══════════════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║          MIGRATION 001 - Normalización tabla asistencias            ║" . PHP_EOL;
echo "╚══════════════════════════════════════════════════════════════════════╝" . PHP_EOL;
echo PHP_EOL;
echo "Base de datos destino : " . $config['name'] . PHP_EOL;
echo "Host                  : " . $config['host'] . ":" . $config['port'] . PHP_EOL;
echo "Timestamp             : " . (new DateTimeImmutable())->format('Y-m-d H:i:s') . PHP_EOL;

// ════════════════════════════════════════════════════════════════════════════
// PASO 1 — ADD COLUMN es_retardo (idempotente)
// ════════════════════════════════════════════════════════════════════════════

titulo("PASO 1: ADD COLUMN es_retardo TINYINT(1) DEFAULT 0");

// Verificar si la columna ya existe
$stmtCheck = $pdo->prepare(
    "SELECT COUNT(*) AS existe
     FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'asistencias'
       AND COLUMN_NAME  = 'es_retardo'"
);
$stmtCheck->execute();
$columnaExiste = (int)($stmtCheck->fetch()['existe'] ?? 0) > 0;

if ($columnaExiste) {
    info("La columna 'es_retardo' YA EXISTE en la tabla asistencias. Se omite este paso.");
} else {
    info("La columna 'es_retardo' NO existe. Se procederá a crearla.");

    if (!confirmar("¿Desea agregar la columna es_retardo TINYINT(1) DEFAULT 0 a la tabla asistencias?")) {
        warn("Paso 1 cancelado por el usuario. Se interrumpe la migración.");
        exit(1);
    }

    try {
        $pdo->exec(
            "ALTER TABLE asistencias
             ADD COLUMN es_retardo TINYINT(1) NOT NULL DEFAULT 0
             COMMENT 'Indica si el registro fue marcado como retardo (1=sí, 0=no)'"
        );
        ok("Columna 'es_retardo' agregada exitosamente.");
    } catch (PDOException $e) {
        // Código 1060 = columna duplicada (race condition; tratar como OK)
        if (strpos($e->getMessage(), '1060') !== false) {
            info("La columna ya existía (creada por otro proceso). Continuando.");
        } else {
            err("Error al agregar columna: " . $e->getMessage());
            exit(1);
        }
    }
}

// ════════════════════════════════════════════════════════════════════════════
// PASO 2 — UPDATE estado legacy → 'Presente'
// ════════════════════════════════════════════════════════════════════════════

titulo("PASO 2: UPDATE estados legacy (abierto / cerrado) → 'Presente'");

// Conteo previo
$stmtPrev = $pdo->query(
    "SELECT estado, COUNT(*) AS total
     FROM asistencias
     WHERE LOWER(estado) IN ('abierto', 'cerrado')
     GROUP BY estado"
);
$legacyRows = $stmtPrev->fetchAll();

$totalLegacy = array_sum(array_column($legacyRows, 'total'));

if ($totalLegacy === 0) {
    info("No se encontraron registros con estado 'abierto' o 'cerrado'. Paso omitido.");
} else {
    echo PHP_EOL;
    echo "  Registros legacy encontrados:" . PHP_EOL;
    foreach ($legacyRows as $row) {
        printf("    %-15s → %d registros%s", $row['estado'], $row['total'], PHP_EOL);
    }
    printf("    %-15s → %d registros%s", "TOTAL", $totalLegacy, PHP_EOL);

    if (!confirmar("¿Desea actualizar estos {$totalLegacy} registros cambiando el estado a 'Presente'?")) {
        warn("Paso 2 cancelado por el usuario. Se interrumpe la migración.");
        exit(1);
    }

    try {
        $pdo->beginTransaction();

        $stmtUpd = $pdo->prepare(
            "UPDATE asistencias
             SET estado = 'Presente'
             WHERE LOWER(estado) IN ('abierto', 'cerrado')"
        );
        $stmtUpd->execute();
        $filasAfectadas = $stmtUpd->rowCount();

        $pdo->commit();
        ok("Se actualizaron {$filasAfectadas} registros → estado = 'Presente'.");
    } catch (PDOException $e) {
        $pdo->rollBack();
        err("Error durante la actualización: " . $e->getMessage());
        exit(1);
    }
}

// ════════════════════════════════════════════════════════════════════════════
// PASO 3 — Marcar es_retardo = 1 donde hora > 09:00 y estado = 'Presente'
// ════════════════════════════════════════════════════════════════════════════

titulo("PASO 3: Marcar es_retardo = 1 donde hora_registro > 09:00 y estado = 'Presente'");

// Verificar nuevamente que la columna existe (pudo haberse omitido en paso 1)
$stmtCheck->execute();
$columnaExisteAhora = (int)($stmtCheck->fetch()['existe'] ?? 0) > 0;

if (!$columnaExisteAhora) {
    err("La columna 'es_retardo' no existe. No se puede ejecutar el Paso 3.");
    err("Asegúrese de completar el Paso 1 antes de intentar el Paso 3.");
    exit(1);
}

// Conteo previo
$stmtPrev3 = $pdo->query(
    "SELECT COUNT(*) AS total
     FROM asistencias
     WHERE estado = 'Presente'
       AND TIME(fecha_registro) > '09:00:00'
       AND (es_retardo = 0 OR es_retardo IS NULL)"
);
$totalRetardos = (int)($stmtPrev3->fetch()['total'] ?? 0);

// También mostrar cuántos ya tienen es_retardo = 1
$stmtYaMarcados = $pdo->query(
    "SELECT COUNT(*) AS total FROM asistencias WHERE es_retardo = 1"
);
$yaMarcados = (int)($stmtYaMarcados->fetch()['total'] ?? 0);

info("Registros con es_retardo = 1 (ya marcados previamente): {$yaMarcados}");
info("Registros candidatos a marcar ahora (estado='Presente', hora > 09:00, es_retardo=0): {$totalRetardos}");

if ($totalRetardos === 0) {
    info("No hay registros nuevos que marcar como retardo. Paso omitido.");
} else {
    // Muestra una muestra de los registros que serán afectados
    $stmtMuestra = $pdo->query(
        "SELECT
             a.id,
             CONCAT(p.nombre, ' ', p.apellido) AS pasante,
             DATE(a.fecha_registro)             AS fecha,
             TIME(a.fecha_registro)             AS hora,
             a.estado
         FROM asistencias a
         LEFT JOIN pasantes p ON p.id = a.pasante_id
         WHERE a.estado = 'Presente'
           AND TIME(a.fecha_registro) > '09:00:00'
           AND (a.es_retardo = 0 OR a.es_retardo IS NULL)
         ORDER BY a.fecha_registro DESC
         LIMIT 10"
    );
    $muestra = $stmtMuestra->fetchAll();

    echo PHP_EOL . "  Muestra de registros a marcar (máx. 10):" . PHP_EOL;
    printf(
        "    %-6s %-28s %-12s %-10s %-12s%s",
        'ID', 'Pasante', 'Fecha', 'Hora', 'Estado', PHP_EOL
    );
    echo "    " . str_repeat('-', 72) . PHP_EOL;
    foreach ($muestra as $r) {
        printf(
            "    %-6s %-28s %-12s %-10s %-12s%s",
            $r['id'],
            substr($r['pasante'] ?? 'N/A', 0, 27),
            $r['fecha'],
            $r['hora'],
            $r['estado'],
            PHP_EOL
        );
    }
    if ($totalRetardos > 10) {
        echo "    ... y " . ($totalRetardos - 10) . " más." . PHP_EOL;
    }

    if (!confirmar("¿Desea marcar {$totalRetardos} registros como es_retardo = 1?")) {
        warn("Paso 3 cancelado por el usuario.");
        exit(1);
    }

    try {
        $pdo->beginTransaction();

        $stmtMark = $pdo->prepare(
            "UPDATE asistencias
             SET es_retardo = 1
             WHERE estado = 'Presente'
               AND TIME(fecha_registro) > '09:00:00'
               AND (es_retardo = 0 OR es_retardo IS NULL)"
        );
        $stmtMark->execute();
        $filasAfectadas3 = $stmtMark->rowCount();

        $pdo->commit();
        ok("Se marcaron {$filasAfectadas3} registros con es_retardo = 1.");
    } catch (PDOException $e) {
        $pdo->rollBack();
        err("Error durante la actualización de es_retardo: " . $e->getMessage());
        exit(1);
    }
}

// ── Resumen final ─────────────────────────────────────────────────────────────

titulo("MIGRACIÓN COMPLETADA");

$totalReg    = (int)$pdo->query("SELECT COUNT(*) AS n FROM asistencias")->fetch()['n'];
$totalPres   = (int)$pdo->query("SELECT COUNT(*) AS n FROM asistencias WHERE estado = 'Presente'")->fetch()['n'];
$totalRet    = (int)$pdo->query("SELECT COUNT(*) AS n FROM asistencias WHERE es_retardo = 1")->fetch()['n'];
$totalLegAun = (int)$pdo->query(
    "SELECT COUNT(*) AS n FROM asistencias WHERE LOWER(estado) IN ('abierto','cerrado')"
)->fetch()['n'];

printf("  %-40s %d%s", "Total registros en asistencias:",   $totalReg,    PHP_EOL);
printf("  %-40s %d%s", "Registros con estado 'Presente':",  $totalPres,   PHP_EOL);
printf("  %-40s %d%s", "Registros marcados como retardo:",  $totalRet,    PHP_EOL);
printf("  %-40s %d%s", "Registros legacy restantes:",       $totalLegAun, PHP_EOL);

if ($totalLegAun > 0) {
    warn("Aún existen {$totalLegAun} registros con estados legacy. Revise manualmente.");
} else {
    ok("No quedan estados legacy. La migración está completa.");
}

echo PHP_EOL;
