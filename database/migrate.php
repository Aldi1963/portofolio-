<?php
/**
 * Database Migration Runner
 * -------------------------
 * Runs all SQL files in database/migrations/ that have not been
 * applied yet. Tracks applied files in the `migrations` table.
 *
 * Usage:
 *   - CLI    : php database/migrate.php
 *   - Browser: https://yoursite.com/database/migrate.php?key=YOUR_SECRET
 *              (the secret is printed to .env as MIGRATE_KEY, or set it manually)
 *
 * Safety: in browser mode this script requires ?key=... to match
 * MIGRATE_KEY from .env so it can't be triggered by strangers.
 */

declare(strict_types=1);

// ─── Bootstrap ─────────────────────────────────────────────────
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

$isCli = (php_sapi_name() === 'cli');

// Browser guard: require matching secret
if (!$isCli) {
    $expected = env('MIGRATE_KEY', '');
    $provided = $_GET['key'] ?? '';
    if ($expected === '' || !hash_equals($expected, (string) $provided)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo "Forbidden. Set MIGRATE_KEY in .env and call this URL with ?key=<value>.\n";
        exit;
    }
    header('Content-Type: text/plain; charset=UTF-8');
}

// ─── Connect ───────────────────────────────────────────────────
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    fwrite(STDERR, 'DB connect failed: ' . $e->getMessage() . PHP_EOL);
    echo 'DB connect failed: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}

// ─── Ensure migrations table ───────────────────────────────────
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `migrations` (
        `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `filename`   VARCHAR(255) NOT NULL UNIQUE,
        `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

$applied = $pdo->query('SELECT filename FROM migrations')->fetchAll(PDO::FETCH_COLUMN);
$applied = array_flip($applied);

// ─── Discover migrations ───────────────────────────────────────
$dir = __DIR__ . '/migrations';
if (!is_dir($dir)) {
    echo "No migrations directory. Nothing to do.\n";
    exit(0);
}

$files = glob($dir . '/*.sql') ?: [];
sort($files, SORT_NATURAL);

if (!$files) {
    echo "No migration files found.\n";
    exit(0);
}

// ─── Run ───────────────────────────────────────────────────────
$ran = 0;
foreach ($files as $file) {
    $name = basename($file);
    if (isset($applied[$name])) {
        echo "SKIP  $name (already applied)\n";
        continue;
    }

    $sql = file_get_contents($file);
    if ($sql === false || trim($sql) === '') {
        echo "SKIP  $name (empty file)\n";
        continue;
    }

    echo "APPLY $name ... ";
    try {
        // Split on DELIMITER directives so we can support stored procedures.
        // This is a lightweight parser good enough for our migration files.
        runSqlScript($pdo, $sql);

        $stmt = $pdo->prepare('INSERT INTO migrations (filename) VALUES (?)');
        $stmt->execute([$name]);

        echo "OK\n";
        $ran++;
    } catch (Throwable $e) {
        echo "FAILED\n  " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "\nDone. Applied $ran new migration(s).\n";

/**
 * Execute a multi-statement SQL script. Handles DELIMITER directives
 * so CREATE PROCEDURE blocks work.
 */
function runSqlScript(PDO $pdo, string $sql): void
{
    $delimiter = ';';
    $buffer    = '';
    $lines     = preg_split('/\R/', $sql);

    foreach ($lines as $line) {
        $trimmed = trim($line);

        // DELIMITER directive (client-side, not sent to server)
        if (stripos($trimmed, 'DELIMITER ') === 0) {
            $delimiter = trim(substr($trimmed, 10));
            continue;
        }

        // Strip SQL line comments (but keep string content intact enough for our needs)
        if ($trimmed === '' || str_starts_with($trimmed, '--')) {
            continue;
        }

        $buffer .= $line . "\n";

        // End of statement?
        if (str_ends_with(rtrim($buffer), $delimiter)) {
            $stmt = rtrim(rtrim($buffer), $delimiter);
            $stmt = trim($stmt);
            if ($stmt !== '') {
                $pdo->exec($stmt);
            }
            $buffer = '';
        }
    }

    // Trailing statement without terminator
    $stmt = trim($buffer);
    if ($stmt !== '') {
        $pdo->exec($stmt);
    }
}
