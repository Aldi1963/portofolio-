<?php
/**
 * Admin - Activity Log
 * Shows recent user activity
 */
if (!hasRole('admin')) {
    setFlash('error', 'Access denied. Admin only.');
    redirect(baseUrl('admin/dashboard'));
}

$adminPage = 'activity-log';
$adminTitle = 'Activity Log';

// Handle clear log
if (isset($_GET['clear']) && $_GET['clear'] === '1') {
    if (!verifyCsrfToken($_GET['token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/activity-log'));
    }
    
    try {
        db()->query("DELETE FROM activity_log");
        setFlash('success', 'Activity log cleared.');
    } catch (Exception $e) {
        setFlash('error', 'Failed to clear log.');
    }
    redirect(baseUrl('admin/activity-log'));
}

$currentPage = (int)(get('page') ?: 1);

try {
    $total = db()->count('activity_log');
    $pagination = paginate($total, 20, $currentPage);
    
    $activities = db()->fetchAll(
        "SELECT a.*, u.full_name, u.username 
         FROM activity_log a 
         LEFT JOIN users u ON a.user_id = u.id 
         ORDER BY a.created_at DESC 
         LIMIT 20 OFFSET {$pagination['offset']}"
    );
} catch (Exception $e) {
    $activities = [];
    $pagination = paginate(0, 20, 1);
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="admin-toolbar" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <p style="margin:0;font-size:0.85rem;color:var(--text-muted);">
        <i class="fas fa-info-circle"></i> Showing the latest activities in your admin panel.
    </p>
    <?php if (!empty($activities)): ?>
    <a href="<?= baseUrl('admin/activity-log?clear=1&token=' . generateCsrfToken()) ?>" 
       class="btn btn-outline" style="font-size:0.8rem;" 
       onclick="return confirm('Clear all activity logs? This cannot be undone.')">
        <i class="fas fa-trash"></i> Clear Log
    </a>
    <?php endif; ?>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <?php if (empty($activities)): ?>
        <div class="empty-state-admin">
            <i class="fas fa-history"></i>
            <h3>No activity yet</h3>
            <p>User actions will be logged here.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td>
                            <strong style="font-size:0.85rem;"><?= xssClean($activity['full_name'] ?? $activity['username'] ?? 'System') ?></strong>
                        </td>
                        <td>
                            <?php
                            $actionColors = [
                                'login' => '#10b981',
                                'logout' => '#6b7280',
                                'create_project' => '#0066ff',
                                'edit_project' => '#f59e0b',
                                'delete_project' => '#ef4444',
                                'create_blog' => '#0066ff',
                                'edit_blog' => '#f59e0b',
                                'delete_blog' => '#ef4444',
                                'save_settings' => '#8b5cf6',
                            ];
                            $color = $actionColors[$activity['action']] ?? '#6b7280';
                            ?>
                            <span style="padding:3px 8px;border-radius:4px;font-size:0.75rem;background:<?= $color ?>15;color:<?= $color ?>;">
                                <?= xssClean($activity['action']) ?>
                            </span>
                        </td>
                        <td><span style="font-size:0.85rem;"><?= truncateText(xssClean($activity['description']), 60) ?></span></td>
                        <td><span style="font-size:0.8rem;color:var(--text-muted);"><?= xssClean($activity['ip_address']) ?></span></td>
                        <td><span style="font-size:0.8rem;"><?= timeAgo($activity['created_at']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginationHtml($pagination, baseUrl('admin/activity-log')) ?>
        <?php endif; ?>
    </div>
</div>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
