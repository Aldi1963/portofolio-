<?php
/**
 * Admin - Blog Posts List
 */
$adminPage = 'blog';
$adminTitle = 'Blog Posts';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (verifyCsrfToken($_GET['token'] ?? '')) {
        try {
            $post = db()->fetch("SELECT thumbnail FROM blogs WHERE id = ?", [$_GET['delete']]);
            if ($post && $post['thumbnail']) deleteFile($post['thumbnail']);
            db()->delete('comments', 'blog_id = ?', [$_GET['delete']]);
            db()->delete('blogs', 'id = ?', [$_GET['delete']]);
            logActivity('delete_blog', 'Deleted blog post ID: ' . $_GET['delete']);
            setFlash('success', 'Post deleted successfully.');
        } catch (Exception $e) {
            setFlash('error', 'Failed to delete post.');
        }
    }
    redirect(baseUrl('admin/blog'));
}

$currentPage = (int)(get('page') ?: 1);
$perPage = 10;

try {
    $total = db()->count('blogs');
    $pagination = paginate($total, $perPage, $currentPage);
    $posts = db()->fetchAll(
        "SELECT b.*, c.name as category_name FROM blogs b 
         LEFT JOIN categories c ON b.category_id = c.id 
         ORDER BY b.created_at DESC 
         LIMIT $perPage OFFSET {$pagination['offset']}"
    );
} catch (Exception $e) {
    $posts = [];
    $pagination = paginate(0, $perPage, 1);
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="admin-toolbar">
    <div class="toolbar-left">
        <p class="toolbar-info">Showing <?= count($posts) ?> of <?= $total ?? 0 ?> posts</p>
    </div>
    <div class="toolbar-right">
        <a href="<?= baseUrl('admin/blog/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Post
        </a>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <?php if (empty($posts)): ?>
        <div class="empty-state-admin">
            <i class="fas fa-newspaper"></i>
            <h3>No blog posts yet</h3>
            <a href="<?= baseUrl('admin/blog/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Create Post</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Views</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><strong><?= truncateText(xssClean($post['title']), 50) ?></strong></td>
                        <td><span class="badge-category"><?= xssClean($post['category_name'] ?? '-') ?></span></td>
                        <td>
                            <span class="badge-<?= $post['status'] === 'published' ? 'success' : ($post['status'] === 'draft' ? 'warning' : 'muted') ?>">
                                <?= ucfirst($post['status']) ?>
                            </span>
                        </td>
                        <td><?= formatNumber($post['views']) ?></td>
                        <td><?= formatDate($post['created_at']) ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?= baseUrl('admin/blog/edit?id=' . $post['id']) ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                <a href="<?= baseUrl('admin/blog?delete=' . $post['id'] . '&token=' . generateCsrfToken()) ?>" 
                                   class="btn-action delete" onclick="return confirm('Delete this post?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginationHtml($pagination, baseUrl('admin/blog')) ?>
        <?php endif; ?>
    </div>
</div>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
