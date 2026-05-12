<?php
/**
 * API - Newsletter Subscription
 */
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Get email from JSON body or form data
$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? post('email');

if (empty($email) || !isValidEmail($email)) {
    jsonResponse(['success' => false, 'message' => 'Please enter a valid email address.'], 422);
}

try {
    // Check if already subscribed
    $existing = db()->fetch("SELECT id, is_active FROM newsletter WHERE email = ?", [$email]);
    
    if ($existing) {
        if ($existing['is_active']) {
            jsonResponse(['success' => false, 'message' => 'This email is already subscribed.']);
        } else {
            // Reactivate
            db()->update('newsletter', ['is_active' => 1, 'unsubscribed_at' => null], 'id = ?', [$existing['id']]);
            jsonResponse(['success' => true, 'message' => 'Welcome back! Subscription reactivated.']);
        }
    } else {
        db()->insert('newsletter', ['email' => $email]);
        jsonResponse(['success' => true, 'message' => 'Successfully subscribed! Thank you.']);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Subscription failed. Please try again.'], 500);
}
