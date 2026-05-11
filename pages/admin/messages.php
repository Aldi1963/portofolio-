<?php
/**
 * Admin - Messages (with Bulk Delete)
 */
$adminPage = 'messages';
$adminTitle = 'Messages';

// Handle single delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (verifyCsrfToken($_GET['token'] ?? '')) {
        db()->delete('contacts', 'id = ?', [$_GET['delete']]);
        setFlash('success', 'Message deleted.');
    }
    redirect(baseUrl('admin/messages'));
}

// Handle bulk delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/messages'));
    }
    
    $selectedIds = $_POST['selected_ids'] ?? [];
    $action = $_POST['bulk_action'];
    
    if (!empty($selectedIds) && is_array($selectedIds)) {
        $ids = array_map('intval', $selectedIds);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        if ($action === 'delete') {
            db()->query("DELETE FROM contacts WHERE id IN ($placeholders)", $ids);
            setFlash('success', count($ids) . ' message(s) deleted successfully.');
        } elseif ($action === 'mark_read') {
            db()->query("UPDATE contacts SET is_read = 1 WHERE id IN ($placeholders)", $ids);
            setFlash('success', count($ids) . ' message(s) marked as read.');
        } elseif ($action === 'mark_unread') {
            db()->query("UPDATE contacts SET is_read = 0 WHERE id IN ($placeholders)", $ids);
            setFlash('success', count($ids) . ' message(s) marked as unread.');
        }
    } else {
        setFlash('error', 'No items selected.');
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
        <form method="POST" id="bulk-form">
            <?= csrfField() ?>
            
            <!-- Bulk Actions Bar -->
            <div class="bulk-actions-bar" id="bulk-bar" style="display:none;padding:12px 16px;background:rgba(0,102,255,0.08);border:1px solid rgba(0,102,255,0.2);border-radius:8px;margin-bottom:16px;display:none;align-items:center;gap:12px;flex-wrap:wrap;">
                <span class="bulk-count" style="font-size:0.85rem;color:var(--accent);font-weight:600;">
                    <span id="selected-count">0</span> selected
                </span>
                <select name="bulk_action" class="bulk-select" style="padding:6px 12px;background:var(--bg-tertiary);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);font-size:0.85rem;">
                    <option value="">Choose Action...</option>
                    <option value="delete">Delete Selected</option>
                    <option value="mark_read">Mark as Read</option>
                    <option value="mark_unread">Mark as Unread</option>
                </select>
                <button type="submit" class="btn btn-primary" style="padding:6px 16px;font-size:0.8rem;" onclick="return confirmBulk()">
                    <i class="fas fa-check"></i> Apply
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:36px;">
                                <input type="checkbox" id="select-all" onchange="toggleAll(this)" style="cursor:pointer;">
                            </th>
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
                                <input type="checkbox" name="selected_ids[]" value="<?= $msg['id'] ?>" class="row-check" onchange="updateBulkBar()" style="cursor:pointer;">
                            </td>
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
                                       class="btn-action delete" onclick="return confirm('Delete this message?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
        <?= paginationHtml($pagination, baseUrl('admin/messages')) ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleAll(master) {
    document.querySelectorAll('.row-check').forEach(cb => cb.checked = master.checked);
    updateBulkBar();
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.row-check:checked').length;
    const bar = document.getElementById('bulk-bar');
    document.getElementById('selected-count').textContent = checked;
    bar.style.display = checked > 0 ? 'flex' : 'none';
}

function confirmBulk() {
    const action = document.querySelector('[name="bulk_action"]').value;
    if (!action) { alert('Please select an action.'); return false; }
    const count = document.querySelectorAll('.row-check:checked').length;
    if (count === 0) { alert('No items selected.'); return false; }
    if (action === 'delete') return confirm('Delete ' + count + ' selected message(s)? This cannot be undone.');
    return true;
}
</script>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
