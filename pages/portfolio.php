<?php
/**
 * Portfolio Page
 * Project grid with category filter, AJAX load more
 */
$pageTitle = lang('portfolio_title');
$pageDescription = 'Explore my portfolio of web development, design, and mobile app projects.';
include TEMPLATES_PATH . '/header.php';

// Get categories
try {
    $categories = db()->fetchAll(
        "SELECT c.*, COUNT(p.id) as project_count 
         FROM categories c 
         LEFT JOIN projects p ON c.id = p.category_id AND p.is_active = 1
         WHERE c.type = 'project' AND c.is_active = 1 
         GROUP BY c.id 
         ORDER BY c.sort_order ASC"
    );
} catch (Exception $e) {
    $categories = [];
}

// Get projects (initial load)
$currentPage = (int)(get('page') ?: 1);
$perPage = 6;
$categoryFilter = get('category');

try {
    $where = "p.is_active = 1";
    $params = [];
    
    if (!empty($categoryFilter)) {
        $where .= " AND c.slug = ?";
        $params[] = $categoryFilter;
    }
    
    $totalProjects = db()->fetch(
        "SELECT COUNT(*) as total FROM projects p LEFT JOIN categories c ON p.category_id = c.id WHERE $where", 
        $params
    )['total'];
    
    $pagination = paginate($totalProjects, $perPage, $currentPage);
    
    $projects = db()->fetchAll(
        "SELECT p.*, c.name as category_name, c.slug as category_slug 
         FROM projects p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE $where 
         ORDER BY p.is_featured DESC, p.sort_order ASC, p.created_at DESC 
         LIMIT $perPage OFFSET {$pagination['offset']}",
        $params
    );
} catch (Exception $e) {
    $projects = [];
    $pagination = paginate(0, $perPage, 1);
}
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="page-header-content" data-aos="fade-up">
            <span class="page-subtitle">My Work</span>
            <h1 class="page-title"><?= lang('portfolio_title') ?></h1>
            <p class="page-desc">A collection of projects I've worked on, from web apps to design systems.</p>
            <div class="breadcrumb">
                <a href="<?= baseUrl() ?>">Home</a>
                <span class="separator"><i class="fas fa-chevron-right"></i></span>
                <span class="current">Portfolio</span>
            </div>
            <div style="margin-top:16px;">
                <a href="<?= baseUrl('portfolio/pdf') ?>" class="btn btn-outline" style="font-size:0.85rem;">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio Section -->
<section class="section portfolio-section">
    <div class="container">
        <!-- Category Filter -->
        <div class="filter-bar" data-aos="fade-up">
            <button class="filter-btn <?= empty($categoryFilter) ? 'active' : '' ?>" data-filter="all">
                <?= lang('portfolio_all') ?>
                <span class="filter-count"><?= $totalProjects ?></span>
            </button>
            <?php foreach ($categories as $cat): ?>
            <button class="filter-btn <?= $categoryFilter === $cat['slug'] ? 'active' : '' ?>" 
                    data-filter="<?= $cat['slug'] ?>">
                <?= xssClean($cat['name']) ?>
                <span class="filter-count"><?= $cat['project_count'] ?></span>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Projects Grid -->
        <div class="projects-grid" id="projects-grid">
            <?php if (empty($projects)): ?>
            <div class="empty-state" data-aos="fade-up">
                <i class="fas fa-folder-open"></i>
                <h3>No projects found</h3>
                <p>Check back later for new projects!</p>
            </div>
            <?php else: ?>
            <?php foreach ($projects as $index => $project): ?>
            <div class="project-card glass" data-category="<?= $project['category_slug'] ?? '' ?>" data-aos="fade-up" data-aos-delay="<?= ($index % 3) * 100 ?>">
                <div class="project-image">
                    <img src="<?= !empty($project['thumbnail']) ? uploadUrl($project['thumbnail']) : asset('images/project-placeholder.jpg') ?>" 
                         alt="<?= xssClean($project['title']) ?>" loading="lazy">
                    <?php if ($project['is_featured']): ?>
                    <span class="project-badge"><i class="fas fa-star"></i> Featured</span>
                    <?php endif; ?>
                    <div class="project-overlay">
                        <div class="project-actions">
                            <?php if (!empty($project['demo_url'])): ?>
                            <a href="<?= $project['demo_url'] ?>" target="_blank" class="project-btn" title="Live Demo">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($project['github_url'])): ?>
                            <a href="<?= $project['github_url'] ?>" target="_blank" class="project-btn" title="GitHub">
                                <i class="fab fa-github"></i>
                            </a>
                            <?php endif; ?>
                            <a href="<?= baseUrl('portfolio/detail/' . $project['id']) ?>" class="project-btn" title="Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="project-info">
                    <span class="project-category"><?= xssClean($project['category_name'] ?? 'Project') ?></span>
                    <h3 class="project-title">
                        <a href="<?= baseUrl('portfolio/detail/' . $project['id']) ?>">
                            <?= xssClean($project['title']) ?>
                        </a>
                    </h3>
                    <p class="project-desc"><?= truncateText($project['short_description'] ?? $project['description'], 120) ?></p>
                    <div class="project-tech">
                        <?php 
                        $techs = explode(',', $project['technologies'] ?? '');
                        foreach (array_slice($techs, 0, 4) as $tech): 
                        ?>
                        <span class="tech-badge"><?= trim($tech) ?></span>
                        <?php endforeach; ?>
                        <?php if (count($techs) > 4): ?>
                        <span class="tech-badge more">+<?= count($techs) - 4 ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="project-footer">
                        <?php if (!empty($project['client_name'])): ?>
                        <span class="project-client"><i class="fas fa-user"></i> <?= xssClean($project['client_name']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($project['project_date'])): ?>
                        <span class="project-date"><i class="fas fa-calendar"></i> <?= formatDate($project['project_date'], 'M Y') ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Load More / Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
        <div class="portfolio-pagination" data-aos="fade-up">
            <button class="btn btn-outline btn-load-more" 
                    id="load-more-btn"
                    data-page="<?= $currentPage ?>"
                    data-total="<?= $pagination['total_pages'] ?>"
                    data-category="<?= $categoryFilter ?>">
                <i class="fas fa-sync-alt"></i> Load More Projects
            </button>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include TEMPLATES_PATH . '/footer.php'; ?>
