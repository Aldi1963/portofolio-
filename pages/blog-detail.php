<?php
/**
 * Blog Detail Page
 * Single article with comments
 */
$slug = get('slug') ?: '';

if (empty($slug)) {
    redirect(baseUrl('blog'));
}

try {
    $post = db()->fetch(
        "SELECT b.*, c.name as category_name, c.slug as category_slug, u.full_name as author_name, u.avatar as author_avatar 
         FROM blogs b 
         LEFT JOIN categories c ON b.category_id = c.id 
         LEFT JOIN users u ON b.user_id = u.id 
         WHERE b.slug = ? AND b.status = 'published'",
        [$slug]
    );
} catch (Exception $e) {
    $post = null;
}

if (!$post) {
    http_response_code(404);
    include ROOT_PATH . '/pages/404.php';
    exit;
}

// Increment views
try {
    db()->query("UPDATE blogs SET views = views + 1 WHERE id = ?", [$post['id']]);
} catch (Exception $e) {}

// Get comments
try {
    $comments = db()->fetchAll(
        "SELECT * FROM comments WHERE blog_id = ? AND is_approved = 1 AND parent_id IS NULL ORDER BY created_at DESC",
        [$post['id']]
    );
    $commentCount = db()->count('comments', 'blog_id = ? AND is_approved = 1', [$post['id']]);
} catch (Exception $e) {
    $comments = [];
    $commentCount = 0;
}

// Get related posts
try {
    $relatedPosts = db()->fetchAll(
        "SELECT b.*, c.name as category_name FROM blogs b 
         LEFT JOIN categories c ON b.category_id = c.id 
         WHERE b.id != ? AND b.status = 'published' AND b.category_id = ? 
         ORDER BY b.published_at DESC LIMIT 3",
        [$post['id'], $post['category_id']]
    );
} catch (Exception $e) {
    $relatedPosts = [];
}

$pageTitle = $post['meta_title'] ?: $post['title'];
$pageDescription = $post['meta_description'] ?: truncateText($post['excerpt'] ?? $post['content'], 160);
$pageImage = !empty($post['thumbnail']) ? uploadUrl($post['thumbnail']) : '';
$pageType = 'article';
include TEMPLATES_PATH . '/header.php';
?>

<!-- Breadcrumb -->
<section class="page-header page-header-compact">
    <div class="container">
        <div class="breadcrumb" data-aos="fade-up">
            <a href="<?= baseUrl() ?>">Home</a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <a href="<?= baseUrl('blog') ?>">Blog</a>
            <span class="separator"><i class="fas fa-chevron-right"></i></span>
            <span class="current"><?= truncateText(xssClean($post['title']), 40) ?></span>
        </div>
    </div>
</section>

<!-- Blog Detail -->
<section class="section blog-detail-section">
    <div class="container">
        <div class="blog-detail-layout">
            <!-- Main Article -->
            <article class="blog-detail-main">
                <!-- Article Header -->
                <header class="article-header" data-aos="fade-up">
                    <a href="<?= baseUrl('blog?category=' . $post['category_slug']) ?>" class="article-category">
                        <?= xssClean($post['category_name'] ?? 'Article') ?>
                    </a>
                    <h1 class="article-title"><?= xssClean($post['title']) ?></h1>
                    <div class="article-meta">
                        <div class="author-info">
                            <div class="author-avatar-small">
                                <?php if (!empty($post['author_avatar'])): ?>
                                <img src="<?= uploadUrl($post['author_avatar']) ?>" alt="<?= xssClean($post['author_name']) ?>">
                                <?php else: ?>
                                <i class="fas fa-user"></i>
                                <?php endif; ?>
                            </div>
                            <span><?= xssClean($post['author_name'] ?? 'Admin') ?></span>
                        </div>
                        <span class="meta-separator">|</span>
                        <span><i class="fas fa-calendar"></i> <?= formatDate($post['published_at'], 'd M Y') ?></span>
                        <span class="meta-separator">|</span>
                        <span><i class="fas fa-eye"></i> <?= formatNumber($post['views']) ?> views</span>
                        <span class="meta-separator">|</span>
                        <span><i class="fas fa-clock"></i> <?= readingTime($post['content']) ?> min read</span>
                        <span class="meta-separator">|</span>
                        <span><i class="fas fa-comment"></i> <?= $commentCount ?> comments</span>
                    </div>
                </header>

                <!-- Featured Image -->
                <?php if (!empty($post['thumbnail'])): ?>
                <div class="article-featured-image" data-aos="fade-up">
                    <img src="<?= uploadUrl($post['thumbnail']) ?>" alt="<?= xssClean($post['title']) ?>">
                </div>
                <?php endif; ?>

                <!-- Article Content -->
                <div class="article-content" data-aos="fade-up">
                    <?= $post['content'] ?>
                </div>

                <!-- Tags -->
                <?php if (!empty($post['tags'])): ?>
                <div class="article-tags" data-aos="fade-up">
                    <i class="fas fa-tags"></i>
                    <?php foreach (explode(',', $post['tags']) as $tag): ?>
                    <a href="<?= baseUrl('blog?tag=' . urlencode(trim($tag))) ?>" class="tag-item">
                        #<?= xssClean(trim($tag)) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Share -->
                <div class="article-share" data-aos="fade-up">
                    <span class="share-label">Share this article:</span>
                    <div class="share-buttons">
                        <a href="https://twitter.com/intent/tweet?url=<?= urlencode(baseUrl('blog/detail/' . $post['slug'])) ?>&text=<?= urlencode($post['title']) ?>" 
                           target="_blank" class="share-btn twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(baseUrl('blog/detail/' . $post['slug'])) ?>" 
                           target="_blank" class="share-btn linkedin"><i class="fab fa-linkedin-in"></i></a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(baseUrl('blog/detail/' . $post['slug'])) ?>" 
                           target="_blank" class="share-btn facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://wa.me/?text=<?= urlencode($post['title'] . ' - ' . baseUrl('blog/detail/' . $post['slug'])) ?>" 
                           target="_blank" class="share-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="comments-section" id="comments" data-aos="fade-up">
                    <h3 class="comments-title">
                        <i class="fas fa-comments"></i> <?= lang('blog_comments') ?> (<?= $commentCount ?>)
                    </h3>

                    <!-- Comment List -->
                    <div class="comments-list">
                        <?php if (empty($comments)): ?>
                        <p class="no-comments">No comments yet. Be the first to comment!</p>
                        <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                        <div class="comment-item">
                            <div class="comment-avatar">
                                <div class="avatar-placeholder"><?= strtoupper(substr($comment['name'], 0, 1)) ?></div>
                            </div>
                            <div class="comment-body">
                                <div class="comment-header">
                                    <h4 class="comment-author"><?= xssClean($comment['name']) ?></h4>
                                    <span class="comment-date"><?= timeAgo($comment['created_at']) ?></span>
                                </div>
                                <p class="comment-text"><?= nl2br(xssClean($comment['content'])) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Comment Form -->
                    <div class="comment-form-wrapper">
                        <h4 class="form-title"><?= lang('blog_leave_comment') ?></h4>
                        <form class="comment-form" id="comment-form" data-blog-id="<?= $post['id'] ?>">
                            <?= csrfField() ?>
                            <div class="form-row">
                                <div class="form-group">
                                    <input type="text" name="name" placeholder="Your Name *" required>
                                </div>
                                <div class="form-group">
                                    <input type="email" name="email" placeholder="Your Email *" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="url" name="website" placeholder="Website (optional)">
                            </div>
                            <div class="form-group">
                                <textarea name="content" rows="5" placeholder="Write your comment... *" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Post Comment
                            </button>
                        </form>
                    </div>
                </div>
            </article>

            <!-- Sidebar -->
            <aside class="blog-sidebar">
                <!-- Author Card -->
                <div class="sidebar-widget glass author-widget" data-aos="fade-left">
                    <div class="author-card">
                        <div class="author-avatar-large">
                            <?php if (!empty($post['author_avatar'])): ?>
                            <img src="<?= uploadUrl($post['author_avatar']) ?>" alt="<?= xssClean($post['author_name']) ?>">
                            <?php else: ?>
                            <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <h4><?= xssClean($post['author_name'] ?? 'Admin') ?></h4>
                        <p><?= getSetting('owner_title', 'Full Stack Developer') ?></p>
                    </div>
                </div>

                <!-- Table of Contents (auto-generated via JS) -->
                <div class="sidebar-widget glass toc-widget" data-aos="fade-left" data-aos-delay="100">
                    <h3 class="widget-title">Table of Contents</h3>
                    <nav class="toc-nav" id="toc-nav"></nav>
                </div>

                <!-- Related Posts -->
                <?php if (!empty($relatedPosts)): ?>
                <div class="sidebar-widget glass" data-aos="fade-left" data-aos-delay="200">
                    <h3 class="widget-title"><?= lang('blog_related') ?></h3>
                    <div class="related-posts-list">
                        <?php foreach ($relatedPosts as $related): ?>
                        <a href="<?= baseUrl('blog/detail/' . $related['slug']) ?>" class="related-post-item">
                            <div class="related-thumb">
                                <img src="<?= !empty($related['thumbnail']) ? uploadUrl($related['thumbnail']) : asset('images/blog-placeholder.jpg') ?>" 
                                     alt="<?= xssClean($related['title']) ?>" loading="lazy">
                            </div>
                            <div class="related-info">
                                <h4><?= truncateText(xssClean($related['title']), 50) ?></h4>
                                <span><?= formatDate($related['published_at']) ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </aside>
        </div>
    </div>
</section>

<?php include TEMPLATES_PATH . '/footer.php'; ?>
