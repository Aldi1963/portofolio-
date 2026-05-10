<?php
/**
 * API - Projects (Load More)
 */
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
}

$page = (int)(get('page') ?: 1);
$perPage = 6;
$category = get('category');

$where = "p.is_active = 1";
$params = [];

if (!empty($category)) {
    $where .= " AND c.slug = ?";
    $params[] = $category;
}

try {
    $total = db()->fetch(
        "SELECT COUNT(*) as total FROM projects p LEFT JOIN categories c ON p.category_id = c.id WHERE $where",
        $params
    )['total'];
    
    $totalPages = ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;
    
    $projects = db()->fetchAll(
        "SELECT p.*, c.name as category_name, c.slug as category_slug 
         FROM projects p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE $where 
         ORDER BY p.is_featured DESC, p.sort_order ASC 
         LIMIT $perPage OFFSET $offset",
        $params
    );
    
    // Generate HTML
    $html = '';
    foreach ($projects as $project) {
        $thumb = !empty($project['thumbnail']) ? uploadUrl($project['thumbnail']) : asset('images/project-placeholder.jpg');
        $techs = explode(',', $project['technologies'] ?? '');
        $techHtml = '';
        foreach (array_slice($techs, 0, 4) as $tech) {
            $techHtml .= '<span class="tech-badge">' . trim($tech) . '</span>';
        }
        
        $html .= '<div class="project-card glass" data-category="' . ($project['category_slug'] ?? '') . '" data-aos="fade-up">';
        $html .= '<div class="project-image">';
        $html .= '<img src="' . $thumb . '" alt="' . xssClean($project['title']) . '" loading="lazy">';
        $html .= '<div class="project-overlay"><div class="project-actions">';
        if (!empty($project['demo_url'])) {
            $html .= '<a href="' . $project['demo_url'] . '" target="_blank" class="project-btn"><i class="fas fa-external-link-alt"></i></a>';
        }
        $html .= '<a href="' . baseUrl('portfolio/detail/' . $project['id']) . '" class="project-btn"><i class="fas fa-eye"></i></a>';
        $html .= '</div></div></div>';
        $html .= '<div class="project-info">';
        $html .= '<span class="project-category">' . xssClean($project['category_name'] ?? 'Project') . '</span>';
        $html .= '<h3 class="project-title"><a href="' . baseUrl('portfolio/detail/' . $project['id']) . '">' . xssClean($project['title']) . '</a></h3>';
        $html .= '<p class="project-desc">' . truncateText($project['short_description'] ?? $project['description'], 100) . '</p>';
        $html .= '<div class="project-tech">' . $techHtml . '</div>';
        $html .= '</div></div>';
    }
    
    jsonResponse([
        'success' => true,
        'html' => $html,
        'page' => $page,
        'total_pages' => $totalPages,
        'total' => $total
    ]);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Failed to load projects.'], 500);
}
