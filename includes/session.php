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

/**
 * Login user via Google OAuth
 * Finds existing user by email or creates a new admin account
 */
function loginWithGoogle($email, $name, $avatar = '') {
    // Find existing user by email
    $user = db()->fetch(
        "SELECT * FROM users WHERE email = ? AND is_active = 1",
        [$email]
    );
    
    if (!$user) {
        // Check if allowed emails is empty (meaning we should auto-create)
        $allowedEmails = config('google_allowed_emails', '');
        
        if (!empty($allowedEmails)) {
            // Whitelist mode: user must already exist in DB or be in the allowed list
            // Try to create a new user for this email
            try {
                $username = strtolower(explode('@', $email)[0]);
                // Make username unique
                $existingUsername = db()->fetch("SELECT id FROM users WHERE username = ?", [$username]);
                if ($existingUsername) {
                    $username .= '_' . rand(100, 999);
                }
                
                $userId = db()->insert('users', [
                    'username' => $username,
                    'email' => $email,
                    'password' => hashPassword(bin2hex(random_bytes(16))), // Random password (won't be used)
                    'full_name' => $name,
                    'avatar' => null,
                    'role' => 'admin',
                    'is_active' => 1,
                ]);
                
                $user = db()->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            } catch (Exception $e) {
                return ['success' => false, 'message' => 'Failed to create account: ' . $e->getMessage()];
            }
        } else {
            // No whitelist: only allow login if email matches an existing user
            return ['success' => false, 'message' => 'No admin account found for ' . htmlspecialchars($email) . '. Please contact the administrator.'];
        }
    }
    
    if (!$user) {
        return ['success' => false, 'message' => 'Login failed. Account not found.'];
    }
    
    // Update last login
    db()->update('users', [
        'last_login' => date('Y-m-d H:i:s'),
        'login_attempts' => 0,
        'locked_until' => null,
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
    $_SESSION['login_method'] = 'google';
    
    return ['success' => true, 'message' => 'Login successful!'];
}
