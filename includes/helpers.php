<?php
/**
 * Helper Functions
 * Common utility functions used throughout the application
 */

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Get base URL
 */
function baseUrl($path = '') {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Get asset URL
 */
function asset($path) {
    return baseUrl('assets/' . ltrim($path, '/'));
}

/**
 * Get upload URL
 */
function uploadUrl($path) {
    if (empty($path)) return asset('images/placeholder.jpg');
    return baseUrl('uploads/' . ltrim($path, '/'));
}

/**
 * Get setting value from database
 */
function getSetting($key, $default = '') {
    static $settings = null;
    
    if ($settings === null) {
        try {
            $results = db()->fetchAll("SELECT setting_key, setting_value FROM settings");
            $settings = [];
            foreach ($results as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            $settings = [];
        }
    }
    
    return $settings[$key] ?? $default;
}

/**
 * Generate SEO-friendly slug
 */
function createSlug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

/**
 * Truncate text
 */
function truncateText($text, $length = 150, $suffix = '...') {
    $text = strip_tags($text);
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . $suffix;
}

/**
 * Format date
 */
function formatDate($date, $format = 'd M Y') {
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

/**
 * Format relative time
 */
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $diff = time() - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    if ($diff < 2592000) return floor($diff / 604800) . ' weeks ago';
    
    return formatDate($datetime);
}

/**
 * Format number with abbreviation
 */
function formatNumber($num) {
    if ($num >= 1000000) return round($num / 1000000, 1) . 'M';
    if ($num >= 1000) return round($num / 1000, 1) . 'K';
    return $num;
}

/**
 * Format currency (IDR)
 */
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Clean input
 */
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Get POST data safely
 */
function post($key, $default = '') {
    return isset($_POST[$key]) ? cleanInput($_POST[$key]) : $default;
}

/**
 * Get GET data safely
 */
function get($key, $default = '') {
    return isset($_GET[$key]) ? cleanInput($_GET[$key]) : $default;
}

/**
 * Flash message system
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function hasFlash() {
    return isset($_SESSION['flash']);
}

/**
 * Upload file with validation
 */
function uploadFile($file, $directory = 'images', $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'], $maxSize = 5242880) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file selected'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error: ' . $file['error']];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File too large. Maximum size: ' . ($maxSize / 1024 / 1024) . 'MB'];
    }
    
    // Check file type
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed: ' . implode(', ', $allowedTypes)];
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowedMimes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
        'svg' => 'image/svg+xml'
    ];
    
    if (isset($allowedMimes[$extension]) && $mimeType !== $allowedMimes[$extension]) {
        return ['success' => false, 'message' => 'File MIME type mismatch'];
    }
    
    // Create directory if not exists
    $uploadDir = UPLOADS_PATH . '/' . $directory;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Compress image if it's an image type (not PDF/SVG)
        $imageTypes = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($extension, $imageTypes)) {
            compressImage($filepath, $filepath, 80);
        }
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $directory . '/' . $filename,
            'full_path' => $filepath
        ];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Compress and resize image
 * Reduces file size while maintaining quality
 */
function compressImage($source, $destination, $quality = 80, $maxWidth = 1920, $maxHeight = 1920) {
    $info = getimagesize($source);
    if ($info === false) return false;
    
    $mime = $info['mime'];
    $width = $info[0];
    $height = $info[1];
    
    // Create image resource based on type
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    if (!$image) return false;
    
    // Resize if too large
    if ($width > $maxWidth || $height > $maxHeight) {
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);
        
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG/WebP
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }
    
    // Save compressed image
    switch ($mime) {
        case 'image/jpeg':
            $result = imagejpeg($image, $destination, $quality);
            break;
        case 'image/png':
            // PNG quality is 0-9 (inverted from jpeg)
            $pngQuality = (int)(9 - ($quality / 100 * 9));
            $result = imagepng($image, $destination, $pngQuality);
            break;
        case 'image/webp':
            $result = imagewebp($image, $destination, $quality);
            break;
        default:
            $result = false;
    }
    
    imagedestroy($image);
    return $result;
}

/**
 * Delete uploaded file
 */
function deleteFile($path) {
    $fullPath = UPLOADS_PATH . '/' . $path;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Pagination helper
 */
function paginate($totalItems, $perPage = 10, $currentPage = 1) {
    $totalPages = ceil($totalItems / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    
    return [
        'total' => $totalItems,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
    ];
}

/**
 * Generate pagination HTML
 */
function paginationHtml($pagination, $baseUrl) {
    if ($pagination['total_pages'] <= 1) return '';
    
    $html = '<nav class="pagination-wrapper"><ul class="pagination">';
    
    // Previous
    if ($pagination['has_prev']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($pagination['current_page'] - 1) . '"><i class="fas fa-chevron-left"></i></a></li>';
    }
    
    // Pages
    $start = max(1, $pagination['current_page'] - 2);
    $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
    
    if ($start > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=1">1</a></li>';
        if ($start > 2) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
    
    for ($i = $start; $i <= $end; $i++) {
        $active = $i == $pagination['current_page'] ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
    }
    
    if ($end < $pagination['total_pages']) {
        if ($end < $pagination['total_pages'] - 1) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $pagination['total_pages'] . '">' . $pagination['total_pages'] . '</a></li>';
    }
    
    // Next
    if ($pagination['has_next']) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($pagination['current_page'] + 1) . '"><i class="fas fa-chevron-right"></i></a></li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Track visitor
 */
function trackVisitor() {
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $page = $_SERVER['REQUEST_URI'] ?? '/';
        $referrer = $_SERVER['HTTP_REFERER'] ?? '';
        
        // Detect device type
        $deviceType = 'desktop';
        if (preg_match('/Mobile|Android|iPhone/i', $userAgent)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/Tablet|iPad/i', $userAgent)) {
            $deviceType = 'tablet';
        }
        
        // Detect browser
        $browser = 'Other';
        if (preg_match('/Chrome/i', $userAgent)) $browser = 'Chrome';
        elseif (preg_match('/Firefox/i', $userAgent)) $browser = 'Firefox';
        elseif (preg_match('/Safari/i', $userAgent)) $browser = 'Safari';
        elseif (preg_match('/Edge/i', $userAgent)) $browser = 'Edge';
        elseif (preg_match('/Opera/i', $userAgent)) $browser = 'Opera';
        
        db()->insert('visitors', [
            'ip_address' => $ip,
            'user_agent' => substr($userAgent, 0, 500),
            'page_visited' => substr($page, 0, 255),
            'referrer' => substr($referrer, 0, 500),
            'device_type' => $deviceType,
            'browser' => $browser
        ]);
    } catch (Exception $e) {
        // Silently fail - don't break the site for tracking
    }
}

/**
 * Get language string
 */
function lang($key, $default = '') {
    static $strings = null;
    
    if ($strings === null) {
        $langFile = ROOT_PATH . '/config/lang/' . APP_LANG . '.php';
        if (file_exists($langFile)) {
            $strings = require $langFile;
        } else {
            $strings = [];
        }
    }
    
    return $strings[$key] ?? $default ?: $key;
}

/**
 * JSON response helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Check if request is AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Calculate reading time for content
 * @param string $content HTML or plain text content
 * @return int Estimated reading time in minutes (minimum 1)
 */
function readingTime($content) {
    $text = strip_tags($content);
    $wordCount = str_word_count($text);
    $minutes = max(1, (int)ceil($wordCount / 200));
    return $minutes;
}

/**
 * Log user activity
 * @param string $action Short action identifier (e.g. 'login', 'create_project')
 * @param string $description Detailed description of the action
 */
function logActivity($action, $description) {
    try {
        $userId = $_SESSION['user_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        db()->insert('activity_log', [
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ip,
        ]);
    } catch (Exception $e) {
        // Silently fail - don't break the app for logging
    }
}

/**
 * Generate meta tags
 */
function metaTags($title = '', $description = '', $image = '', $type = 'website') {
    $siteName = getSetting('site_name', APP_NAME);
    $title = $title ? $title . ' - ' . $siteName : $siteName;
    $description = $description ?: getSetting('site_description', '');
    $image = $image ?: baseUrl('assets/images/og-image.jpg');
    $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    
    $meta = '';
    $meta .= '<title>' . htmlspecialchars($title) . '</title>' . "\n";
    $meta .= '<meta name="description" content="' . htmlspecialchars($description) . '">' . "\n";
    $meta .= '<meta name="keywords" content="' . htmlspecialchars(getSetting('site_keywords', '')) . '">' . "\n";
    $meta .= '<meta property="og:title" content="' . htmlspecialchars($title) . '">' . "\n";
    $meta .= '<meta property="og:description" content="' . htmlspecialchars($description) . '">' . "\n";
    $meta .= '<meta property="og:image" content="' . $image . '">' . "\n";
    $meta .= '<meta property="og:url" content="' . $url . '">' . "\n";
    $meta .= '<meta property="og:type" content="' . $type . '">' . "\n";
    $meta .= '<meta property="og:site_name" content="' . htmlspecialchars($siteName) . '">' . "\n";
    $meta .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $meta .= '<meta name="twitter:title" content="' . htmlspecialchars($title) . '">' . "\n";
    $meta .= '<meta name="twitter:description" content="' . htmlspecialchars($description) . '">' . "\n";
    $meta .= '<meta name="twitter:image" content="' . $image . '">' . "\n";
    $meta .= '<link rel="canonical" href="' . $url . '">' . "\n";
    
    return $meta;
}


/**
 * Generate JSON-LD Schema Markup
 * @param string $type Schema.org type (e.g., 'Person', 'Article', 'CreativeWork')
 * @param array $data Key-value pairs for the schema properties
 * @return string HTML script tag with JSON-LD markup
 */
function schemaMarkup($type, $data) {
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => $type,
    ];
    
    $schema = array_merge($schema, $data);
    
    return '<script type="application/ld+json">' . "\n" . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n" . '</script>';
}
