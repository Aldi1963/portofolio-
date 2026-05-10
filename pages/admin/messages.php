<?php
/**
 * Admin - Messages
 */
$adminPage = 'messages';
$adminTitle = 'Messages';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (verifyCsrfToken($_GET['token'] ?? '')) {
        db()->delete('contacts', 'id = ?', [$_GET['delete']]);
        setFlash('success', 'Message deleted.');
    }
    redirect(baseUrl('admin/messages'));
}

// Handle mark as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    db()->update('contacts', ['is_read' => 1], 'id = ?', [$_GET['read']]);
    redirect(baseUrl('admin/messages'));
}

$currentPage = (int)(get('page') ?: 1);
try {
    $total = db()->count('contacts');
    $pagination = paginate($total, 15, $currentPage);
    $messages = db()->fetchAll("SELECT * FROM contacts ORDER BY is_read ASC, created_at DESC LIMIT 15 OFFSET {$pagination['offset']}");
} catch (Exception $e) {
    $messages = [];
    $pagination = paginate(0, 15, 1);
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="dashboard-card">
    <div class="card-body">
        <?php if (empty($messages)): ?>
        <div class="empty-state-admin">
            <i class="fas fa-envelope-open"></i>
            <h3>No messages</h3>
            <p>Messages from your contact form will appear here.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                    <tr class="<?= !$msg['is_read'] ? 'row-unread' : '' ?>">
                        <td>
                            <?php if (!$msg['is_read']): ?>
                            <span class="badge-warning">New</span>
                            <?php else: ?>
                            <span class="badge-muted">Read</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= xssClean($msg['name']) ?></strong></td>
                        <td><?= xssClean($msg['email']) ?></td>
                        <td><?= truncateText(xssClean($msg['subject']), 30) ?></td>
                        <td><?= timeAgo($msg['created_at']) ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="<?= baseUrl('admin/messages/view?id=' . $msg['id']) ?>" class="btn-action view" title="View"><i class="fas fa-eye"></i></a>
                                <a href="mailto:<?= $msg['email'] ?>" class="btn-action edit" title="Reply"><i class="fas fa-reply"></i></a>
                                <a href="<?= baseUrl('admin/messages?delete=' . $msg['id'] . '&token=' . generateCsrfToken()) ?>" 
                                   class="btn-action delete" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= paginationHtml($pagination, baseUrl('admin/messages')) ?>
        <?php endif; ?>
    </div>
</div>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
