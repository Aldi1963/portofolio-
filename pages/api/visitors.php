<?php
/**
 * API - Visitors Stats (for admin)
 */
header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'message' => 'Unauthorized'], 401);
}

$period = get('period') ?: '7days';

try {
    switch ($period) {
        case '30days':
            $days = 30;
            break;
        case '90days':
            $days = 90;
            break;
        default:
            $days = 7;
    }
    
    $stats = db()->fetchAll(
        "SELECT DATE(visited_at) as date, COUNT(*) as count 
         FROM visitors 
         WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
         GROUP BY DATE(visited_at) 
         ORDER BY date ASC",
        [$days]
    );
    
    $total = db()->count('visitors', 'visited_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)', [$days]);
    $unique = db()->fetch(
        "SELECT COUNT(DISTINCT ip_address) as total FROM visitors WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)",
        [$days]
    )['total'];
    
    jsonResponse([
        'success' => true,
        'data' => $stats,
        'total' => $total,
        'unique' => $unique,
        'period' => $period
    ]);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Failed to fetch stats.'], 500);
}
