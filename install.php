<?php
/**
 * Professional Portfolio Installer v2.0
 * Multi-step wizard with system checks, DB testing, and progress UI
 * Single-file installer - DELETE AFTER INSTALLATION
 */

// ─── CONFIGURATION ────────────────────────────────────────────────
define('MIN_PHP_VERSION', '8.0.0');
define('REQUIRED_EXTENSIONS', ['pdo', 'pdo_mysql', 'mbstring', 'fileinfo', 'json', 'curl', 'gd']);
define('WRITABLE_DIRS', ['uploads', 'cache']);

// ─── CHECK IF ALREADY INSTALLED ───────────────────────────────────
$lockFile = __DIR__ . '/cache/.installed';
if (file_exists($lockFile) && !isset($_GET['force'])) {
    header('Content-Type: text/html; charset=UTF-8');
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Already Installed</title><style>body{background:#0a0a0f;color:#fff;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}.card{background:rgba(18,18,26,0.95);backdrop-filter:blur(20px);border:1px solid rgba(255,107,0,0.2);border-radius:20px;padding:50px;text-align:center;max-width:450px}h1{color:#ff6b00;margin-bottom:12px;font-size:1.6rem}p{color:#a0a0b0;margin-bottom:20px;line-height:1.6}code{background:rgba(255,107,0,0.1);color:#ff6b00;padding:3px 8px;border-radius:4px;font-size:0.85rem}a{display:inline-block;margin-top:16px;padding:12px 28px;background:linear-gradient(135deg,#ff6b00,#e55500);color:#fff;border-radius:10px;text-decoration:none;font-weight:600;transition:all 0.3s}a:hover{transform:translateY(-2px);box-shadow:0 8px 25px rgba(255,107,0,0.3)}</style></head><body><div class="card"><h1>&#9989; Already Installed</h1><p>This application has already been installed.<br>To reinstall, delete <code>cache/.installed</code></p><a href="/">Go to Website &rarr;</a></div></body></html>');
}

// ─── HANDLE AJAX ACTIONS ──────────────────────────────────────────
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=UTF-8');
    
    switch ($_GET['action']) {
        case 'check_requirements':
            echo json_encode(checkRequirements());
            exit;
            
        case 'test_db':
            $input = json_decode(file_get_contents('php://input'), true);
            echo json_encode(testDatabaseConnection(
                $input['host'] ?? 'localhost',
                $input['name'] ?? '',
                $input['user'] ?? 'root',
                $input['pass'] ?? ''
            ));
            exit;
            
        case 'install':
            $input = json_decode(file_get_contents('php://input'), true);
            echo json_encode(performInstallation($input));
            exit;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
            exit;
    }
}

// ─── HELPER FUNCTIONS ─────────────────────────────────────────────

function checkRequirements(): array {
    $results = [];
    $allPassed = true;
    
    // PHP Version
    $phpOk = version_compare(PHP_VERSION, MIN_PHP_VERSION, '>=');
    $results[] = [
        'name' => 'PHP Version',
        'required' => '>= ' . MIN_PHP_VERSION,
        'current' => PHP_VERSION,
        'passed' => $phpOk,
        'critical' => true
    ];
    if (!$phpOk) $allPassed = false;
    
    // Required Extensions
    foreach (REQUIRED_EXTENSIONS as $ext) {
        $loaded = extension_loaded($ext);
        $results[] = [
            'name' => "PHP Extension: $ext",
            'required' => 'Installed',
            'current' => $loaded ? 'Installed' : 'Not Found',
            'passed' => $loaded,
            'critical' => in_array($ext, ['pdo', 'pdo_mysql', 'mbstring', 'json'])
        ];
        if (!$loaded && in_array($ext, ['pdo', 'pdo_mysql', 'mbstring', 'json'])) {
            $allPassed = false;
        }
    }
    
    // Writable Directories
    foreach (WRITABLE_DIRS as $dir) {
        $path = __DIR__ . '/' . $dir;
        $writable = is_dir($path) && is_writable($path);
        $results[] = [
            'name' => "Directory: $dir/",
            'required' => 'Writable',
            'current' => $writable ? 'Writable' : (is_dir($path) ? 'Not Writable' : 'Not Found'),
            'passed' => $writable,
            'critical' => true
        ];
        if (!$writable) $allPassed = false;
    }
    
    // SQL File exists
    $sqlExists = file_exists(__DIR__ . '/database/portfolio_db.sql');
    $results[] = [
        'name' => 'SQL Schema File',
        'required' => 'Present',
        'current' => $sqlExists ? 'Found' : 'Missing',
        'passed' => $sqlExists,
        'critical' => true
    ];
    if (!$sqlExists) $allPassed = false;
    
    // Server info
    $serverInfo = [
        'php_version' => PHP_VERSION,
        'os' => PHP_OS,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'max_upload' => ini_get('upload_max_filesize'),
        'max_post' => ini_get('post_max_size'),
        'memory_limit' => ini_get('memory_limit'),
    ];
    
    return [
        'success' => true,
        'all_passed' => $allPassed,
        'results' => $results,
        'server_info' => $serverInfo
    ];
}

function testDatabaseConnection(string $host, string $name, string $user, string $pass): array {
    try {
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        
        // Get MySQL version
        $version = $pdo->query("SELECT VERSION()")->fetchColumn();
        
        // Check if database exists
        $stmt = $pdo->prepare("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?");
        $stmt->execute([$name]);
        $dbExists = $stmt->fetchColumn() !== false;
        
        return [
            'success' => true,
            'message' => 'Connection successful!',
            'mysql_version' => $version,
            'db_exists' => $dbExists,
            'db_message' => $dbExists ? "Database '$name' already exists (will use it)" : "Database '$name' will be created during installation"
        ];
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        // Simplify common errors
        if (strpos($msg, 'Connection refused') !== false) {
            $msg = "Connection refused. Is MySQL running on '$host'?";
        } elseif (strpos($msg, 'Access denied') !== false) {
            $msg = "Access denied. Check username and password.";
        } elseif (strpos($msg, 'Unknown host') !== false || strpos($msg, 'getaddrinfo') !== false) {
            $msg = "Cannot resolve host '$host'. Check the hostname.";
        }
        return ['success' => false, 'message' => $msg];
    }
}

function performInstallation(array $data): array {
    $steps = [];
    $errors = [];
    
    $host = trim($data['db_host'] ?? 'localhost');
    $name = trim($data['db_name'] ?? 'portfolio_db');
    $user = trim($data['db_user'] ?? 'root');
    $pass = $data['db_pass'] ?? '';
    $appUrl = rtrim(trim($data['app_url'] ?? ''), '/');
    $appName = trim($data['app_name'] ?? 'MyPortfolio');
    $appLang = $data['app_lang'] ?? 'id';
    $ownerName = trim($data['owner_name'] ?? '');
    $ownerEmail = trim($data['owner_email'] ?? '');
    $whatsapp = trim($data['whatsapp'] ?? '');
    $adminUser = trim($data['admin_user'] ?? 'admin');
    $adminPass = $data['admin_pass'] ?? 'admin123';
    
    try {
        // Step 1: Connect to MySQL
        $dsn = "mysql:host=$host;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $steps[] = ['step' => 'db_connect', 'status' => 'done', 'message' => 'Connected to MySQL server'];
        
        // Step 2: Create/use database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$name`");
        $steps[] = ['step' => 'db_create', 'status' => 'done', 'message' => "Database '$name' ready"];
        
        // Step 3: Import SQL schema
        $sqlFile = __DIR__ . '/database/portfolio_db.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found: database/portfolio_db.sql");
        }
        
        $sql = file_get_contents($sqlFile);
        $sql = preg_replace('/CREATE DATABASE.*?;/s', '', $sql);
        $sql = preg_replace('/USE\s+`.*?`;/s', '', $sql);
        $sql = preg_replace('/^--.*$/m', '', $sql);
        $sql = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $sql);
        $sql = str_replace(['START TRANSACTION;', 'COMMIT;', 'SET AUTOCOMMIT = 0;'], '', $sql);
        
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        $executed = 0;
        $failed = 0;
        $failedStmts = [];
        
        foreach ($statements as $stmt) {
            if (!empty($stmt) && strlen($stmt) > 5) {
                try {
                    $pdo->exec($stmt);
                    $executed++;
                } catch (PDOException $e) {
                    $failed++;
                    if (count($failedStmts) < 5) {
                        $failedStmts[] = [
                            'statement' => mb_substr($stmt, 0, 100) . '...',
                            'error' => $e->getMessage()
                        ];
                    }
                }
            }
        }
        
        $msg = "Executed $executed SQL statements";
        if ($failed > 0) $msg .= " ($failed skipped/failed)";
        $steps[] = ['step' => 'db_import', 'status' => 'done', 'message' => $msg, 'failed_statements' => $failedStmts];
        
        // Step 4: Configure admin account
        $hashedPass = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, full_name = ?, email = ?, login_attempts = 0, locked_until = NULL WHERE id = 1");
        $stmt->execute([$adminUser, $hashedPass, $ownerName ?: 'Admin', $ownerEmail ?: 'admin@portfolio.com']);
        $steps[] = ['step' => 'admin_setup', 'status' => 'done', 'message' => 'Admin account configured'];
        
        // Step 5: Update site settings
        $updates = [
            'site_name' => $appName,
            'owner_name' => $ownerName ?: 'Admin',
            'owner_email' => $ownerEmail,
            'whatsapp_number' => $whatsapp,
            'hero_title' => 'Hi, I am ' . ($ownerName ?: 'Admin'),
        ];
        $stmtUpdate = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        foreach ($updates as $key => $val) {
            if (!empty($val)) $stmtUpdate->execute([$val, $key]);
        }
        $steps[] = ['step' => 'settings', 'status' => 'done', 'message' => 'Site settings updated'];
        
        // Step 6: Generate .env file
        $env = "# Auto-generated by Portfolio Installer\n";
        $env .= "# Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $env .= "DB_HOST={$host}\nDB_NAME={$name}\nDB_USER={$user}\nDB_PASS={$pass}\n\n";
        $env .= "APP_NAME={$appName}\nAPP_URL={$appUrl}\nAPP_ENV=production\nAPP_DEBUG=false\nAPP_LANG={$appLang}\n\n";
        $env .= "SESSION_LIFETIME=3600\nCSRF_TOKEN_LIFETIME=3600\nLOGIN_MAX_ATTEMPTS=5\nLOGIN_LOCKOUT_TIME=900\n";
        file_put_contents(__DIR__ . '/.env', $env);
        $steps[] = ['step' => 'env_file', 'status' => 'done', 'message' => '.env configuration file created'];
        
        // Step 7: Create upload directories
        $dirs = ['uploads/images', 'uploads/projects', 'uploads/blog', 'uploads/testimonials', 'uploads/settings', 'cache'];
        foreach ($dirs as $dir) {
            $path = __DIR__ . '/' . $dir;
            if (!is_dir($path)) mkdir($path, 0755, true);
        }
        $steps[] = ['step' => 'directories', 'status' => 'done', 'message' => 'Upload directories created'];
        
        // Step 8: Create lock file
        $lockFile = __DIR__ . '/cache/.installed';
        file_put_contents($lockFile, json_encode([
            'installed_at' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'installer_version' => '2.0'
        ]));
        $steps[] = ['step' => 'finalize', 'status' => 'done', 'message' => 'Installation finalized & locked'];
        
        return [
            'success' => true,
            'steps' => $steps,
            'admin_url' => $appUrl . '/admin/login',
            'admin_user' => $adminUser,
            'admin_pass' => $adminPass
        ];
        
    } catch (PDOException $e) {
        $steps[] = ['step' => 'error', 'status' => 'fail', 'message' => 'Database Error: ' . $e->getMessage()];
        return ['success' => false, 'steps' => $steps, 'error' => $e->getMessage()];
    } catch (Exception $e) {
        $steps[] = ['step' => 'error', 'status' => 'fail', 'message' => $e->getMessage()];
        return ['success' => false, 'steps' => $steps, 'error' => $e->getMessage()];
    }
}

// ─── AUTO-DETECT VALUES ───────────────────────────────────────────
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$detectedUrl = $protocol . '://' . $host . $scriptDir;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Installer v2.0</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0a0a0f;
            --bg-card: rgba(18, 18, 26, 0.95);
            --bg-input: #1a1a2e;
            --accent: #ff6b00;
            --accent-hover: #e55500;
            --accent-glow: rgba(255, 107, 0, 0.15);
            --text-primary: #ffffff;
            --text-secondary: #a0a0b0;
            --text-muted: #6b6b80;
            --border: rgba(255, 255, 255, 0.08);
            --border-accent: rgba(255, 107, 0, 0.3);
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
            --radius: 12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background-image: 
                radial-gradient(ellipse at 20% 50%, rgba(255,107,0,0.03) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(255,107,0,0.02) 0%, transparent 50%);
        }

        .installer-container {
            width: 100%;
            max-width: 720px;
        }

        .installer-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 48px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        /* Header */
        .installer-header {
            text-align: center;
            margin-bottom: 36px;
        }

        .installer-logo {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .installer-logo .hl { color: var(--accent); }

        .installer-title {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .installer-subtitle {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        /* Steps indicator */
        .steps-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin: 32px 0;
            padding: 0 20px;
        }

        .step-dot {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            background: var(--bg-input);
            border: 2px solid var(--border);
            color: var(--text-muted);
            transition: all 0.4s ease;
            position: relative;
            flex-shrink: 0;
        }

        .step-dot.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
            box-shadow: 0 0 20px var(--accent-glow);
        }

        .step-dot.completed {
            background: var(--success);
            border-color: var(--success);
            color: #fff;
        }

        .step-line {
            flex: 1;
            height: 2px;
            background: var(--border);
            transition: background 0.4s ease;
            min-width: 20px;
        }

        .step-line.completed { background: var(--success); }

        /* Step panels */
        .step-panel {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .step-panel.active { display: block; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Step title */
        .step-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .step-description {
            color: var(--text-secondary);
            font-size: 0.88rem;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        /* Requirements table */
        .req-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .req-table tr {
            border-bottom: 1px solid var(--border);
        }

        .req-table td {
            padding: 12px 8px;
            font-size: 0.85rem;
        }

        .req-table td:first-child { color: var(--text-secondary); width: 50%; }
        .req-table td:nth-child(2) { color: var(--text-muted); width: 30%; }
        .req-table td:last-child { text-align: right; width: 20%; }

        .req-pass { color: var(--success); font-weight: 600; }
        .req-fail { color: var(--error); font-weight: 600; }
        .req-warn { color: var(--warning); font-weight: 600; }

        .server-info {
            background: rgba(255,107,0,0.05);
            border: 1px solid rgba(255,107,0,0.12);
            border-radius: var(--radius);
            padding: 16px;
            margin-bottom: 20px;
        }

        .server-info-title {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--accent);
            font-weight: 600;
            margin-bottom: 10px;
        }

        .server-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .server-info-item {
            font-size: 0.82rem;
            color: var(--text-secondary);
        }

        .server-info-item span { color: var(--text-primary); font-weight: 500; }

        /* Form styles */
        .section-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--accent);
            font-weight: 600;
            margin: 28px 0 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255,107,0,0.1);
        }

        .section-label:first-child { margin-top: 0; }

        .form-group {
            margin-bottom: 16px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text-primary);
            font-size: 0.9rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .form-input.error { border-color: var(--error); }
        .form-input.success { border-color: var(--success); }

        .form-help {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 5px;
        }

        /* Buttons */
        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 10px;
            font-size: 0.92rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent-hover));
            color: #fff;
            box-shadow: 0 4px 15px rgba(255,107,0,0.25);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255,107,0,0.35);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-secondary {
            background: var(--bg-input);
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        .btn-test {
            background: rgba(255,107,0,0.1);
            color: var(--accent);
            border: 1px solid rgba(255,107,0,0.3);
            padding: 10px 20px;
            font-size: 0.84rem;
        }

        .btn-test:hover {
            background: rgba(255,107,0,0.2);
        }

        .btn-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 28px;
            gap: 12px;
        }

        /* Test connection result */
        .test-result {
            margin-top: 12px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.84rem;
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .test-result.show { display: block; }
        .test-result.success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: var(--success); }
        .test-result.error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: var(--error); }

        /* Progress steps */
        .progress-list {
            list-style: none;
            margin: 20px 0;
        }

        .progress-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 8px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            transition: all 0.4s ease;
        }

        .progress-item.pending { opacity: 0.5; }
        .progress-item.running { border-color: var(--accent); background: rgba(255,107,0,0.05); }
        .progress-item.done { border-color: rgba(16,185,129,0.3); background: rgba(16,185,129,0.05); }
        .progress-item.fail { border-color: rgba(239,68,68,0.3); background: rgba(239,68,68,0.05); }

        .progress-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        .progress-item.pending .progress-icon { background: var(--border); color: var(--text-muted); }
        .progress-item.running .progress-icon { background: var(--accent); color: #fff; animation: pulse 1.5s infinite; }
        .progress-item.done .progress-icon { background: var(--success); color: #fff; }
        .progress-item.fail .progress-icon { background: var(--error); color: #fff; }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }

        .progress-text {
            font-size: 0.88rem;
            color: var(--text-secondary);
        }

        .progress-item.done .progress-text { color: var(--success); }
        .progress-item.fail .progress-text { color: var(--error); }
        .progress-item.running .progress-text { color: var(--accent); font-weight: 500; }

        /* Success panel */
        .success-box {
            text-align: center;
            padding: 20px 0;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(16,185,129,0.1);
            border: 3px solid var(--success);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
        }

        .success-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--success);
            margin-bottom: 8px;
        }

        .success-subtitle {
            color: var(--text-secondary);
            margin-bottom: 24px;
        }

        .credentials-card {
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            text-align: left;
            margin: 20px 0;
        }

        .credentials-card h4 {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--accent);
            margin-bottom: 12px;
        }

        .credential-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid var(--border);
        }

        .credential-row:last-child { border: none; }

        .credential-label { color: var(--text-muted); font-size: 0.84rem; }
        .credential-value { color: var(--text-primary); font-weight: 600; font-size: 0.9rem; font-family: 'Courier New', monospace; }

        .warning-box {
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 10px;
            padding: 16px;
            margin-top: 20px;
            text-align: center;
        }

        .warning-box p {
            color: var(--error);
            font-size: 0.88rem;
            font-weight: 600;
        }

        /* Error detail collapsible */
        .error-detail {
            margin-top: 12px;
        }

        .error-detail summary {
            cursor: pointer;
            color: var(--text-muted);
            font-size: 0.82rem;
            padding: 8px 0;
        }

        .error-detail pre {
            background: rgba(0,0,0,0.3);
            padding: 12px;
            border-radius: 8px;
            font-size: 0.78rem;
            color: var(--error);
            overflow-x: auto;
            margin-top: 8px;
            white-space: pre-wrap;
            word-break: break-all;
        }

        /* Spinner */
        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Responsive */
        @media (max-width: 640px) {
            .installer-card { padding: 28px 20px; }
            .form-row { grid-template-columns: 1fr; }
            .server-info-grid { grid-template-columns: 1fr; }
            .btn-group { flex-direction: column-reverse; }
            .btn-group .btn { width: 100%; justify-content: center; }
            .steps-indicator { padding: 0; }
            .step-dot { width: 30px; height: 30px; font-size: 0.72rem; }
        }
    </style>
</head>
<body>
<div class="installer-container">
    <div class="installer-card">
        <!-- Header -->
        <div class="installer-header">
            <div class="installer-logo">&lt;<span class="hl">Portfolio</span>/&gt;</div>
            <h1 class="installer-title">Installation Wizard</h1>
            <p class="installer-subtitle">Professional setup in 5 easy steps</p>
        </div>

        <!-- Steps Indicator -->
        <div class="steps-indicator">
            <div class="step-dot active" data-step="1">1</div>
            <div class="step-line" data-line="1"></div>
            <div class="step-dot" data-step="2">2</div>
            <div class="step-line" data-line="2"></div>
            <div class="step-dot" data-step="3">3</div>
            <div class="step-line" data-line="3"></div>
            <div class="step-dot" data-step="4">4</div>
            <div class="step-line" data-line="4"></div>
            <div class="step-dot" data-step="5">5</div>
        </div>


        <!-- ═══ STEP 1: System Requirements ═══ -->
        <div class="step-panel active" id="step-1">
            <h2 class="step-title">System Requirements Check</h2>
            <p class="step-description">Verifying your server meets all requirements for installation.</p>
            
            <div id="req-loading" style="text-align:center;padding:40px 0;">
                <div class="spinner" style="width:32px;height:32px;border-width:3px;margin:0 auto 16px;border-color:rgba(255,107,0,0.2);border-top-color:var(--accent)"></div>
                <p style="color:var(--text-muted);font-size:0.88rem;">Checking system requirements...</p>
            </div>
            
            <div id="req-results" style="display:none;">
                <div id="server-info-box" class="server-info">
                    <div class="server-info-title">Server Information</div>
                    <div class="server-info-grid" id="server-info-grid"></div>
                </div>
                
                <table class="req-table" id="req-table">
                    <tbody></tbody>
                </table>
                
                <div id="req-summary" style="margin-top:16px;padding:14px;border-radius:10px;font-size:0.88rem;text-align:center;"></div>
            </div>
            
            <div class="btn-group">
                <span></span>
                <button class="btn btn-primary" id="btn-step1-next" disabled onclick="goToStep(2)">
                    Continue &rarr;
                </button>
            </div>
        </div>

        <!-- ═══ STEP 2: Database Configuration ═══ -->
        <div class="step-panel" id="step-2">
            <h2 class="step-title">Database Configuration</h2>
            <p class="step-description">Enter your MySQL database credentials. You can test the connection before proceeding.</p>
            
            <div class="section-label">Connection Details</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Database Host</label>
                    <input type="text" class="form-input" id="db_host" value="localhost">
                    <div class="form-help">Usually "localhost" or "127.0.0.1"</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Database Name</label>
                    <input type="text" class="form-input" id="db_name" value="portfolio_db">
                    <div class="form-help">Will be created if it doesn't exist</div>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" id="db_user" value="root">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-input" id="db_pass" placeholder="Leave empty if none">
                </div>
            </div>
            
            <div style="text-align:center;margin-top:16px;">
                <button class="btn btn-test" id="btn-test-db" onclick="testConnection()">
                    &#9889; Test Connection
                </button>
            </div>
            
            <div class="test-result" id="db-test-result"></div>
            
            <div class="btn-group">
                <button class="btn btn-secondary" onclick="goToStep(1)">&larr; Back</button>
                <button class="btn btn-primary" id="btn-step2-next" onclick="goToStep(3)">
                    Continue &rarr;
                </button>
            </div>
        </div>


        <!-- ═══ STEP 3: Website & Admin Setup ═══ -->
        <div class="step-panel" id="step-3">
            <h2 class="step-title">Website &amp; Admin Setup</h2>
            <p class="step-description">Configure your website details and admin account credentials.</p>
            
            <div class="section-label">Website Settings</div>
            <div class="form-group">
                <label class="form-label">Website URL</label>
                <input type="url" class="form-input" id="app_url" value="<?= htmlspecialchars($detectedUrl) ?>">
                <div class="form-help">Auto-detected from current request. No trailing slash.</div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Website Name</label>
                    <input type="text" class="form-input" id="app_name" value="MyPortfolio">
                </div>
                <div class="form-group">
                    <label class="form-label">Language</label>
                    <select class="form-input form-select" id="app_lang">
                        <option value="id">Bahasa Indonesia</option>
                        <option value="en">English</option>
                    </select>
                </div>
            </div>
            
            <div class="section-label">Your Profile</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-input" id="owner_name" placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-input" id="owner_email" placeholder="you@email.com">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">WhatsApp Number</label>
                <input type="text" class="form-input" id="whatsapp" placeholder="6281234567890">
                <div class="form-help">With country code, no + or spaces (e.g. 6281234567890)</div>
            </div>
            
            <div class="section-label">Admin Account</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Admin Username</label>
                    <input type="text" class="form-input" id="admin_user" value="admin">
                </div>
                <div class="form-group">
                    <label class="form-label">Admin Password</label>
                    <input type="password" class="form-input" id="admin_pass" value="admin123">
                    <div class="form-help">Use a strong password!</div>
                </div>
            </div>
            
            <div class="btn-group">
                <button class="btn btn-secondary" onclick="goToStep(2)">&larr; Back</button>
                <button class="btn btn-primary" onclick="startInstallation()">
                    &#128640; Install Now
                </button>
            </div>
        </div>


        <!-- ═══ STEP 4: Installation Progress ═══ -->
        <div class="step-panel" id="step-4">
            <h2 class="step-title">Installing...</h2>
            <p class="step-description">Please wait while we set everything up. Do not close this page.</p>
            
            <ul class="progress-list" id="progress-list">
                <li class="progress-item pending" data-task="db_connect">
                    <div class="progress-icon">&#9679;</div>
                    <div class="progress-text">Connecting to database server</div>
                </li>
                <li class="progress-item pending" data-task="db_create">
                    <div class="progress-icon">&#9679;</div>
                    <div class="progress-text">Creating database</div>
                </li>
                <li class="progress-item pending" data-task="db_import">
                    <div class="progress-icon">&#9679;</div>
                    <div class="progress-text">Importing SQL schema &amp; data</div>
                </li>
                <li class="progress-item pending" data-task="admin_setup">
                    <div class="progress-icon">&#9679;</div>
                    <div class="progress-text">Setting up admin account</div>
                </li>
                <li class="progress-item pending" data-task="settings">
                    <div class="progress-icon">&#9679;</div>
                    <div class="progress-text">Saving site settings</div>
                </li>
                <li class="progress-item pending" data-task="env_file">
                    <div class="progress-icon">&#9679;</div>
                    <div class="progress-text">Generating .env configuration</div>
                </li>
                <li class="progress-item pending" data-task="directories">
                    <div class="progress-icon">&#9679;</div>
                    <div class="progress-text">Creating upload directories</div>
                </li>
                <li class="progress-item pending" data-task="finalize">
                    <div class="progress-icon">&#9679;</div>
                    <div class="progress-text">Finalizing installation</div>
                </li>
            </ul>
            
            <div id="install-error" style="display:none;">
                <div class="test-result error show" id="install-error-msg"></div>
                <div class="error-detail" id="install-error-detail" style="display:none;">
                    <details>
                        <summary>View Error Details</summary>
                        <pre id="install-error-pre"></pre>
                    </details>
                </div>
                <div class="btn-group" style="margin-top:16px;">
                    <button class="btn btn-secondary" onclick="goToStep(2)">&larr; Check Database</button>
                    <button class="btn btn-primary" onclick="retryInstallation()">&#8635; Retry</button>
                </div>
            </div>
        </div>

        <!-- ═══ STEP 5: Success ═══ -->
        <div class="step-panel" id="step-5">
            <div class="success-box">
                <div class="success-icon">&#10003;</div>
                <h2 class="success-title">Installation Complete!</h2>
                <p class="success-subtitle">Your portfolio website is ready to use.</p>
                
                <div class="credentials-card">
                    <h4>&#128272; Admin Login Credentials</h4>
                    <div class="credential-row">
                        <span class="credential-label">Login URL</span>
                        <span class="credential-value" id="cred-url"></span>
                    </div>
                    <div class="credential-row">
                        <span class="credential-label">Username</span>
                        <span class="credential-value" id="cred-user"></span>
                    </div>
                    <div class="credential-row">
                        <span class="credential-label">Password</span>
                        <span class="credential-value" id="cred-pass"></span>
                    </div>
                </div>
                
                <div class="warning-box">
                    <p>&#9888; IMPORTANT: Delete <code>install.php</code> from your server for security!</p>
                </div>
                
                <div class="btn-group" style="margin-top:24px;justify-content:center;">
                    <a href="#" id="btn-go-admin" class="btn btn-primary" style="text-decoration:none;">
                        Open Admin Panel &rarr;
                    </a>
                </div>
            </div>
        </div>

    </div><!-- /.installer-card -->
</div><!-- /.installer-container -->


<script>
// ─── STATE ────────────────────────────────────────────────────────
let currentStep = 1;
let requirementsPassed = false;
let dbTested = false;
let installData = {};

// ─── INITIALIZATION ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    checkRequirements();
});

// ─── STEP NAVIGATION ─────────────────────────────────────────────
function goToStep(step) {
    if (step === 2 && !requirementsPassed) return;
    
    // Hide current
    document.getElementById('step-' + currentStep).classList.remove('active');
    // Show target
    document.getElementById('step-' + step).classList.add('active');
    
    // Update dots
    document.querySelectorAll('.step-dot').forEach(dot => {
        const s = parseInt(dot.dataset.step);
        dot.classList.remove('active', 'completed');
        if (s < step) dot.classList.add('completed');
        else if (s === step) dot.classList.add('active');
    });
    
    // Update lines
    document.querySelectorAll('.step-line').forEach(line => {
        const l = parseInt(line.dataset.line);
        line.classList.toggle('completed', l < step);
    });
    
    currentStep = step;
}

// ─── STEP 1: REQUIREMENTS CHECK ──────────────────────────────────
async function checkRequirements() {
    try {
        const res = await fetch('?action=check_requirements');
        const data = await res.json();
        
        document.getElementById('req-loading').style.display = 'none';
        document.getElementById('req-results').style.display = 'block';
        
        // Server info
        const info = data.server_info;
        const infoGrid = document.getElementById('server-info-grid');
        infoGrid.innerHTML = `
            <div class="server-info-item">PHP: <span>${info.php_version}</span></div>
            <div class="server-info-item">OS: <span>${info.os}</span></div>
            <div class="server-info-item">Server: <span>${info.server_software}</span></div>
            <div class="server-info-item">Upload: <span>${info.max_upload}</span></div>
            <div class="server-info-item">Memory: <span>${info.memory_limit}</span></div>
            <div class="server-info-item">Post Max: <span>${info.max_post}</span></div>
        `;
        
        // Requirements table
        const tbody = document.querySelector('#req-table tbody');
        tbody.innerHTML = '';
        data.results.forEach(item => {
            const tr = document.createElement('tr');
            const statusClass = item.passed ? 'req-pass' : (item.critical ? 'req-fail' : 'req-warn');
            const statusIcon = item.passed ? '&#10003;' : (item.critical ? '&#10007;' : '&#9888;');
            tr.innerHTML = `
                <td>${item.name}</td>
                <td>${item.current}</td>
                <td class="${statusClass}">${statusIcon} ${item.passed ? 'OK' : 'FAIL'}</td>
            `;
            tbody.appendChild(tr);
        });
        
        // Summary
        const summary = document.getElementById('req-summary');
        if (data.all_passed) {
            summary.style.background = 'rgba(16,185,129,0.08)';
            summary.style.border = '1px solid rgba(16,185,129,0.3)';
            summary.style.color = '#10b981';
            summary.innerHTML = '&#10003; All critical requirements passed! You can proceed.';
            requirementsPassed = true;
            document.getElementById('btn-step1-next').disabled = false;
        } else {
            summary.style.background = 'rgba(239,68,68,0.08)';
            summary.style.border = '1px solid rgba(239,68,68,0.3)';
            summary.style.color = '#ef4444';
            summary.innerHTML = '&#10007; Some critical requirements failed. Please fix them before continuing.';
            requirementsPassed = false;
            document.getElementById('btn-step1-next').disabled = true;
        }
        
    } catch (err) {
        document.getElementById('req-loading').innerHTML = `
            <p style="color:var(--error);">Failed to check requirements: ${err.message}</p>
            <button class="btn btn-test" onclick="checkRequirements()" style="margin-top:12px;">Retry</button>
        `;
    }
}

// ─── STEP 2: TEST DATABASE CONNECTION ─────────────────────────────
async function testConnection() {
    const btn = document.getElementById('btn-test-db');
    const result = document.getElementById('db-test-result');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Testing...';
    
    const payload = {
        host: document.getElementById('db_host').value,
        name: document.getElementById('db_name').value,
        user: document.getElementById('db_user').value,
        pass: document.getElementById('db_pass').value
    };
    
    try {
        const res = await fetch('?action=test_db', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        
        result.classList.remove('success', 'error');
        result.classList.add('show');
        
        if (data.success) {
            result.classList.add('success');
            result.innerHTML = `&#10003; ${data.message}<br><small>MySQL ${data.mysql_version} &mdash; ${data.db_message}</small>`;
            dbTested = true;
        } else {
            result.classList.add('error');
            result.innerHTML = `&#10007; ${data.message}`;
            dbTested = false;
        }
    } catch (err) {
        result.classList.remove('success');
        result.classList.add('show', 'error');
        result.innerHTML = `&#10007; Network error: ${err.message}`;
    }
    
    btn.disabled = false;
    btn.innerHTML = '&#9889; Test Connection';
}

// ─── STEP 3 → 4: START INSTALLATION ──────────────────────────────
async function startInstallation() {
    // Gather all data
    installData = {
        db_host: document.getElementById('db_host').value,
        db_name: document.getElementById('db_name').value,
        db_user: document.getElementById('db_user').value,
        db_pass: document.getElementById('db_pass').value,
        app_url: document.getElementById('app_url').value.replace(/\/+$/, ''),
        app_name: document.getElementById('app_name').value,
        app_lang: document.getElementById('app_lang').value,
        owner_name: document.getElementById('owner_name').value,
        owner_email: document.getElementById('owner_email').value,
        whatsapp: document.getElementById('whatsapp').value,
        admin_user: document.getElementById('admin_user').value,
        admin_pass: document.getElementById('admin_pass').value
    };
    
    // Validate required fields
    if (!installData.db_host || !installData.db_name || !installData.db_user) {
        alert('Please fill in all database fields.');
        return;
    }
    if (!installData.app_url || !installData.admin_user || !installData.admin_pass) {
        alert('Please fill in website URL, admin username and password.');
        return;
    }
    
    // Go to step 4
    goToStep(4);
    
    // Reset progress items
    document.querySelectorAll('.progress-item').forEach(item => {
        item.className = 'progress-item pending';
        item.querySelector('.progress-icon').innerHTML = '&#9679;';
    });
    document.getElementById('install-error').style.display = 'none';
    
    // Animate progress simulation then perform actual install
    await performInstall();
}

async function performInstall() {
    const tasks = ['db_connect', 'db_create', 'db_import', 'admin_setup', 'settings', 'env_file', 'directories', 'finalize'];
    
    // Set first as running
    setTaskStatus('db_connect', 'running');
    
    try {
        const res = await fetch('?action=install', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(installData)
        });
        const data = await res.json();
        
        if (data.success) {
            // Animate each completed step with delay
            for (let i = 0; i < data.steps.length; i++) {
                const step = data.steps[i];
                await delay(350);
                setTaskStatus(step.step, step.status === 'done' ? 'done' : 'fail');
                // Set next as running
                if (i + 1 < data.steps.length) {
                    setTaskStatus(data.steps[i + 1].step, 'running');
                }
            }
            
            await delay(500);
            
            // Show success
            document.getElementById('cred-url').textContent = data.admin_url;
            document.getElementById('cred-user').textContent = data.admin_user;
            document.getElementById('cred-pass').textContent = data.admin_pass;
            document.getElementById('btn-go-admin').href = data.admin_url;
            
            goToStep(5);
        } else {
            // Show progress up to failure
            if (data.steps) {
                for (let i = 0; i < data.steps.length; i++) {
                    const step = data.steps[i];
                    await delay(300);
                    setTaskStatus(step.step, step.status === 'done' ? 'done' : 'fail');
                }
            }
            
            // Show error
            document.getElementById('install-error').style.display = 'block';
            document.getElementById('install-error-msg').innerHTML = '&#10007; Installation failed: ' + (data.error || 'Unknown error');
            
            if (data.steps) {
                const failedSteps = data.steps.filter(s => s.failed_statements && s.failed_statements.length > 0);
                if (failedSteps.length > 0) {
                    document.getElementById('install-error-detail').style.display = 'block';
                    let details = '';
                    failedSteps.forEach(fs => {
                        fs.failed_statements.forEach(f => {
                            details += `Statement: ${f.statement}\nError: ${f.error}\n\n`;
                        });
                    });
                    document.getElementById('install-error-pre').textContent = details;
                }
            }
        }
    } catch (err) {
        setTaskStatus('db_connect', 'fail');
        document.getElementById('install-error').style.display = 'block';
        document.getElementById('install-error-msg').innerHTML = '&#10007; Network error: ' + err.message;
    }
}

function retryInstallation() {
    document.getElementById('install-error').style.display = 'none';
    document.getElementById('install-error-detail').style.display = 'none';
    performInstall();
}

function setTaskStatus(task, status) {
    const item = document.querySelector(`.progress-item[data-task="${task}"]`);
    if (!item) return;
    
    item.className = 'progress-item ' + status;
    const icon = item.querySelector('.progress-icon');
    
    switch (status) {
        case 'running':
            icon.innerHTML = '&#9679;';
            break;
        case 'done':
            icon.innerHTML = '&#10003;';
            break;
        case 'fail':
            icon.innerHTML = '&#10007;';
            break;
        default:
            icon.innerHTML = '&#9679;';
    }
}

function delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}
</script>
</body>
</html>
