<?php
/**
 * Admin - Edit Blog Post
 */
$adminPage = 'blog';
$adminTitle = 'Edit Post';

$postId = (int)(get('id') ?: 0);
if (!$postId) redirect(baseUrl('admin/blog'));

try {
    $post = db()->fetch("SELECT * FROM blogs WHERE id = ?", [$postId]);
    $categories = db()->fetchAll("SELECT * FROM categories WHERE type = 'blog' AND is_active = 1 ORDER BY sort_order ASC");
} catch (Exception $e) {
    redirect(baseUrl('admin/blog'));
}

if (!$post) { setFlash('error', 'Post not found.'); redirect(baseUrl('admin/blog')); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) { setFlash('error', 'Invalid token.'); redirect(baseUrl('admin/blog/edit?id=' . $postId)); }
    
    $title = post('title');
    $content = sanitizeHtml($_POST['content'] ?? '');
    $status = post('status') ?: 'draft';
    
    $thumbnail = $post['thumbnail'];
    if (!empty($_FILES['thumbnail']['tmp_name'])) {
        $upload = uploadFile($_FILES['thumbnail'], 'blog');
        if ($upload['success']) {
            if ($post['thumbnail']) deleteFile($post['thumbnail']);
            $thumbnail = $upload['path'];
        }
    }
    
    $publishedAt = $post['published_at'];
    if ($status === 'published') {
        $scheduledDate = post('published_at');
        if (!empty($scheduledDate)) {
            $publishedAt = $scheduledDate;
        } elseif (empty($post['published_at'])) {
            $publishedAt = date('Y-m-d H:i:s');
        }
    }
    
    try {
        db()->update('blogs', [
            'category_id' => (int)post('category_id') ?: null,
            'title' => $title,
            'excerpt' => post('excerpt'),
            'content' => $content,
            'thumbnail' => $thumbnail,
            'tags' => post('tags'),
            'meta_title' => post('meta_title'),
            'meta_description' => post('meta_description'),
            'status' => $status,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'published_at' => $publishedAt,
        ], 'id = ?', [$postId]);
        logActivity('edit_blog', 'Updated blog post: ' . $title);
        setFlash('success', 'Post updated!');
        redirect(baseUrl('admin/blog'));
    } catch (Exception $e) {
        setFlash('error', 'Update failed.');
        redirect(baseUrl('admin/blog/edit?id=' . $postId));
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
                    <input type="text" name="title" value="<?= xssClean($post['title']) ?>" required>
                </div>
                <div class="form-group col-4">
                    <label>Category</label>
                    <select name="category_id">
                        <option value="">Select</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $post['category_id'] ? 'selected' : '' ?>><?= xssClean($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Excerpt</label>
                <textarea name="excerpt" rows="3"><?= xssClean($post['excerpt'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Content *</label>
                <textarea name="content" rows="15" class="richtext"><?= $post['content'] ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group col-6">
                    <label>Tags</label>
                    <input type="text" name="tags" value="<?= xssClean($post['tags'] ?? '') ?>">
                </div>
                <div class="form-group col-3">
                    <label>Status</label>
                    <select name="status" id="post-status" onchange="toggleScheduleField()">
                        <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                        <option value="archived" <?= $post['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>
                <div class="form-group col-3">
                    <label>Thumbnail</label>
                    <input type="file" name="thumbnail" accept="image/*" class="file-input">
                    <?php if ($post['thumbnail']): ?>
                    <img src="<?= uploadUrl($post['thumbnail']) ?>" style="max-height:60px;margin-top:5px;border-radius:4px;">
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group" id="schedule-field" style="<?= $post['status'] === 'published' ? '' : 'display:none;' ?>">
                <label><i class="fas fa-calendar-alt"></i> Scheduled Publish Date/Time</label>
                <input type="datetime-local" name="published_at" 
                       value="<?= !empty($post['published_at']) ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : '' ?>">
                <small class="form-help" style="display:flex;align-items:center;gap:4px;margin-top:4px;font-size:0.78rem;color:var(--text-muted,#6b6b80);"><i class="fas fa-info-circle"></i> Leave empty to publish immediately, or set a future date for scheduled publishing.</small>
            </div>
            
            <div class="form-row">
                <div class="form-group col-6">
                    <label>Meta Title</label>
                    <input type="text" name="meta_title" value="<?= xssClean($post['meta_title'] ?? '') ?>">
                </div>
                <div class="form-group col-6">
                    <label>Meta Description</label>
                    <input type="text" name="meta_description" value="<?= xssClean($post['meta_description'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_featured" value="1" <?= $post['is_featured'] ? 'checked' : '' ?>>
                    <span>Featured</span>
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Post</button>
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
