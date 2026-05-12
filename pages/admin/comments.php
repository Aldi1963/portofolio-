<?php
/**
 * Admin - Comment Moderation
 * List all comments with approve/reject/delete functionality
 */
$adminPage = 'comments';
$adminTitle = 'Comment Moderation';

// Handle single actions
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    if (!verifyCsrfToken($_GET['token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/comments'));
    }
    
    $commentId = (int)$_GET['id'];
    $action = $_GET['action'];
    
    switch ($action) {
        case 'approve':
            db()->update('comments', ['is_approved' => 1], 'id = ?', [$commentId]);
            logActivity('approve_comment', 'Approved comment ID: ' . $commentId);
            setFlash('success', 'Comment approved.');
            break;
        case 'reject':
            db()->update('comments', ['is_approved' => 0], 'id = ?', [$commentId]);
            logActivity('reject_comment', 'Rejected comment ID: ' . $commentId);
            setFlash('success', 'Comment rejected.');
            break;
        case 'delete':
            db()->delete('comments', 'id = ?', [$commentId]);
            logActivity('delete_comment', 'Deleted comment ID: ' . $commentId);
            setFlash('success', 'Comment deleted.');
            break;
    }
    redirect(baseUrl('admin/comments'));
}

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/comments'));
    }
    
    $selectedIds = $_POST['selected_ids'] ?? [];
    $action = $_POST['bulk_action'];
    
    if (!empty($selectedIds) && is_array($selectedIds)) {
        $ids = array_map('intval', $selectedIds);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        switch ($action) {
            case 'approve':
                db()->query("UPDATE comments SET is_approved = 1 WHERE id IN ($placeholders)", $ids);
                logActivity('bulk_approve_comments', 'Bulk approved ' . count($ids) . ' comments');
                setFlash('success', count($ids) . ' comment(s) approved.');
                break;
            case 'reject':
                db()->query("UPDATE comments SET is_approved = 0 WHERE id IN ($placeholders)", $ids);
                logActivity('bulk_reject_comments', 'Bulk rejected ' . count($ids) . ' comments');
                setFlash('success', count($ids) . ' comment(s) rejected.');
                break;
            case 'delete':
                db()->query("DELETE FROM comments WHERE id IN ($placeholders)", $ids);
                logActivity('bulk_delete_comments', 'Bulk deleted ' . count($ids) . ' comments');
                setFlash('success', count($ids) . ' comment(s) deleted.');
                break;
        }
    } else {
        setFlash('error', 'No items selected.');
    }
    redirect(baseUrl('admin/comments'));
}

// Fetch comments with blog title
$currentPage = (int)(get('page') ?: 1);
$statusFilter = get('status');

try {
    $where = '1=1';
    $params = [];
    
    if ($statusFilter === 'approved') {
        $where .= ' AND c.is_approved = 1';
    } elseif ($statusFilter === 'pending') {
        $where .= ' AND c.is_approved = 0';
    }
    
    $total = db()->fetch(
        "SELECT COUNT(*) as total FROM comments c WHERE $where",
        $params
    )['total'];
    
    $pagination = paginate($total, 15, $currentPage);
    
    $comments = db()->fetchAll(
        "SELECT c.*, b.title as blog_title, b.slug as blog_slug 
         FROM comments c 
         LEFT JOIN blogs b ON c.blog_id = b.id 
         WHERE $where 
         ORDER BY c.is_approved ASC, c.created_at DESC 
         LIMIT 15 OFFSET {$pagination['offset']}",
        $params
    );
} catch (Exception $e) {
    $comments = [];
    $pagination = paginate(0, 15, 1);
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="admin-toolbar" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div class="filter-tabs" style="display:flex;gap:8px;">
        <a href="<?= baseUrl('admin/comments') ?>" class="btn <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline' ?>" style="padding:6px 14px;font-size:0.85rem;">All</a>
        <a href="<?= baseUrl('admin/comments?status=pending') ?>" class="btn <?= $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline' ?>" style="padding:6px 14px;font-size:0.85rem;">Pending</a>
        <a href="<?= baseUrl('admin/comments?status=approved') ?>" class="btn <?= $statusFilter === 'approved' ? 'btn-primary' : 'btn-outline' ?>" style="padding:6px 14px;font-size:0.85rem;">Approved</a>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <?php if (empty($comments)): ?>
        <div class="empty-state-admin">
            <i class="fas fa-comments"></i>
            <h3>No comments found</h3>
            <p>Comments from blog posts will appear here for moderation.</p>
        </div>
        <?php else: ?>
        <form method="POST" id="bulk-form">
            <?= csrfField() ?>
            
            <!-- Bulk Actions Bar -->
            <div class="bulk-actions-bar" id="bulk-bar" style="display:none;padding:12px 16px;background:rgba(0,102,255,0.08);border:1px solid rgba(0,102,255,0.2);border-radius:8px;margin-bottom:16px;align-items:center;gap:12px;flex-wrap:wrap;">
                <span class="bulk-count" style="font-size:0.85rem;color:var(--accent);font-weight:600;">
                    <span id="selected-count">0</span> selected
                </span>
                <select name="bulk_action" class="bulk-select" style="padding:6px 12px;background:var(--bg-tertiary);border:1px solid var(--border);border-radius:6px;color:var(--text-primary);font-size:0.85rem;">
                    <option value="">Choose Action...</option>
                    <option value="approve">Approve Selected</option>
                    <option value="reject">Reject Selected</option>
                    <option value="delete">Delete Selected</option>
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
                            <th>Blog Post</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Comment</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_ids[]" value="<?= $comment['id'] ?>" class="row-check" onchange="updateBulkBar()" style="cursor:pointer;">
                            </td>
                            <td>
                                <?php if ($comment['is_approved']): ?>
                                <span class="badge-success" style="padding:3px 8px;border-radius:4px;font-size:0.75rem;background:rgba(16,185,129,0.1);color:#10b981;">Approved</span>
                                <?php else: ?>
                                <span class="badge-warning" style="padding:3px 8px;border-radius:4px;font-size:0.75rem;background:rgba(245,158,11,0.1);color:#f59e0b;">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="font-size:0.85rem;"><?= truncateText(xssClean($comment['blog_title'] ?? 'Unknown'), 30) ?></strong>
                            </td>
                            <td><?= xssClean($comment['name']) ?></td>
                            <td><span style="font-size:0.8rem;color:var(--text-muted);"><?= xssClean($comment['email']) ?></span></td>
                            <td><span style="font-size:0.85rem;"><?= truncateText(xssClean($comment['content']), 50) ?></span></td>
                            <td><span style="font-size:0.8rem;"><?= timeAgo($comment['created_at']) ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <?php if (!$comment['is_approved']): ?>
                                    <a href="<?= baseUrl('admin/comments?action=approve&id=' . $comment['id'] . '&token=' . generateCsrfToken()) ?>" 
                                       class="btn-action view" title="Approve"><i class="fas fa-check"></i></a>
                                    <?php else: ?>
                                    <a href="<?= baseUrl('admin/comments?action=reject&id=' . $comment['id'] . '&token=' . generateCsrfToken()) ?>" 
                                       class="btn-action edit" title="Reject"><i class="fas fa-times"></i></a>
                                    <?php endif; ?>
                                    <a href="<?= baseUrl('admin/comments?action=delete&id=' . $comment['id'] . '&token=' . generateCsrfToken()) ?>" 
                                       class="btn-action delete" onclick="return confirm('Delete this comment?')" title="Delete"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
        <?= paginationHtml($pagination, baseUrl('admin/comments')) ?>
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
    if (action === 'delete') return confirm('Delete ' + count + ' selected comment(s)? This cannot be undone.');
    return true;
}
</script>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
