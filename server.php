<?php
/**
 * Local Development Server Router
 * Emulates Apache mod_rewrite functionality for PHP built-in web server.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/'
);

// Normalize multiple slashes
$uri = preg_replace('#/+#', '/', $uri);

// Serve existing files or directories directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Pass requested URL path to index.php via the 'url' GET parameter
$_GET['url'] = ltrim($uri, '/');
require_once __DIR__ . '/index.php';
