<?php
/**
 * Admin - Services List
 */
$adminPage = 'services';
$adminTitle = 'Services';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (verifyCsrfToken($_GET['token'] ?? '')) {
        db()->delete('services', 'id = ?', [$_GET['delete']]);
        setFlash('success', 'Service deleted.');
    }
    redirect(baseUrl('admin/services'));
}

try {
    $services = db()->fetchAll("SELECT * FROM services ORDER BY sort_order ASC");
} catch (Exception $e) {
    $services = [];
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="admin-toolbar">
    <div class="toolbar-right">
        <a href="<?= baseUrl('admin/services/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Service</a>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <?php if (empty($services)): ?>
        <div class="empty-state-admin">
            <i class="fas fa-concierge-bell"></i>
            <h3>No services yet</h3>
            <a href="<?= baseUrl('admin/services/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Service</a>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead><tr><th>Title</th><th>Price</th><th>Popular</th><th>Active</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($services as $svc): ?>
                    <tr>
                        <td><strong><i class="<?= $svc['icon'] ?>"></i> <?= xssClean($svc['title']) ?></strong></td>
                        <td><?= $svc['price'] ? formatCurrency($svc['price']) . '/' . $svc['price_unit'] : 'Custom' ?></td>
                        <td><?= $svc['is_popular'] ? '<span class="badge-success">Yes</span>' : '<span class="badge-muted">No</span>' ?></td>
                        <td><?= $svc['is_active'] ? '<span class="badge-success">Active</span>' : '<span class="badge-muted">Inactive</span>' ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?= baseUrl('admin/services/edit?id=' . $svc['id']) ?>" class="btn-action edit"><i class="fas fa-edit"></i></a>
                                <a href="<?= baseUrl('admin/services?delete=' . $svc['id'] . '&token=' . generateCsrfToken()) ?>" class="btn-action delete" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
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
