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
    
    $username = post('username');
    $password = $_POST['password'] ?? '';
    
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

            <div class="login-footer">
                <a href="<?= baseUrl() ?>"><i class="fas fa-arrow-left"></i> Back to Website</a>
            </div>
        </div>
    </div>
</body>
</html>
