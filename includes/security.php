<?php
/**
 * Security Functions
 * CSRF protection, XSS filtering, rate limiting
 */

/**
 * Generate CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get CSRF hidden input field
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCsrfToken() . '">';
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token = null) {
    $token = $token ?? ($_POST['csrf_token'] ?? '');
    
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        return false;
    }
    
    // Check token expiry
    if (time() - ($_SESSION['csrf_token_time'] ?? 0) > CSRF_TOKEN_LIFETIME) {
        return false;
    }
    
    return true;
}

/**
 * XSS Clean - sanitize output
 */
function xssClean($data) {
    if (is_array($data)) {
        return array_map('xssClean', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Sanitize HTML content (for rich text editor output)
 */
function sanitizeHtml($html) {
    // Allow basic HTML tags
    $allowed = '<h1><h2><h3><h4><h5><h6><p><br><strong><b><em><i><u><s><a><ul><ol><li><blockquote><pre><code><img><table><thead><tbody><tr><th><td><hr><div><span>';
    $html = strip_tags($html, $allowed);
    
    // Remove on* event handlers
    $html = preg_replace('/\bon\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
    $html = preg_replace('/\bon\w+\s*=\s*\S+/i', '', $html);
    
    // Remove javascript: protocol
    $html = preg_replace('/javascript\s*:/i', '', $html);
    
    return $html;
}

/**
 * Rate limiter
 */
function checkRateLimit($action, $maxAttempts = null, $lockoutTime = null) {
    $maxAttempts = $maxAttempts ?? LOGIN_MAX_ATTEMPTS;
    $lockoutTime = $lockoutTime ?? LOGIN_LOCKOUT_TIME;
    
    $key = 'rate_limit_' . $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 0,
            'first_attempt' => time(),
            'locked_until' => 0
        ];
    }
    
    $limiter = &$_SESSION[$key];
    
    // Check if locked
    if ($limiter['locked_until'] > time()) {
        $remaining = $limiter['locked_until'] - time();
        return [
            'allowed' => false,
            'remaining_time' => $remaining,
            'message' => "Too many attempts. Please try again in " . ceil($remaining / 60) . " minutes."
        ];
    }
    
    // Reset if window expired
    if (time() - $limiter['first_attempt'] > $lockoutTime) {
        $limiter['attempts'] = 0;
        $limiter['first_attempt'] = time();
    }
    
    return [
        'allowed' => true,
        'attempts' => $limiter['attempts'],
        'max_attempts' => $maxAttempts
    ];
}

/**
 * Record a rate limit attempt
 */
function recordRateLimitAttempt($action) {
    $key = 'rate_limit_' . $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = [
            'attempts' => 0,
            'first_attempt' => time(),
            'locked_until' => 0
        ];
    }
    
    $_SESSION[$key]['attempts']++;
    
    if ($_SESSION[$key]['attempts'] >= LOGIN_MAX_ATTEMPTS) {
        $_SESSION[$key]['locked_until'] = time() + LOGIN_LOCKOUT_TIME;
    }
}

/**
 * Reset rate limit
 */
function resetRateLimit($action) {
    $key = 'rate_limit_' . $action . '_' . ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    unset($_SESSION[$key]);
}

/**
 * Verify Google reCAPTCHA
 */
function verifyRecaptcha($response) {
    if (empty(config('recaptcha_secret_key'))) return true; // Skip if not configured
    
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = [
        'secret' => config('recaptcha_secret_key'),
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === false) return false;
    
    $json = json_decode($result, true);
    return $json['success'] ?? false;
}

/**
 * Validate email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate URL
 */
function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Secure headers
 */
function setSecureHeaders() {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    if (APP_ENV === 'production') {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}

// Apply secure headers
setSecureHeaders();
