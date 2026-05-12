<?php
/**
 * Admin - Testimonials
 */
$adminPage = 'testimonials';
$adminTitle = 'Testimonials';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (verifyCsrfToken($_GET['token'] ?? '')) {
        $t = db()->fetch("SELECT client_avatar FROM testimonials WHERE id = ?", [$_GET['delete']]);
        if ($t && $t['client_avatar']) deleteFile($t['client_avatar']);
        db()->delete('testimonials', 'id = ?', [$_GET['delete']]);
        setFlash('success', 'Testimonial deleted.');
    }
    redirect(baseUrl('admin/testimonials'));
}

try { $testimonials = db()->fetchAll("SELECT * FROM testimonials ORDER BY sort_order ASC"); } catch (Exception $e) { $testimonials = []; }

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="admin-toolbar">
    <div class="toolbar-right">
        <a href="<?= baseUrl('admin/testimonials/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Testimonial</a>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <?php if (empty($testimonials)): ?>
        <div class="empty-state-admin"><i class="fas fa-quote-right"></i><h3>No testimonials</h3></div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th>Client</th><th>Company</th><th>Rating</th><th>Active</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($testimonials as $t): ?>
                    <tr>
                        <td><strong><?= xssClean($t['client_name']) ?></strong></td>
                        <td><?= xssClean($t['client_company'] ?? '-') ?></td>
                        <td><?php for($i=0;$i<$t['rating'];$i++) echo '<i class="fas fa-star" style="color:#fbbf24"></i>'; ?></td>
                        <td><?= $t['is_active'] ? '<span class="badge-success">Yes</span>' : '<span class="badge-muted">No</span>' ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?= baseUrl('admin/testimonials/edit?id=' . $t['id']) ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                <a href="<?= baseUrl('admin/testimonials?delete=' . $t['id'] . '&token=' . generateCsrfToken()) ?>" class="btn-action delete" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
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
