<?php
/**
 * Admin - Categories CRUD
 * Manage project and blog categories
 */
$adminPage = 'categories';
$adminTitle = 'Categories';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (!verifyCsrfToken($_GET['token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/categories'));
    }
    
    $catId = (int)$_GET['delete'];
    try {
        db()->delete('categories', 'id = ?', [$catId]);
        logActivity('delete_category', 'Deleted category ID: ' . $catId);
        setFlash('success', 'Category deleted.');
    } catch (Exception $e) {
        setFlash('error', 'Cannot delete category. It may have associated items.');
    }
    redirect(baseUrl('admin/categories'));
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/categories'));
    }
    
    $name = post('name');
    $type = post('type');
    $color = post('color') ?: '#0066ff';
    $editId = (int)post('edit_id');
    
    if (empty($name) || empty($type)) {
        setFlash('error', 'Name and type are required.');
        redirect(baseUrl('admin/categories'));
    }
    
    $slug = createSlug($name);
    
    try {
        if ($editId > 0) {
            // Check slug uniqueness excluding current
            $existing = db()->fetch("SELECT id FROM categories WHERE slug = ? AND id != ?", [$slug, $editId]);
            if ($existing) $slug .= '-' . time();
            
            db()->update('categories', [
                'name' => $name,
                'slug' => $slug,
                'type' => $type,
                'color' => $color,
            ], 'id = ?', [$editId]);
            logActivity('edit_category', 'Updated category: ' . $name);
            setFlash('success', 'Category updated.');
        } else {
            // Check slug uniqueness
            $existing = db()->fetch("SELECT id FROM categories WHERE slug = ?", [$slug]);
            if ($existing) $slug .= '-' . time();
            
            db()->insert('categories', [
                'name' => $name,
                'slug' => $slug,
                'type' => $type,
                'color' => $color,
                'is_active' => 1,
                'sort_order' => 0,
            ]);
            logActivity('create_category', 'Created category: ' . $name);
            setFlash('success', 'Category created.');
        }
    } catch (Exception $e) {
        setFlash('error', 'Failed to save category.');
    }
    redirect(baseUrl('admin/categories'));
}

// Fetch categories with item count
try {
    $categories = db()->fetchAll(
        "SELECT c.*, 
            (SELECT COUNT(*) FROM projects p WHERE p.category_id = c.id) as project_count,
            (SELECT COUNT(*) FROM blogs b WHERE b.category_id = c.id) as blog_count
         FROM categories c 
         ORDER BY c.type ASC, c.sort_order ASC, c.name ASC"
    );
} catch (Exception $e) {
    $categories = [];
}

// Check if editing
$editCategory = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $editCategory = db()->fetch("SELECT * FROM categories WHERE id = ?", [(int)$_GET['edit']]);
    } catch (Exception $e) {}
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<!-- Add/Edit Form -->
<div class="dashboard-card" style="margin-bottom:20px;">
    <div class="card-header" style="padding:16px 20px;border-bottom:1px solid var(--border,rgba(255,255,255,0.08));">
        <h3 style="margin:0;font-size:1rem;">
            <i class="fas fa-<?= $editCategory ? 'edit' : 'plus' ?>"></i> 
            <?= $editCategory ? 'Edit Category' : 'Add New Category' ?>
        </h3>
    </div>
    <div class="card-body">
        <form method="POST" class="admin-form" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <?= csrfField() ?>
            <?php if ($editCategory): ?>
            <input type="hidden" name="edit_id" value="<?= $editCategory['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group" style="flex:2;min-width:200px;margin-bottom:0;">
                <label>Name *</label>
                <input type="text" name="name" required placeholder="Category name" 
                       value="<?= $editCategory ? xssClean($editCategory['name']) : '' ?>">
            </div>
            <div class="form-group" style="flex:1;min-width:140px;margin-bottom:0;">
                <label>Type *</label>
                <select name="type" required>
                    <option value="project" <?= ($editCategory && $editCategory['type'] === 'project') ? 'selected' : '' ?>>Project</option>
                    <option value="blog" <?= ($editCategory && $editCategory['type'] === 'blog') ? 'selected' : '' ?>>Blog</option>
                </select>
            </div>
            <div class="form-group" style="flex:0;min-width:80px;margin-bottom:0;">
                <label>Color</label>
                <input type="color" name="color" value="<?= $editCategory ? xssClean($editCategory['color']) : '#0066ff' ?>" 
                       style="height:38px;padding:4px;cursor:pointer;">
            </div>
            <div style="display:flex;gap:8px;margin-bottom:0;">
                <button type="submit" class="btn btn-primary" style="padding:8px 20px;">
                    <i class="fas fa-save"></i> <?= $editCategory ? 'Update' : 'Add' ?>
                </button>
                <?php if ($editCategory): ?>
                <a href="<?= baseUrl('admin/categories') ?>" class="btn btn-outline" style="padding:8px 14px;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Categories List -->
<div class="dashboard-card">
    <div class="card-body">
        <?php if (empty($categories)): ?>
        <div class="empty-state-admin">
            <i class="fas fa-tags"></i>
            <h3>No categories</h3>
            <p>Add your first category using the form above.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Color</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Type</th>
                        <th>Items</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td>
                            <span style="display:inline-block;width:20px;height:20px;border-radius:4px;background:<?= xssClean($cat['color']) ?>;"></span>
                        </td>
                        <td><strong><?= xssClean($cat['name']) ?></strong></td>
                        <td><span style="font-size:0.8rem;color:var(--text-muted);"><?= xssClean($cat['slug']) ?></span></td>
                        <td>
                            <?php if ($cat['type'] === 'project'): ?>
                            <span style="padding:3px 8px;border-radius:4px;font-size:0.75rem;background:rgba(0,102,255,0.1);color:#0066ff;">Project</span>
                            <?php else: ?>
                            <span style="padding:3px 8px;border-radius:4px;font-size:0.75rem;background:rgba(16,185,129,0.1);color:#10b981;">Blog</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $count = $cat['type'] === 'project' ? $cat['project_count'] : $cat['blog_count'];
                            ?>
                            <span style="font-size:0.85rem;"><?= $count ?> item<?= $count != 1 ? 's' : '' ?></span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?= baseUrl('admin/categories?edit=' . $cat['id']) ?>" class="btn-action edit" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="<?= baseUrl('admin/categories?delete=' . $cat['id'] . '&token=' . generateCsrfToken()) ?>" 
                                   class="btn-action delete" onclick="return confirm('Delete this category?')" title="Delete"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
