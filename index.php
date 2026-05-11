<?php
/**
 * Main Entry Point
 * Routes all requests to appropriate controllers
 */

// Load configuration
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/session.php';

// Load dynamic config from database (overrides .env)
loadDynamicConfig();

// Track visitor
trackVisitor();

// Get requested URL
$url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
$url = filter_var($url, FILTER_SANITIZE_URL);

// Load routes
$routes = require_once __DIR__ . '/config/routes.php';

// Check if route exists
if (array_key_exists($url, $routes)) {
    $page = $routes[$url];
} else {
    // Check for dynamic routes (blog/detail/slug, portfolio/detail/id)
    $urlParts = explode('/', $url);
    
    if (count($urlParts) >= 3 && $urlParts[0] === 'blog' && $urlParts[1] === 'detail') {
        $page = 'blog-detail';
        $_GET['slug'] = $urlParts[2];
    } elseif (count($urlParts) >= 3 && $urlParts[0] === 'portfolio' && $urlParts[1] === 'detail') {
        $page = 'portfolio-detail';
        $_GET['id'] = $urlParts[2];
    } elseif (count($urlParts) >= 3 && $urlParts[0] === 'admin') {
        $page = 'admin/' . implode('-', array_slice($urlParts, 1));
        if (!array_key_exists($url, $routes)) {
            $page = '404';
        }
    } else {
        $page = '404';
    }
}

// Check admin authentication for admin routes
if (strpos($page, 'admin/') === 0 && $page !== 'admin/login' && $page !== 'admin/logout') {
    if (!isLoggedIn()) {
        redirect(APP_URL . '/admin/login');
        exit;
    }
}

// Load the page
$pagePath = __DIR__ . '/pages/' . $page . '.php';

if (file_exists($pagePath)) {
    require_once $pagePath;
} else {
    http_response_code(404);
    require_once __DIR__ . '/pages/404.php';
}
