<?php
/**
 * Application Configuration
 * Loads environment variables and defines application constants
 */

// Load .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
    return true;
}

// Load environment
loadEnv(__DIR__ . '/../.env');

// Helper function to get env values
function env($key, $default = '') {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// Application Constants
define('APP_NAME', env('APP_NAME', 'MyPortfolio'));
define('APP_URL', env('APP_URL', 'http://localhost'));
define('APP_ENV', env('APP_ENV', 'production'));
define('APP_DEBUG', env('APP_DEBUG', 'false') === 'true');
define('APP_LANG', env('APP_LANG', 'id'));
define('APP_VERSION', '1.0.0');

// Database Constants
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'portfolio_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

// Mail Constants (loaded from DB first, fallback to .env)
define('MAIL_HOST', env('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', (int)env('MAIL_PORT', '587'));
define('MAIL_USERNAME', env('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', env('MAIL_PASSWORD', ''));
define('MAIL_FROM', env('MAIL_FROM', ''));
define('MAIL_FROM_NAME', env('MAIL_FROM_NAME', APP_NAME));

// Security Constants
define('SESSION_LIFETIME', (int)env('SESSION_LIFETIME', '3600'));
define('CSRF_TOKEN_LIFETIME', (int)env('CSRF_TOKEN_LIFETIME', '3600'));
define('LOGIN_MAX_ATTEMPTS', (int)env('LOGIN_MAX_ATTEMPTS', '5'));
define('LOGIN_LOCKOUT_TIME', (int)env('LOGIN_LOCKOUT_TIME', '900'));

// reCAPTCHA
define('RECAPTCHA_SITE_KEY', env('RECAPTCHA_SITE_KEY', ''));
define('RECAPTCHA_SECRET_KEY', env('RECAPTCHA_SECRET_KEY', ''));

// Google Analytics
define('GA_TRACKING_ID', env('GA_TRACKING_ID', ''));

// WhatsApp
define('WHATSAPP_NUMBER', env('WHATSAPP_NUMBER', ''));

/**
 * Override constants with database settings (dynamic config)
 * Called after database is available
 */
function loadDynamicConfig() {
    try {
        $dbSettings = db()->fetchAll("SELECT setting_key, setting_value FROM settings WHERE setting_group IN ('mail', 'integration', 'security')");
        foreach ($dbSettings as $row) {
            $key = $row['setting_key'];
            $val = $row['setting_value'];
            if ($val === '' || $val === null) continue; // Skip empty, use .env fallback
            
            // Store in a global array for runtime access
            $GLOBALS['_dynamic_config'][$key] = $val;
        }
    } catch (Exception $e) {
        // Database not available yet (e.g., during install), use .env values
    }
}

/**
 * Get dynamic config value (checks DB settings first, then constant)
 * Maps DB setting_key to PHP constant names
 */
function config($key, $default = '') {
    // Check dynamic config from DB first
    if (isset($GLOBALS['_dynamic_config'][$key])) {
        return $GLOBALS['_dynamic_config'][$key];
    }
    
    // Map DB keys to constant names
    $keyMap = [
        'mail_host' => 'MAIL_HOST',
        'mail_port' => 'MAIL_PORT',
        'mail_username' => 'MAIL_USERNAME',
        'mail_password' => 'MAIL_PASSWORD',
        'mail_from' => 'MAIL_FROM',
        'mail_from_name' => 'MAIL_FROM_NAME',
        'recaptcha_site_key' => 'RECAPTCHA_SITE_KEY',
        'recaptcha_secret_key' => 'RECAPTCHA_SECRET_KEY',
        'ga_tracking_id' => 'GA_TRACKING_ID',
        'whatsapp_number' => 'WHATSAPP_NUMBER',
        'session_lifetime' => 'SESSION_LIFETIME',
        'csrf_token_lifetime' => 'CSRF_TOKEN_LIFETIME',
        'login_max_attempts' => 'LOGIN_MAX_ATTEMPTS',
        'login_lockout_time' => 'LOGIN_LOCKOUT_TIME',
    ];
    
    // Try mapped constant
    if (isset($keyMap[$key]) && defined($keyMap[$key])) {
        return constant($keyMap[$key]);
    }
    
    // Try key directly as constant (uppercase)
    $constName = strtoupper($key);
    if (defined($constName)) {
        return constant($constName);
    }
    
    return $default;
}

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('API_PATH', ROOT_PATH . '/api');
define('CACHE_PATH', ROOT_PATH . '/cache');

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    // Only set secure cookie if actually using HTTPS
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
    ini_set('session.cookie_secure', $isHttps ? 1 : 0);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}
