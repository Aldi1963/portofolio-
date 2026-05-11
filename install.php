<?php
/**
 * Web-Based Installer
 * Fill in database & site config via browser - no file editing needed!
 * DELETE THIS FILE AFTER INSTALLATION
 */

// Check if already installed
$lockFile = __DIR__ . '/cache/.installed';
if (file_exists($lockFile)) {
    die('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Already Installed</title><style>body{background:#0a0a0f;color:#fff;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}.card{background:#12121a;border:1px solid rgba(255,255,255,0.1);border-radius:16px;padding:40px;text-align:center;max-width:400px;}h1{color:#0066ff;margin-bottom:12px;}p{color:#a0a0b0;margin-bottom:20px;}a{color:#0066ff;}</style></head><body><div class="card"><h1>Already Installed</h1><p>Delete <code>cache/.installed</code> to reinstall.</p><a href="/">Go to Website</a></div></body></html>');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Installer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:#0a0a0f;color:#fff;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .installer{max-width:650px;width:100%;background:#12121a;border:1px solid rgba(255,255,255,0.1);border-radius:16px;padding:40px}
        h1{font-size:1.8rem;margin-bottom:4px}h1 span{color:#0066ff}
        .subtitle{color:#a0a0b0;margin-bottom:30px;font-size:0.9rem}
        .section-title{font-size:0.78rem;text-transform:uppercase;letter-spacing:1.5px;color:#0066ff;font-weight:600;margin:24px 0 12px;padding-bottom:8px;border-bottom:1px solid rgba(255,255,255,0.05)}
        .form-group{margin-bottom:16px}
        label{display:block;font-size:0.82rem;font-weight:600;color:#a0a0b0;margin-bottom:5px}
        input,select{width:100%;padding:11px 14px;background:#1a1a2e;border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;font-size:0.9rem;font-family:inherit}
        input:focus,select:focus{outline:none;border-color:#0066ff;box-shadow:0 0 0 3px rgba(0,102,255,0.1)}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        .form-help{font-size:0.73rem;color:#6b6b80;margin-top:4px}
        .btn{width:100%;padding:14px;background:linear-gradient(135deg,#0066ff,#0044cc);color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer;margin-top:20px;transition:all 0.3s}
        .btn:hover{transform:translateY(-2px);box-shadow:0 5px 20px rgba(0,102,255,0.3)}
        .message{padding:14px 18px;border-radius:8px;margin-bottom:20px;font-size:0.88rem;line-height:1.5}
        .success{background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);color:#10b981}
        .error{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444}
        .steps{margin:20px 0}.step{padding:8px 0;display:flex;align-items:center;gap:10px;font-size:0.88rem;color:#a0a0b0}
        .step.done{color:#10b981}.step.fail{color:#ef4444}
        .info-box{background:rgba(0,102,255,0.05);border:1px solid rgba(0,102,255,0.15);border-radius:8px;padding:16px;margin-top:20px;font-size:0.85rem;color:#a0a0b0;line-height:1.7}
        .info-box strong{color:#0066ff;display:block;margin-bottom:6px}
        .info-box code{background:#1a1a2e;padding:2px 6px;border-radius:4px;font-size:0.82rem}
        .logo{text-align:center;margin-bottom:24px}.logo span{font-size:1.5rem;font-weight:700}.logo .hl{color:#0066ff}
        @media(max-width:600px){.form-row{grid-template-columns:1fr}.installer{padding:24px}}
    </style>
</head>
<body>
<div class="installer">
    <div class="logo"><span>&lt;<span class="hl">Portfolio</span>/&gt;</span></div>
    <h1>Web <span>Installer</span></h1>
    <p class="subtitle">Isi form di bawah, klik Install. Tanpa perlu edit file apapun!</p>

    <?php
    $installed = false;
    $error = '';
    $steps = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $host = trim($_POST['db_host'] ?? 'localhost');
        $name = trim($_POST['db_name'] ?? 'portfolio_db');
        $user = trim($_POST['db_user'] ?? 'root');
        $pass = $_POST['db_pass'] ?? '';
        $appUrl = rtrim(trim($_POST['app_url'] ?? ''), '/');
        $appName = trim($_POST['app_name'] ?? 'MyPortfolio');
        $appLang = $_POST['app_lang'] ?? 'id';
        $ownerName = trim($_POST['owner_name'] ?? '');
        $ownerEmail = trim($_POST['owner_email'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $adminUser = trim($_POST['admin_user'] ?? 'admin');
        $adminPass = $_POST['admin_pass'] ?? 'admin123';

        try {
            // Step 1: Connect to MySQL
            $dsn = "mysql:host=$host;charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $steps[] = ['done', 'Connected to MySQL server'];

            // Step 2: Create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$name`");
            $steps[] = ['done', "Database '$name' ready"];

            // Step 3: Import SQL (statement by statement)
            $sqlFile = __DIR__ . '/database/portfolio_db.sql';
            if (!file_exists($sqlFile)) throw new Exception("SQL file not found at: $sqlFile");
            
            $sql = file_get_contents($sqlFile);
            // Remove database/use statements
            $sql = preg_replace('/CREATE DATABASE.*?;/s', '', $sql);
            $sql = preg_replace('/USE\s+`.*?`;/s', '', $sql);
            // Remove SQL comments
            $sql = preg_replace('/^--.*$/m', '', $sql);
            // Prevent duplicate entry errors
            $sql = str_replace('INSERT INTO', 'INSERT IGNORE INTO', $sql);
            // Remove transaction commands (we handle our own)
            $sql = str_replace(['START TRANSACTION;', 'COMMIT;', 'SET AUTOCOMMIT = 0;'], '', $sql);
            
            // Split into individual statements and execute
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($statements as $stmt) {
                if (!empty($stmt) && strlen($stmt) > 5) {
                    $pdo->exec($stmt);
                }
            }
            $steps[] = ['done', 'Database tables & data imported'];

            // Step 4: Set admin credentials
            $hashedPass = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, full_name = ?, email = ? WHERE id = 1");
            $stmt->execute([$adminUser, $hashedPass, $ownerName ?: 'Admin', $ownerEmail ?: 'admin@portfolio.com']);
            $steps[] = ['done', 'Admin account configured'];

            // Step 5: Update settings in database
            $updates = [
                'site_name' => $appName,
                'owner_name' => $ownerName ?: 'Admin',
                'owner_email' => $ownerEmail,
                'whatsapp_number' => $whatsapp,
            ];
            $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            foreach ($updates as $key => $val) {
                if (!empty($val)) $stmt->execute([$val, $key]);
            }
            $steps[] = ['done', 'Site settings saved'];

            // Step 6: Write .env
            $env = "# Generated by installer - " . date('Y-m-d H:i:s') . "\n";
            $env .= "DB_HOST={$host}\nDB_NAME={$name}\nDB_USER={$user}\nDB_PASS={$pass}\n\n";
            $env .= "APP_NAME={$appName}\nAPP_URL={$appUrl}\nAPP_ENV=production\nAPP_DEBUG=false\nAPP_LANG={$appLang}\n\n";
            $env .= "SESSION_LIFETIME=3600\nCSRF_TOKEN_LIFETIME=3600\nLOGIN_MAX_ATTEMPTS=5\nLOGIN_LOCKOUT_TIME=900\n";
            file_put_contents(__DIR__ . '/.env', $env);
            $steps[] = ['done', '.env created automatically'];

            // Step 7: Create directories
            foreach (['uploads/images','uploads/projects','uploads/blog','uploads/testimonials','uploads/settings','cache'] as $dir) {
                $path = __DIR__ . '/' . $dir;
                if (!is_dir($path)) mkdir($path, 0755, true);
            }
            $steps[] = ['done', 'Directories created'];

            // Step 8: Lock
            file_put_contents($lockFile, date('Y-m-d H:i:s'));
            $steps[] = ['done', 'Installation complete & locked'];

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
        <div class="message success">&#10003; Installation completed successfully!</div>
        <div class="steps">
            <?php foreach ($steps as $s): ?>
            <div class="step done"><span>&#10003;</span> <?= htmlspecialchars($s[1]) ?></div>
            <?php endforeach; ?>
        </div>
        <div class="info-box">
            <strong>Admin Login:</strong>
            URL: <code><?= htmlspecialchars($_POST['app_url'] ?? '') ?>/admin/login</code><br>
            Username: <code><?= htmlspecialchars($adminUser) ?></code><br>
            Password: <code><?= htmlspecialchars($adminPass) ?></code><br><br>
            <strong>Semua konfigurasi lain (SMTP, reCAPTCHA, Analytics, dll) bisa diatur dari:</strong><br>
            Admin Panel &rarr; Settings &rarr; tab Email/SMTP & Integrations<br><br>
            <span style="color:#ef4444;font-weight:600;">HAPUS FILE install.php INI SETELAH SELESAI!</span>
        </div>
        <a href="<?= htmlspecialchars($_POST['app_url'] ?? '/') ?>/admin/login" style="display:block;text-align:center;margin-top:16px;padding:12px;background:#0066ff;color:#fff;border-radius:8px;font-weight:600;text-decoration:none;">Open Admin Panel &rarr;</a>
    
    <?php elseif ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!$installed): ?>
    <form method="POST" autocomplete="off">
        
        <div class="section-title">&#128451; Database</div>
        <div class="form-row">
            <div class="form-group">
                <label>DB Host</label>
                <input type="text" name="db_host" value="localhost" required>
            </div>
            <div class="form-group">
                <label>DB Name</label>
                <input type="text" name="db_name" value="portfolio_db" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>DB Username</label>
                <input type="text" name="db_user" value="root" required>
            </div>
            <div class="form-group">
                <label>DB Password</label>
                <input type="password" name="db_pass">
                <div class="form-help">Kosongkan jika tidak ada password</div>
            </div>
        </div>

        <div class="section-title">&#127760; Website</div>
        <div class="form-group">
            <label>Website URL (tanpa / di akhir)</label>
            <input type="url" name="app_url" value="http://<?= $_SERVER['HTTP_HOST'] ?? 'localhost' ?><?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?>" required>
            <div class="form-help">Contoh: http://159.203.179.169 atau http://domain.com/portfolio</div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Nama Website</label>
                <input type="text" name="app_name" value="MyPortfolio" required>
            </div>
            <div class="form-group">
                <label>Bahasa</label>
                <select name="app_lang">
                    <option value="id">Bahasa Indonesia</option>
                    <option value="en">English</option>
                </select>
            </div>
        </div>

        <div class="section-title">&#128100; Profil Anda</div>
        <div class="form-row">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="owner_name" placeholder="Aldi">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="owner_email" placeholder="aldi@email.com">
            </div>
        </div>
        <div class="form-group">
            <label>Nomor WhatsApp (kode negara, tanpa + atau spasi)</label>
            <input type="text" name="whatsapp" placeholder="6281234567890">
        </div>

        <div class="section-title">&#128274; Admin Login</div>
        <div class="form-row">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="admin_user" value="admin" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="admin_pass" value="admin123" required>
                <div class="form-help">Gunakan password yang kuat!</div>
            </div>
        </div>

        <button type="submit" class="btn">&#128640; Install Sekarang</button>
        
        <div class="info-box" style="margin-top:20px">
            <strong>Catatan:</strong>
            Pengaturan SMTP Email, Google Analytics, reCAPTCHA, dan integrasi lainnya bisa diatur setelah install dari <strong>Admin Panel &rarr; Settings</strong>. Tidak perlu diisi sekarang.
        </div>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
