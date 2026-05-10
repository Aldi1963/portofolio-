<?php
/**
 * Blog List Page
 * Articles with search, categories, and pagination
 */
$pageTitle = lang('blog_title');
$pageDescription = 'Read articles about web development, design, technology, and more.';
include TEMPLATES_PATH . '/header.php';

// Get search query
$search = get('q');
$categorySlug = get('category');
$tag = get('tag');
$currentPage = (int)(get('page') ?: 1);
$perPage = 6;

// Build query
$where = "b.status = 'published'";
$params = [];

if (!empty($search)) {
    $where .= " AND (b.title LIKE ? OR b.content LIKE ? OR b.tags LIKE ?)";
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if (!empty($categorySlug)) {
    $where .= " AND c.slug = ?";
    $params[] = $categorySlug;
}

if (!empty($tag)) {
    $where .= " AND b.tags LIKE ?";
    $params[] = "%$tag%";
}

try {
    // Get total
    $totalPosts = db()->fetch(
        "SELECT COUNT(*) as total FROM blogs b LEFT JOIN categories c ON b.category_id = c.id WHERE $where",
        $params
    )['total'];
    
    $pagination = paginate($totalPosts, $perPage, $currentPage);
    
    // Get posts
    $posts = db()->fetchAll(
        "SELECT b.*, c.name as category_name, c.slug as category_slug, u.full_name as author_name 
         FROM blogs b 
         LEFT JOIN categories c ON b.category_id = c.id 
         LEFT JOIN users u ON b.user_id = u.id 
         WHERE $where 
         ORDER BY b.is_featured DESC, b.published_at DESC 
         LIMIT $perPage OFFSET {$pagination['offset']}",
        $params
    );
    
    // Get categories with count
    $blogCategories = db()->fetchAll(
        "SELECT c.*, COUNT(b.id) as post_count 
         FROM categories c 
         LEFT JOIN blogs b ON c.id = b.category_id AND b.status = 'published'
         WHERE c.type = 'blog' AND c.is_active = 1 
         GROUP BY c.id 
         ORDER BY c.sort_order ASC"
    );
    
    // Get featured post
    $featuredPost = db()->fetch(
        "SELECT b.*, c.name as category_name, c.slug as category_slug 
         FROM blogs b 
         LEFT JOIN categories c ON b.category_id = c.id 
         WHERE b.status = 'published' AND b.is_featured = 1 
         ORDER BY b.published_at DESC LIMIT 1"
    );
    
    // Get popular tags
    $allTags = [];
    $tagResults = db()->fetchAll("SELECT tags FROM blogs WHERE status = 'published' AND tags IS NOT NULL");
    foreach ($tagResults as $row) {
        $tags = explode(',', $row['tags']);
        foreach ($tags as $t) {
            $t = trim($t);
            if (!empty($t)) {
                $allTags[$t] = ($allTags[$t] ?? 0) + 1;
            }
        }
    }
    arsort($allTags);
    $popularTags = array_slice($allTags, 0, 10, true);
    
} catch (Exception $e) {
    $posts = [];
    $blogCategories = [];
    $featuredPost = null;
    $popularTags = [];
    $pagination = paginate(0, $perPage, 1);
}
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="page-header-content" data-aos="fade-up">
            <span class="page-subtitle">Articles & Insights</span>
            <h1 class="page-title"><?= lang('blog_title') ?></h1>
            <p class="page-desc">Thoughts, tutorials, and tips about web development and technology.</p>
            <div class="breadcrumb">
                <a href="<?= baseUrl() ?>">Home</a>
                <span class="separator"><i class="fas fa-chevron-right"></i></span>
                <span class="current">Blog</span>
            </div>
        </div>
    </div>
</section>

<!-- Blog Section -->
<section class="section blog-section">
    <div class="container">
        <div class="blog-layout">
            <!-- Main Content -->
            <div class="blog-main">
                <!-- Search Bar -->
                <form class="blog-search" action="<?= baseUrl('blog') ?>" method="GET" data-aos="fade-up">
                    <div class="search-input-group">
                        <i class="fas fa-search"></i>
                        <input type="text" name="q" value="<?= xssClean($search) ?>" placeholder="<?= lang('blog_search') ?>">
                        <?php if (!empty($search)): ?>
                        <a href="<?= baseUrl('blog') ?>" class="search-clear"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if (!empty($search)): ?>
                <p class="search-result-text" data-aos="fade-up">
                    Showing results for "<strong><?= xssClean($search) ?></strong>" (<?= $totalPosts ?> articles found)
                </p>
                <?php endif; ?>

                <!-- Blog Grid -->
                <div class="blog-grid">
                    <?php if (empty($posts)): ?>
                    <div class="empty-state" data-aos="fade-up">
                        <i class="fas fa-newspaper"></i>
                        <h3>No articles found</h3>
                        <p>Try a different search term or browse all articles.</p>
                        <?php if (!empty($search)): ?>
                        <a href="<?= baseUrl('blog') ?>" class="btn btn-outline">View All Articles</a>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <?php foreach ($posts as $index => $post): ?>
                    <article class="blog-card glass" data-aos="fade-up" data-aos-delay="<?= ($index % 3) * 100 ?>">
                        <div class="blog-card-image">
                            <a href="<?= baseUrl('blog/detail/' . $post['slug']) ?>">
                                <img src="<?= !empty($post['thumbnail']) ? uploadUrl($post['thumbnail']) : asset('images/blog-placeholder.jpg') ?>" 
                                     alt="<?= xssClean($post['title']) ?>" loading="lazy">
                            </a>
                            <span class="blog-category-badge">
                                <a href="<?= baseUrl('blog?category=' . ($post['category_slug'] ?? '')) ?>">
                                    <?= xssClean($post['category_name'] ?? 'Article') ?>
                                </a>
                            </span>
                        </div>
                        <div class="blog-card-content">
                            <div class="blog-card-meta">
                                <span><i class="fas fa-calendar"></i> <?= formatDate($post['published_at']) ?></span>
                                <span><i class="fas fa-eye"></i> <?= formatNumber($post['views']) ?></span>
                            </div>
                            <h3 class="blog-card-title">
                                <a href="<?= baseUrl('blog/detail/' . $post['slug']) ?>">
                                    <?= xssClean($post['title']) ?>
                                </a>
                            </h3>
                            <p class="blog-card-excerpt"><?= truncateText($post['excerpt'] ?? $post['content'], 150) ?></p>
                            <div class="blog-card-footer">
                                <a href="<?= baseUrl('blog/detail/' . $post['slug']) ?>" class="read-more">
                                    <?= lang('blog_read_more') ?> <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?= paginationHtml($pagination, baseUrl('blog')) ?>
            </div>

            <!-- Sidebar -->
            <aside class="blog-sidebar">
                <!-- Categories -->
                <div class="sidebar-widget glass" data-aos="fade-left">
                    <h3 class="widget-title">Categories</h3>
                    <ul class="category-list">
                        <li class="<?= empty($categorySlug) ? 'active' : '' ?>">
                            <a href="<?= baseUrl('blog') ?>">
                                <span>All Posts</span>
                                <span class="count"><?= $totalPosts ?></span>
                            </a>
                        </li>
                        <?php foreach ($blogCategories as $cat): ?>
                        <li class="<?= $categorySlug === $cat['slug'] ? 'active' : '' ?>">
                            <a href="<?= baseUrl('blog?category=' . $cat['slug']) ?>">
                                <span><?= xssClean($cat['name']) ?></span>
                                <span class="count"><?= $cat['post_count'] ?></span>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Popular Tags -->
                <?php if (!empty($popularTags)): ?>
                <div class="sidebar-widget glass" data-aos="fade-left" data-aos-delay="100">
                    <h3 class="widget-title">Popular Tags</h3>
                    <div class="tag-cloud">
                        <?php foreach ($popularTags as $tagName => $count): ?>
                        <a href="<?= baseUrl('blog?tag=' . urlencode($tagName)) ?>" class="tag-item <?= $tag === $tagName ? 'active' : '' ?>">
                            #<?= xssClean($tagName) ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Newsletter Widget -->
                <div class="sidebar-widget glass newsletter-widget" data-aos="fade-left" data-aos-delay="200">
                    <h3 class="widget-title">Newsletter</h3>
                    <p>Get notified about new articles and resources.</p>
                    <form class="newsletter-form" id="sidebar-newsletter">
                        <input type="email" name="email" placeholder="Your email address" required>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> Subscribe
                        </button>
                    </form>
                </div>
            </aside>
        </div>
    </div>
</section>

<?php include TEMPLATES_PATH . '/footer.php'; ?>
