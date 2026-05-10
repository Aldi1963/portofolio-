<?php
/**
 * Admin - View Message
 */
$adminPage = 'messages';
$adminTitle = 'View Message';

$msgId = (int)(get('id') ?: 0);
if (!$msgId) redirect(baseUrl('admin/messages'));

try {
    $message = db()->fetch("SELECT * FROM contacts WHERE id = ?", [$msgId]);
    if (!$message) { redirect(baseUrl('admin/messages')); }
    // Mark as read
    if (!$message['is_read']) {
        db()->update('contacts', ['is_read' => 1], 'id = ?', [$msgId]);
    }
} catch (Exception $e) {
    redirect(baseUrl('admin/messages'));
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="admin-toolbar">
    <a href="<?= baseUrl('admin/messages') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
    <a href="mailto:<?= $message['email'] ?>?subject=Re: <?= urlencode($message['subject']) ?>" class="btn btn-primary">
        <i class="fas fa-reply"></i> Reply via Email
    </a>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <div class="message-detail">
            <div class="message-detail-header">
                <h2><?= xssClean($message['subject']) ?></h2>
                <span class="message-date"><?= formatDate($message['created_at'], 'd M Y H:i') ?></span>
            </div>
            <div class="message-detail-meta">
                <p><strong>From:</strong> <?= xssClean($message['name']) ?> &lt;<?= xssClean($message['email']) ?>&gt;</p>
                <?php if ($message['phone']): ?>
                <p><strong>Phone:</strong> <?= xssClean($message['phone']) ?></p>
                <?php endif; ?>
                <p><strong>IP:</strong> <?= xssClean($message['ip_address'] ?? 'N/A') ?></p>
            </div>
            <div class="message-detail-body">
                <?= nl2br(xssClean($message['message'])) ?>
            </div>
        </div>
    </div>
</div>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
