<?php
/**
 * Database Installer
 * Run this file once to set up the database
 * DELETE THIS FILE AFTER INSTALLATION
 */

// Simple security - prevent running if already installed
$lockFile = __DIR__ . '/cache/.installed';
if (file_exists($lockFile)) {
    die('<h1>Already Installed</h1><p>The application is already installed. Delete <code>cache/.installed</code> to reinstall.</p>');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Installer</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, sans-serif; background: #0a0a0f; color: #fff; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .installer { max-width: 600px; width: 100%; background: #12121a; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 40px; }
        h1 { font-size: 1.8rem; margin-bottom: 8px; }
        h1 span { color: #0066ff; }
        .subtitle { color: #a0a0b0; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; color: #a0a0b0; margin-bottom: 6px; }
        input { width: 100%; padding: 12px 16px; background: #1a1a2e; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; font-size: 0.9rem; }
        input:focus { outline: none; border-color: #0066ff; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .btn { width: 100%; padding: 14px; background: linear-gradient(135deg, #0066ff, #0044cc); color: #fff; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 10px; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(0,102,255,0.3); }
        .message { padding: 14px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; }
        .success { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; }
        .error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; }
        .steps { margin: 20px 0; }
        .step { padding: 8px 0; display: flex; align-items: center; gap: 10px; font-size: 0.9rem; color: #a0a0b0; }
        .step .icon { width: 20px; text-align: center; }
        .step.done { color: #10b981; }
        .step.fail { color: #ef4444; }
        .info { background: rgba(0,102,255,0.1); border: 1px solid rgba(0,102,255,0.2); border-radius: 8px; padding: 14px; margin-top: 20px; font-size: 0.85rem; color: #a0a0b0; }
        .info strong { color: #0066ff; }
    </style>
</head>
<body>
<div class="installer">
    <h1>&lt;<span>Portfolio</span>/&gt; Installer</h1>
    <p class="subtitle">Set up your portfolio website database</p>

    <?php
    $installed = false;
    $error = '';
    $steps = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $host = $_POST['db_host'] ?? 'localhost';
        $name = $_POST['db_name'] ?? 'portfolio_db';
        $user = $_POST['db_user'] ?? 'root';
        $pass = $_POST['db_pass'] ?? '';
        $appUrl = rtrim($_POST['app_url'] ?? 'http://localhost', '/');

        try {
            // Step 1: Connect to MySQL
            $dsn = "mysql:host=$host;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $steps[] = ['done', 'Connected to MySQL server'];

            // Step 2: Create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$name`");
            $steps[] = ['done', "Database '$name' created/selected"];

            // Step 3: Import SQL
            $sqlFile = __DIR__ . '/database/portfolio_db.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception("SQL file not found at: $sqlFile");
            }
            
            $sql = file_get_contents($sqlFile);
            // Remove the CREATE DATABASE and USE statements (we already did that)
            $sql = preg_replace('/CREATE DATABASE.*?;/s', '', $sql);
            $sql = preg_replace('/USE\s+`.*?`;/s', '', $sql);
            
            $pdo->exec($sql);
            $steps[] = ['done', 'Database tables and data imported'];

            // Step 4: Update .env file
            $envFile = __DIR__ . '/.env';
            $envContent = file_get_contents($envFile);
            $envContent = preg_replace('/DB_HOST=.*/', "DB_HOST=$host", $envContent);
            $envContent = preg_replace('/DB_NAME=.*/', "DB_NAME=$name", $envContent);
            $envContent = preg_replace('/DB_USER=.*/', "DB_USER=$user", $envContent);
            $envContent = preg_replace('/DB_PASS=.*/', "DB_PASS=$pass", $envContent);
            $envContent = preg_replace('/APP_URL=.*/', "APP_URL=$appUrl", $envContent);
            file_put_contents($envFile, $envContent);
            $steps[] = ['done', '.env configuration updated'];

            // Step 5: Create directories
            $dirs = ['uploads/images', 'uploads/projects', 'uploads/blog', 'uploads/testimonials', 'uploads/settings', 'cache'];
            foreach ($dirs as $dir) {
                $path = __DIR__ . '/' . $dir;
                if (!is_dir($path)) mkdir($path, 0755, true);
            }
            $steps[] = ['done', 'Upload directories created'];

            // Step 6: Create lock file
            file_put_contents($lockFile, date('Y-m-d H:i:s'));
            $steps[] = ['done', 'Installation locked'];

            $installed = true;

        } catch (PDOException $e) {
            $error = 'Database Error: ' . $e->getMessage();
            $steps[] = ['fail', $error];
        } catch (Exception $e) {
            $error = $e->getMessage();
            $steps[] = ['fail', $error];
        }
    }
    ?>

    <?php if ($installed): ?>
        <div class="message success">Installation completed successfully!</div>
        <div class="steps">
            <?php foreach ($steps as $step): ?>
            <div class="step done"><span class="icon">&#10003;</span> <?= $step[1] ?></div>
            <?php endforeach; ?>
        </div>
        <div class="info">
            <p><strong>Admin Login:</strong></p>
            <p>URL: <strong><?= htmlspecialchars($_POST['app_url'] ?? '') ?>/admin/login</strong></p>
            <p>Username: <strong>admin</strong></p>
            <p>Password: <strong>admin123</strong></p>
            <br>
            <p><strong>IMPORTANT:</strong> Please change your admin password after first login and DELETE this install.php file!</p>
        </div>
    <?php elseif ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
        <div class="steps">
            <?php foreach ($steps as $step): ?>
            <div class="step <?= $step[0] ?>"><span class="icon"><?= $step[0] === 'done' ? '&#10003;' : '&#10007;' ?></span> <?= $step[1] ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!$installed): ?>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Database Host</label>
                <input type="text" name="db_host" value="localhost" required>
            </div>
            <div class="form-group">
                <label>Database Name</label>
                <input type="text" name="db_name" value="portfolio_db" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Database Username</label>
                <input type="text" name="db_user" value="root" required>
            </div>
            <div class="form-group">
                <label>Database Password</label>
                <input type="password" name="db_pass" value="">
            </div>
        </div>
        <div class="form-group">
            <label>Website URL (no trailing slash)</label>
            <input type="url" name="app_url" value="http://localhost" placeholder="https://yourdomain.com" required>
        </div>
        <button type="submit" class="btn">Install Now</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
