<?php
/**
 * Portfolio Detail Page
 * Single project view with gallery
 */
$projectId = (int)(get('id') ?: 0);

if (!$projectId) {
    redirect(baseUrl('portfolio'));
}

try {
    $project = db()->fetch(
        "SELECT p.*, c.name as category_name, c.slug as category_slug 
         FROM projects p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.id = ? AND p.is_active = 1",
        [$projectId]
    );
} catch (Exception $e) {
    $project = null;
}

if (!$project) {
    http_response_code(404);
    include ROOT_PATH . '/pages/404.php';
    exit;
}

// Increment view count
try {
    db()->query("UPDATE projects SET views = views + 1 WHERE id = ?", [$projectId]);
} catch (Exception $e) {}

// Get project images
try {
    $projectImages = db()->fetchAll(
        "SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC",
        [$projectId]
    );
} catch (Exception $e) {
    $projectImages = [];
}

// Get related projects
try {
    $relatedProjects = db()->fetchAll(
        "SELECT p.*, c.name as category_name FROM projects p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.id != ? AND p.category_id = ? AND p.is_active = 1 
         ORDER BY RAND() LIMIT 3",
        [$projectId, $project['category_id']]
    );
} catch (Exception $e) {
    $relatedProjects = [];
}

$pageTitle = $project['title'];
$pageDescription = $project['short_description'] ?? truncateText($project['description'], 160);
$pageImage = !empty($project['thumbnail']) ? uploadUrl($project['thumbnail']) : '';
include TEMPLATES_PATH . '/header.php';
?>

<!-- Page Header -->
<section class="page-header page-header-compact">
    <div class="container">
        <div class="breadcrumb" data-aos="fade-up">
            <a href="<?= baseUrl() ?>">Home</a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <a href="<?= baseUrl('portfolio') ?>">Portfolio</a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <span class="current"><?= xssClean($project['title']) ?></span>
        </div>
    </div>
</section>

<!-- Project Detail -->
<section class="section project-detail-section">
    <div class="container">
        <div class="project-detail-grid">
            <!-- Main Content -->
            <div class="project-detail-main">
                <!-- Project Header -->
                <div class="project-detail-header" data-aos="fade-up">
                    <span class="project-category-badge"><?= xssClean($project['category_name'] ?? 'Project') ?></span>
                    <h1 class="project-detail-title"><?= xssClean($project['title']) ?></h1>
                    <div class="project-meta">
                        <?php if (!empty($project['client_name'])): ?>
                        <span><i class="fas fa-user"></i> <?= xssClean($project['client_name']) ?></span>
                        <?php endif; ?>
                        <?php if (!empty($project['project_date'])): ?>
                        <span><i class="fas fa-calendar"></i> <?= formatDate($project['project_date'], 'F Y') ?></span>
                        <?php endif; ?>
                        <span><i class="fas fa-eye"></i> <?= formatNumber($project['views']) ?> views</span>
                    </div>
                </div>

                <!-- Project Image / Gallery -->
                <div class="project-gallery" data-aos="fade-up">
                    <div class="gallery-main">
                        <img src="<?= !empty($project['thumbnail']) ? uploadUrl($project['thumbnail']) : asset('images/project-placeholder.jpg') ?>" 
                             alt="<?= xssClean($project['title']) ?>" id="gallery-main-img">
                    </div>
                    <?php if (!empty($projectImages)): ?>
                    <div class="gallery-thumbnails">
                        <button class="gallery-thumb active" data-src="<?= !empty($project['thumbnail']) ? uploadUrl($project['thumbnail']) : asset('images/project-placeholder.jpg') ?>">
                            <img src="<?= !empty($project['thumbnail']) ? uploadUrl($project['thumbnail']) : asset('images/project-placeholder.jpg') ?>" alt="Main">
                        </button>
                        <?php foreach ($projectImages as $img): ?>
                        <button class="gallery-thumb" data-src="<?= uploadUrl($img['image_path']) ?>">
                            <img src="<?= uploadUrl($img['image_path']) ?>" alt="<?= xssClean($img['caption'] ?? '') ?>">
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Project Description -->
                <div class="project-description" data-aos="fade-up">
                    <h2>About This Project</h2>
                    <div class="content-body">
                        <?= $project['description'] ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="project-detail-sidebar">
                <!-- Project Info Card -->
                <div class="sidebar-card glass" data-aos="fade-left">
                    <h3 class="sidebar-card-title">Project Details</h3>
                    <ul class="project-info-list">
                        <?php if (!empty($project['client_name'])): ?>
                        <li>
                            <span class="info-label"><i class="fas fa-user"></i> Client</span>
                            <span class="info-value"><?= xssClean($project['client_name']) ?></span>
                        </li>
                        <?php endif; ?>
                        <li>
                            <span class="info-label"><i class="fas fa-tag"></i> Category</span>
                            <span class="info-value"><?= xssClean($project['category_name'] ?? 'N/A') ?></span>
                        </li>
                        <?php if (!empty($project['project_date'])): ?>
                        <li>
                            <span class="info-label"><i class="fas fa-calendar"></i> Date</span>
                            <span class="info-value"><?= formatDate($project['project_date'], 'F Y') ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>

                    <!-- Technologies -->
                    <?php if (!empty($project['technologies'])): ?>
                    <h4 class="tech-title">Technologies Used</h4>
                    <div class="project-tech-list">
                        <?php foreach (explode(',', $project['technologies']) as $tech): ?>
                        <span class="tech-badge"><?= trim($tech) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="project-actions-sidebar">
                        <?php if (!empty($project['demo_url'])): ?>
                        <a href="<?= $project['demo_url'] ?>" target="_blank" class="btn btn-primary btn-block">
                            <i class="fas fa-external-link-alt"></i> <?= lang('portfolio_demo') ?>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($project['github_url'])): ?>
                        <a href="<?= $project['github_url'] ?>" target="_blank" class="btn btn-outline btn-block">
                            <i class="fab fa-github"></i> <?= lang('portfolio_github') ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Share Card -->
                <div class="sidebar-card glass" data-aos="fade-left" data-aos-delay="100">
                    <h3 class="sidebar-card-title">Share Project</h3>
                    <div class="share-buttons">
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(baseUrl('portfolio/detail/' . $project['id'])) ?>&text=<?= urlencode($project['title']) ?>" 
                           target="_blank" class="share-btn twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(baseUrl('portfolio/detail/' . $project['id'])) ?>" 
                           target="_blank" class="share-btn linkedin"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(baseUrl('portfolio/detail/' . $project['id'])) ?>" 
                           target="_blank" class="share-btn facebook"><i class="fab fa-facebook-f"></i></a>
                        <button class="share-btn copy-link" data-url="<?= baseUrl('portfolio/detail/' . $project['id']) ?>">
                            <i class="fas fa-link"></i>
                        </button>
                    </div>
                </div>
            </aside>
        </div>

        <!-- Related Projects -->
        <?php if (!empty($relatedProjects)): ?>
        <div class="related-projects" data-aos="fade-up">
            <h2 class="section-title">Related Projects</h2>
            <div class="projects-grid projects-grid-3">
                <?php foreach ($relatedProjects as $related): ?>
                <div class="project-card glass">
                    <div class="project-image">
                        <img src="<?= !empty($related['thumbnail']) ? uploadUrl($related['thumbnail']) : asset('images/project-placeholder.jpg') ?>" 
                             alt="<?= xssClean($related['title']) ?>" loading="lazy">
                        <div class="project-overlay">
                            <div class="project-actions">
                                <a href="<?= baseUrl('portfolio/detail/' . $related['id']) ?>" class="project-btn">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="project-info">
                        <span class="project-category"><?= xssClean($related['category_name'] ?? 'Project') ?></span>
                        <h3 class="project-title">
                            <a href="<?= baseUrl('portfolio/detail/' . $related['id']) ?>"><?= xssClean($related['title']) ?></a>
                        </h3>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include TEMPLATES_PATH . '/footer.php'; ?>
