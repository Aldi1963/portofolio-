<?php
/**
 * Session & Authentication Management
 */

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current logged in user
 */
function currentUser() {
    if (!isLoggedIn()) return null;
    
    static $user = null;
    if ($user === null) {
        $user = db()->fetch("SELECT * FROM users WHERE id = ? AND is_active = 1", [$_SESSION['user_id']]);
    }
    return $user;
}

/**
 * Login user
 */
function loginUser($username, $password) {
    // Check rate limit
    $rateLimit = checkRateLimit('login');
    if (!$rateLimit['allowed']) {
        return ['success' => false, 'message' => $rateLimit['message']];
    }
    
    // Find user
    $user = db()->fetch(
        "SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1",
        [$username, $username]
    );
    
    if (!$user) {
        recordRateLimitAttempt('login');
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }
    
    // Check if account is locked
    if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
        return ['success' => false, 'message' => 'Account is temporarily locked. Please try again later.'];
    }
    
    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        // Increment login attempts
        $attempts = $user['login_attempts'] + 1;
        $lockedUntil = null;
        
        if ($attempts >= LOGIN_MAX_ATTEMPTS) {
            $lockedUntil = date('Y-m-d H:i:s', time() + LOGIN_LOCKOUT_TIME);
        }
        
        db()->update('users', [
            'login_attempts' => $attempts,
            'locked_until' => $lockedUntil
        ], 'id = ?', [$user['id']]);
        
        recordRateLimitAttempt('login');
        return ['success' => false, 'message' => 'Invalid username or password.'];
    }
    
    // Successful login
    // Reset login attempts
    db()->update('users', [
        'login_attempts' => 0,
        'locked_until' => null,
        'last_login' => date('Y-m-d H:i:s')
    ], 'id = ?', [$user['id']]);
    
    // Regenerate session ID
    session_regenerate_id(true);
    
    // Set session data
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
    
    // Reset rate limit
    resetRateLimit('login');
    
    return ['success' => true, 'message' => 'Login successful!'];
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = [];
    
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    
    session_destroy();
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isLoggedIn()) {
        if (time() - ($_SESSION['login_time'] ?? 0) > SESSION_LIFETIME) {
            logoutUser();
            session_start();
            setFlash('warning', 'Your session has expired. Please login again.');
            redirect(baseUrl('admin/login'));
        }
        // Refresh session time on activity
        $_SESSION['login_time'] = time();
    }
}

// Check session timeout on every request
checkSessionTimeout();

/**
 * Require authentication (use in admin pages)
 */
function requireAuth() {
    if (!isLoggedIn()) {
        setFlash('error', 'Please login to access this page.');
        redirect(baseUrl('admin/login'));
    }
}

/**
 * Check if user has role
 */
function hasRole($role) {
    return isLoggedIn() && ($_SESSION['user_role'] ?? '') === $role;
}
