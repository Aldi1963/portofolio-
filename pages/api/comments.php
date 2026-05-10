<?php
/**
 * API - Blog Comments
 */
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

// Rate limit
$rateLimit = checkRateLimit('comment', 10, 600);
if (!$rateLimit['allowed']) {
    jsonResponse(['success' => false, 'message' => $rateLimit['message']], 429);
}

// CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    jsonResponse(['success' => false, 'message' => 'Invalid security token.'], 403);
}

$blogId = (int)($_POST['blog_id'] ?? 0);
$name = post('name');
$email = post('email');
$website = post('website');
$content = $_POST['content'] ?? '';

// Validation
if (!$blogId) jsonResponse(['success' => false, 'message' => 'Invalid article.'], 422);
if (empty($name) || strlen($name) < 2) jsonResponse(['success' => false, 'message' => 'Name is required.'], 422);
if (empty($email) || !isValidEmail($email)) jsonResponse(['success' => false, 'message' => 'Valid email is required.'], 422);
if (empty($content) || strlen($content) < 5) jsonResponse(['success' => false, 'message' => 'Comment is too short.'], 422);
if (!empty($website) && !isValidUrl($website)) jsonResponse(['success' => false, 'message' => 'Invalid website URL.'], 422);

// Verify blog exists
try {
    $blog = db()->fetch("SELECT id FROM blogs WHERE id = ? AND status = 'published'", [$blogId]);
    if (!$blog) jsonResponse(['success' => false, 'message' => 'Article not found.'], 404);
    
    db()->insert('comments', [
        'blog_id' => $blogId,
        'name' => $name,
        'email' => $email,
        'website' => $website,
        'content' => cleanInput($content),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        'is_approved' => 0 // Requires admin approval
    ]);
    
    recordRateLimitAttempt('comment');
    jsonResponse(['success' => true, 'message' => 'Comment submitted! It will appear after approval.']);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Failed to post comment.'], 500);
}
