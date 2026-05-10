<?php
/**
 * API - Contact Form Handler
 * Receives contact form submission, validates, stores, and optionally sends email
 */
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Rate limiting
$rateLimit = checkRateLimit('contact', 5, 300);
if (!$rateLimit['allowed']) {
    jsonResponse(['success' => false, 'message' => $rateLimit['message']], 429);
}

// CSRF verification
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    jsonResponse(['success' => false, 'message' => 'Invalid security token. Please refresh the page.'], 403);
}

// Get and validate input
$name = post('name');
$email = post('email');
$phone = post('phone');
$subject = post('subject');
$message = $_POST['message'] ?? '';

// Validation
$errors = [];
if (empty($name) || strlen($name) < 2) $errors[] = 'Name is required (min 2 characters)';
if (empty($email) || !isValidEmail($email)) $errors[] = 'Valid email is required';
if (empty($subject) || strlen($subject) < 3) $errors[] = 'Subject is required (min 3 characters)';
if (empty($message) || strlen($message) < 10) $errors[] = 'Message is required (min 10 characters)';

if (!empty($errors)) {
    jsonResponse(['success' => false, 'message' => implode('. ', $errors)], 422);
}

// Verify reCAPTCHA if configured
if (!empty(RECAPTCHA_SECRET_KEY)) {
    $recaptchaToken = $_POST['recaptcha_token'] ?? '';
    if (!verifyRecaptcha($recaptchaToken)) {
        jsonResponse(['success' => false, 'message' => 'reCAPTCHA verification failed.'], 422);
    }
}

// Store in database
try {
    db()->insert('contacts', [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'subject' => $subject,
        'message' => cleanInput($message),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);
    
    recordRateLimitAttempt('contact');
    
    // Send email notification (optional)
    if (!empty(MAIL_USERNAME) && MAIL_USERNAME !== 'your-email@gmail.com') {
        try {
            $to = getSetting('owner_email', MAIL_FROM);
            $emailSubject = "New Contact: " . $subject;
            $emailBody = "New message from your portfolio website:\n\n";
            $emailBody .= "Name: $name\n";
            $emailBody .= "Email: $email\n";
            $emailBody .= "Phone: $phone\n";
            $emailBody .= "Subject: $subject\n";
            $emailBody .= "Message:\n$message\n";
            
            $headers = "From: " . MAIL_FROM . "\r\n";
            $headers .= "Reply-To: $email\r\n";
            
            @mail($to, $emailSubject, $emailBody, $headers);
        } catch (Exception $e) {
            // Email failed but message saved - don't fail the request
        }
    }
    
    jsonResponse(['success' => true, 'message' => lang('contact_success')]);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Failed to save message. Please try again.'], 500);
}
