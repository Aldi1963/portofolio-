<?php
/**
 * Admin - Create Testimonial
 */
$adminPage = 'testimonials';
$adminTitle = 'Add Testimonial';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) { setFlash('error', 'Invalid token.'); redirect(baseUrl('admin/testimonials/create')); }
    
    $avatar = '';
    if (!empty($_FILES['client_avatar']['tmp_name'])) {
        $upload = uploadFile($_FILES['client_avatar'], 'testimonials');
        if ($upload['success']) $avatar = $upload['path'];
    }
    
    try {
        db()->insert('testimonials', [
            'client_name' => post('client_name'),
            'client_position' => post('client_position'),
            'client_company' => post('client_company'),
            'client_avatar' => $avatar,
            'content' => post('content'),
            'rating' => (int)post('rating') ?: 5,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int)post('sort_order'),
        ]);
        setFlash('success', 'Testimonial added!');
        redirect(baseUrl('admin/testimonials'));
    } catch (Exception $e) { setFlash('error', 'Failed.'); redirect(baseUrl('admin/testimonials/create')); }
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="admin-toolbar"><a href="<?= baseUrl('admin/testimonials') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a></div>

<div class="dashboard-card"><div class="card-body">
    <form method="POST" enctype="multipart/form-data" class="admin-form">
        <?= csrfField() ?>
        <div class="form-row">
            <div class="form-group col-4"><label>Client Name *</label><input type="text" name="client_name" required></div>
            <div class="form-group col-4"><label>Position</label><input type="text" name="client_position" placeholder="CEO"></div>
            <div class="form-group col-4"><label>Company</label><input type="text" name="client_company"></div>
        </div>
        <div class="form-group"><label>Testimonial Content *</label><textarea name="content" rows="4" required></textarea></div>
        <div class="form-row">
            <div class="form-group col-3"><label>Rating (1-5)</label><input type="number" name="rating" min="1" max="5" value="5"></div>
            <div class="form-group col-3"><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>
            <div class="form-group col-3"><label>Avatar</label><input type="file" name="client_avatar" accept="image/*" class="file-input"></div>
            <div class="form-group col-3"><label class="checkbox-label"><input type="checkbox" name="is_active" value="1" checked><span>Active</span></label></div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
            <a href="<?= baseUrl('admin/testimonials') ?>" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div></div>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
