<?php
/**
 * Admin Login Page
 */
if (isLoggedIn()) {
    redirect(baseUrl('admin/dashboard'));
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid security token. Please try again.');
        redirect(baseUrl('admin/login'));
    }
    
    // Get raw username (don't escape - it needs to match DB exactly)
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        setFlash('error', 'Username and password are required.');
        redirect(baseUrl('admin/login'));
    }
    
    $result = loginUser($username, $password);
    
    if ($result['success']) {
        setFlash('success', 'Welcome back, ' . ($_SESSION['full_name'] ?? 'Admin') . '!');
        redirect(baseUrl('admin/dashboard'));
    } else {
        setFlash('error', $result['message']);
        redirect(baseUrl('admin/login'));
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= getSetting('site_name', APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card glass">
            <div class="login-header">
                <a href="<?= baseUrl() ?>" class="login-brand">
                    &lt;<span class="brand-highlight"><?= getSetting('owner_name', 'Aldi') ?></span>/&gt;
                </a>
                <h1>Welcome Back</h1>
                <p>Sign in to your admin dashboard</p>
            </div>

            <?php if ($flash = getFlash()): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= $flash['message'] ?>
            </div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="<?= baseUrl('admin/login') ?>">
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Enter username or email" required autofocus>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter password" required>
                        <button type="button" class="toggle-password" tabindex="-1">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <?php if (config('google_oauth_enabled') === '1' && !empty(config('google_client_id'))): ?>
            <!-- Google Login Divider -->
            <div class="login-divider">
                <span>or</span>
            </div>

            <!-- Google Sign-In Button -->
            <a href="<?= baseUrl('admin/google-callback') ?>" class="btn-google">
                <svg class="google-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20">
                    <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                    <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                    <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                    <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                </svg>
                <span>Sign in with Google</span>
            </a>
            <?php endif; ?>

            <div class="login-footer">
                <a href="<?= baseUrl() ?>"><i class="fas fa-arrow-left"></i> Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>
