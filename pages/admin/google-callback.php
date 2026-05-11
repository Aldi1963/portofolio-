<?php
/**
 * Google OAuth Callback Handler
 * Handles the redirect from Google after user authorizes
 */

// Check if Google OAuth is enabled
$googleEnabled = config('google_oauth_enabled', '0');
if ($googleEnabled !== '1') {
    setFlash('error', 'Google Login is not enabled.');
    redirect(baseUrl('admin/login'));
}

$clientId = config('google_client_id');
$clientSecret = config('google_client_secret');

if (empty($clientId) || empty($clientSecret)) {
    setFlash('error', 'Google OAuth is not configured properly.');
    redirect(baseUrl('admin/login'));
}

// Check for error from Google
if (isset($_GET['error'])) {
    setFlash('error', 'Google login was cancelled or failed: ' . htmlspecialchars($_GET['error']));
    redirect(baseUrl('admin/login'));
}

// Check for authorization code
$code = $_GET['code'] ?? '';
if (empty($code)) {
    // Step 1: Redirect to Google for authorization
    $state = bin2hex(random_bytes(16));
    $_SESSION['google_oauth_state'] = $state;
    
    $redirectUri = rtrim(APP_URL, '/') . '/admin/google-callback';
    
    $params = http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'online',
        'state' => $state,
        'prompt' => 'select_account'
    ]);
    
    redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
}

// Step 2: Exchange authorization code for access token
// Verify state
$state = $_GET['state'] ?? '';
if (empty($state) || $state !== ($_SESSION['google_oauth_state'] ?? '')) {
    setFlash('error', 'Invalid OAuth state. Please try again.');
    redirect(baseUrl('admin/login'));
}
unset($_SESSION['google_oauth_state']);

$redirectUri = rtrim(APP_URL, '/') . '/admin/google-callback';

// Exchange code for token
$tokenUrl = 'https://oauth2.googleapis.com/token';
$tokenData = [
    'code' => $code,
    'client_id' => $clientId,
    'client_secret' => $clientSecret,
    'redirect_uri' => $redirectUri,
    'grant_type' => 'authorization_code'
];

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($tokenData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT => 30
]);
$tokenResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || empty($tokenResponse)) {
    setFlash('error', 'Failed to get access token from Google.');
    redirect(baseUrl('admin/login'));
}

$tokenJson = json_decode($tokenResponse, true);
$accessToken = $tokenJson['access_token'] ?? '';

if (empty($accessToken)) {
    setFlash('error', 'Invalid token response from Google.');
    redirect(baseUrl('admin/login'));
}

// Step 3: Get user info from Google
$userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo';
$ch = curl_init($userInfoUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
    CURLOPT_TIMEOUT => 30
]);
$userResponse = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || empty($userResponse)) {
    setFlash('error', 'Failed to get user info from Google.');
    redirect(baseUrl('admin/login'));
}

$googleUser = json_decode($userResponse, true);
$googleEmail = $googleUser['email'] ?? '';
$googleName = $googleUser['name'] ?? '';
$googleAvatar = $googleUser['picture'] ?? '';

if (empty($googleEmail)) {
    setFlash('error', 'Could not retrieve email from Google account.');
    redirect(baseUrl('admin/login'));
}

// Step 4: Check if email is allowed
$allowedEmails = config('google_allowed_emails', '');
$emailAllowed = false;

if (!empty($allowedEmails)) {
    // Check against whitelist
    $allowedList = array_filter(array_map('trim', explode("\n", $allowedEmails)));
    foreach ($allowedList as $allowed) {
        if (strtolower(trim($allowed)) === strtolower($googleEmail)) {
            $emailAllowed = true;
            break;
        }
    }
} else {
    // No whitelist = check if email matches an existing admin user
    $emailAllowed = true;
}

if (!$emailAllowed) {
    setFlash('error', 'Your Google account (' . htmlspecialchars($googleEmail) . ') is not authorized to access the admin panel.');
    redirect(baseUrl('admin/login'));
}

// Step 5: Login or create/link user
$result = loginWithGoogle($googleEmail, $googleName, $googleAvatar);

if ($result['success']) {
    setFlash('success', 'Welcome, ' . ($_SESSION['full_name'] ?? $googleName) . '! Logged in via Google.');
    redirect(baseUrl('admin/dashboard'));
} else {
    setFlash('error', $result['message']);
    redirect(baseUrl('admin/login'));
}
