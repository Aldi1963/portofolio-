<?php
/**
 * Admin - Create Blog Post
 */
$adminPage = 'blog';
$adminTitle = 'New Blog Post';

try {
    $categories = db()->fetchAll("SELECT * FROM categories WHERE type = 'blog' AND is_active = 1 ORDER BY sort_order ASC");
} catch (Exception $e) {
    $categories = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/blog/create'));
    }
    
    $title = post('title');
    $categoryId = (int)post('category_id');
    $excerpt = post('excerpt');
    $content = sanitizeHtml($_POST['content'] ?? '');
    $tags = post('tags');
    $metaTitle = post('meta_title');
    $metaDesc = post('meta_description');
    $status = post('status') ?: 'draft';
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    
    if (empty($title) || empty($content)) {
        setFlash('error', 'Title and content are required.');
        redirect(baseUrl('admin/blog/create'));
    }
    
    $slug = createSlug($title);
    $existing = db()->fetch("SELECT id FROM blogs WHERE slug = ?", [$slug]);
    if ($existing) $slug .= '-' . time();
    
    $thumbnail = '';
    if (!empty($_FILES['thumbnail']['tmp_name'])) {
        $upload = uploadFile($_FILES['thumbnail'], 'blog');
        if ($upload['success']) $thumbnail = $upload['path'];
    }
    
    try {
        $publishedAt = null;
        if ($status === 'published') {
            $scheduledDate = post('published_at');
            $publishedAt = !empty($scheduledDate) ? $scheduledDate : date('Y-m-d H:i:s');
        }
        
        db()->insert('blogs', [
            'category_id' => $categoryId ?: null,
            'user_id' => $_SESSION['user_id'],
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'thumbnail' => $thumbnail,
            'tags' => $tags,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDesc,
            'status' => $status,
            'is_featured' => $isFeatured,
            'published_at' => $publishedAt,
        ]);
        logActivity('create_blog', 'Created blog post: ' . $title);
        setFlash('success', 'Blog post created successfully!');
        redirect(baseUrl('admin/blog'));
    } catch (Exception $e) {
        setFlash('error', 'Failed to create post.');
        redirect(baseUrl('admin/blog/create'));
    }
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="admin-toolbar">
    <a href="<?= baseUrl('admin/blog') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <?= csrfField() ?>
            
            <div class="form-row">
                <div class="form-group col-8">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="Article title">
                </div>
                <div class="form-group col-4">
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= xssClean($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Excerpt</label>
                <textarea name="excerpt" rows="3" placeholder="Brief summary of the article"></textarea>
            </div>
            
            <div class="form-group">
                <label>Content *</label>
                <textarea name="content" rows="15" class="richtext" placeholder="Write your article..."></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group col-6">
                    <label>Tags (comma-separated)</label>
                    <input type="text" name="tags" placeholder="PHP, Laravel, Tutorial">
                </div>
                <div class="form-group col-3">
                    <label>Status</label>
                    <select name="status" id="post-status" onchange="toggleScheduleField()">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
                <div class="form-group col-3">
                    <label>Thumbnail</label>
                    <input type="file" name="thumbnail" accept="image/*" class="file-input">
                </div>
            </div>
            
            <div class="form-group" id="schedule-field" style="display:none;">
                <label><i class="fas fa-calendar-alt"></i> Schedule Publish Date/Time</label>
                <input type="datetime-local" name="published_at" placeholder="Leave empty to publish immediately">
                <small class="form-help"><i class="fas fa-info-circle"></i> Leave empty to publish immediately, or set a future date for scheduled publishing.</small>
            </div>
            
            <div class="form-row">
                <div class="form-group col-6">
                    <label>Meta Title (SEO)</label>
                    <input type="text" name="meta_title" placeholder="SEO title (optional)">
                </div>
                <div class="form-group col-6">
                    <label>Meta Description (SEO)</label>
                    <input type="text" name="meta_description" placeholder="SEO description (optional)">
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_featured" value="1">
                    <span>Featured Post</span>
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Publish Post</button>
                <a href="<?= baseUrl('admin/blog') ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleScheduleField() {
    const status = document.getElementById('post-status').value;
    const field = document.getElementById('schedule-field');
    field.style.display = status === 'published' ? 'block' : 'none';
}
</script>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
