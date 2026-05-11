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
if (!empty(config('recaptcha_secret_key'))) {
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
    if (!empty(config('mail_username')) && config('mail_username') !== 'your-email@gmail.com') {
        try {
            require_once ROOT_PATH . '/includes/email-template.php';
            
            $to = getSetting('owner_email', config('mail_from'));
            
            // Send HTML notification to admin
            $adminHtml = emailTemplateContact([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message
            ]);
            sendHtmlEmail($to, "New Contact: " . $subject, $adminHtml, $email);
            
            // Send auto-reply to sender
            $replyHtml = emailTemplateAutoReply(['name' => $name]);
            sendHtmlEmail($email, "Thank you for contacting " . getSetting('owner_name', 'us'), $replyHtml);
            
        } catch (Exception $e) {
            // Email failed but message saved - don't fail the request
        }
    }
    
    jsonResponse(['success' => true, 'message' => lang('contact_success')]);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Failed to save message. Please try again.'], 500);
}
