<?php
/**
 * Admin - Create Service
 */
$adminPage = 'services';
$adminTitle = 'Add Service';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) { setFlash('error', 'Invalid token.'); redirect(baseUrl('admin/services/create')); }
    
    $title = post('title');
    if (empty($title)) { setFlash('error', 'Title required.'); redirect(baseUrl('admin/services/create')); }
    
    try {
        db()->insert('services', [
            'title' => $title,
            'slug' => createSlug($title),
            'description' => post('description'),
            'icon' => post('icon') ?: 'fas fa-code',
            'price' => (float)post('price') ?: null,
            'price_unit' => post('price_unit') ?: 'project',
            'features' => post('features'),
            'is_popular' => isset($_POST['is_popular']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int)post('sort_order'),
        ]);
        setFlash('success', 'Service created!');
        redirect(baseUrl('admin/services'));
    } catch (Exception $e) {
        setFlash('error', 'Failed.');
        redirect(baseUrl('admin/services/create'));
    }
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="admin-toolbar">
    <a href="<?= baseUrl('admin/services') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <form method="POST" class="admin-form">
            <?= csrfField() ?>
            <div class="form-row">
                <div class="form-group col-6"><label>Title *</label><input type="text" name="title" required></div>
                <div class="form-group col-3"><label>Icon (FontAwesome)</label><input type="text" name="icon" placeholder="fas fa-code"></div>
                <div class="form-group col-3"><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>
            </div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="4"></textarea></div>
            <div class="form-row">
                <div class="form-group col-4"><label>Price</label><input type="number" name="price" step="0.01"></div>
                <div class="form-group col-4"><label>Price Unit</label><input type="text" name="price_unit" value="project" placeholder="project/month/hour"></div>
                <div class="form-group col-4"><label>Features (comma-separated)</label><input type="text" name="features" placeholder="Feature 1,Feature 2"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label class="checkbox-label"><input type="checkbox" name="is_popular" value="1"><span>Popular</span></label></div>
                <div class="form-group"><label class="checkbox-label"><input type="checkbox" name="is_active" value="1" checked><span>Active</span></label></div>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                <a href="<?= baseUrl('admin/services') ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
