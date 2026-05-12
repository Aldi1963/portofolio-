<?php
/**
 * Admin - Experience & Education CRUD
 * Manage work experience, education, and certifications (timeline)
 */
$adminPage = 'experience';
$adminTitle = 'Experience & Education';

// Handle bulk delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('bulk_action') === 'delete') {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/experience'));
    }
    
    $selectedIds = $_POST['selected_ids'] ?? [];
    if (!empty($selectedIds)) {
        $ids = array_map('intval', $selectedIds);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        try {
            db()->query("DELETE FROM experience WHERE id IN ($placeholders)", $ids);
            logActivity('bulk_delete_experience', 'Deleted ' . count($ids) . ' experience entries');
            setFlash('success', count($ids) . ' entries deleted successfully.');
        } catch (Exception $e) {
            setFlash('error', 'Failed to delete selected entries.');
        }
    } else {
        setFlash('error', 'No entries selected.');
    }
    redirect(baseUrl('admin/experience'));
}

// Handle single delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (!verifyCsrfToken($_GET['token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/experience'));
    }
    
    $deleteId = (int)$_GET['delete'];
    try {
        db()->delete('experience', 'id = ?', [$deleteId]);
        logActivity('delete_experience', 'Deleted experience ID: ' . $deleteId);
        setFlash('success', 'Entry deleted successfully.');
    } catch (Exception $e) {
        setFlash('error', 'Failed to delete entry.');
    }
    redirect(baseUrl('admin/experience'));
}

// Handle add/edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !post('bulk_action')) {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/experience'));
    }
    
    $title = post('title');
    $company = post('company');
    $location = post('location');
    $description = post('description');
    $type = post('type');
    $startDate = post('start_date');
    $endDate = post('end_date') ?: null;
    $isCurrent = isset($_POST['is_current']) ? 1 : 0;
    $sortOrder = (int)post('sort_order');
    $editId = (int)post('edit_id');
    
    // Validation
    if (empty($title) || empty($type) || empty($startDate)) {
        setFlash('error', 'Title, type, and start date are required.');
        redirect(baseUrl('admin/experience' . ($editId ? '?edit=' . $editId : '')));
    }
    
    if (!in_array($type, ['work', 'education', 'certification'])) {
        setFlash('error', 'Invalid type selected.');
        redirect(baseUrl('admin/experience'));
    }
    
    $data = [
        'title' => $title,
        'company' => $company,
        'location' => $location,
        'description' => $description,
        'type' => $type,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'is_current' => $isCurrent,
        'sort_order' => $sortOrder,
    ];
    
    try {
        if ($editId > 0) {
            db()->update('experience', $data, 'id = ?', [$editId]);
            logActivity('edit_experience', 'Updated experience: ' . $title);
            setFlash('success', 'Entry updated successfully.');
        } else {
            db()->insert('experience', $data);
            logActivity('create_experience', 'Created experience: ' . $title);
            setFlash('success', 'Entry created successfully.');
        }
    } catch (Exception $e) {
        setFlash('error', 'Failed to save entry.');
    }
    redirect(baseUrl('admin/experience'));
}

// Fetch all experience entries
try {
    $entries = db()->fetchAll("SELECT * FROM experience ORDER BY sort_order ASC, start_date DESC");
} catch (Exception $e) {
    $entries = [];
}

// Group by type
$grouped = [
    'work' => [],
    'education' => [],
    'certification' => [],
];
foreach ($entries as $entry) {
    $grouped[$entry['type']][] = $entry;
}

// Check if editing
$editEntry = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    try {
        $editEntry = db()->fetch("SELECT * FROM experience WHERE id = ?", [(int)$_GET['edit']]);
    } catch (Exception $e) {}
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<!-- Add/Edit Form -->
<div class="dashboard-card" style="margin-bottom:20px;">
    <div class="card-header" style="padding:16px 20px;border-bottom:1px solid var(--border,rgba(255,255,255,0.08));">
        <h3 style="margin:0;font-size:1rem;">
            <i class="fas fa-<?= $editEntry ? 'edit' : 'plus' ?>"></i> 
            <?= $editEntry ? 'Edit Entry' : 'Add New Entry' ?>
        </h3>
    </div>
    <div class="card-body">
        <form method="POST" class="admin-form">
            <?= csrfField() ?>
            <?php if ($editEntry): ?>
            <input type="hidden" name="edit_id" value="<?= $editEntry['id'] ?>">
            <?php endif; ?>
            
            <div class="form-row" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
                <div class="form-group" style="flex:2;min-width:200px;margin-bottom:0;">
                    <label>Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Senior Developer, Bachelor's Degree" 
                           value="<?= $editEntry ? xssClean($editEntry['title']) : '' ?>">
                </div>
                <div class="form-group" style="flex:1;min-width:160px;margin-bottom:0;">
                    <label>Company / Institution</label>
                    <input type="text" name="company" placeholder="e.g. Google, MIT" 
                           value="<?= $editEntry ? xssClean($editEntry['company']) : '' ?>">
                </div>
                <div class="form-group" style="flex:1;min-width:140px;margin-bottom:0;">
                    <label>Location</label>
                    <input type="text" name="location" placeholder="e.g. Jakarta, Remote" 
                           value="<?= $editEntry ? xssClean($editEntry['location']) : '' ?>">
                </div>
            </div>
            
            <div class="form-row" style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px;">
                <div class="form-group" style="flex:1;min-width:130px;margin-bottom:0;">
                    <label>Type *</label>
                    <select name="type" required>
                        <option value="work" <?= ($editEntry && $editEntry['type'] === 'work') ? 'selected' : '' ?>>Work</option>
                        <option value="education" <?= ($editEntry && $editEntry['type'] === 'education') ? 'selected' : '' ?>>Education</option>
                        <option value="certification" <?= ($editEntry && $editEntry['type'] === 'certification') ? 'selected' : '' ?>>Certification</option>
                    </select>
                </div>
                <div class="form-group" style="flex:1;min-width:140px;margin-bottom:0;">
                    <label>Start Date *</label>
                    <input type="date" name="start_date" required 
                           value="<?= $editEntry ? xssClean($editEntry['start_date']) : '' ?>">
                </div>
                <div class="form-group" style="flex:1;min-width:140px;margin-bottom:0;">
                    <label>End Date</label>
                    <input type="date" name="end_date" 
                           value="<?= ($editEntry && $editEntry['end_date']) ? xssClean($editEntry['end_date']) : '' ?>">
                </div>
                <div class="form-group" style="flex:0;min-width:100px;margin-bottom:0;">
                    <label>Current</label>
                    <div style="padding-top:6px;">
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-weight:normal;">
                            <input type="checkbox" name="is_current" value="1" 
                                   <?= ($editEntry && $editEntry['is_current']) ? 'checked' : '' ?>
                                   style="width:auto;">
                            <span>Active</span>
                        </label>
                    </div>
                </div>
                <div class="form-group" style="flex:0;min-width:80px;margin-bottom:0;">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" min="0" placeholder="0" 
                           value="<?= $editEntry ? (int)$editEntry['sort_order'] : '0' ?>"
                           style="width:80px;">
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom:12px;">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Brief description of role, achievements, or details..."><?= $editEntry ? xssClean($editEntry['description']) : '' ?></textarea>
            </div>
            
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary" style="padding:8px 20px;">
                    <i class="fas fa-save"></i> <?= $editEntry ? 'Update' : 'Add Entry' ?>
                </button>
                <?php if ($editEntry): ?>
                <a href="<?= baseUrl('admin/experience') ?>" class="btn btn-outline" style="padding:8px 14px;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Experience List -->
<form method="POST" id="bulkForm">
    <?= csrfField() ?>
    <input type="hidden" name="bulk_action" value="delete">
    
    <!-- Bulk Actions Toolbar -->
    <?php if (!empty($entries)): ?>
    <div class="admin-toolbar" style="margin-bottom:12px;display:flex;align-items:center;gap:12px;">
        <button type="submit" class="btn btn-outline" style="padding:6px 14px;font-size:0.85rem;" 
                onclick="return confirm('Delete all selected entries?')">
            <i class="fas fa-trash"></i> Delete Selected
        </button>
        <span style="font-size:0.85rem;color:var(--text-muted);">
            Total: <?= count($entries) ?> entries
        </span>
    </div>
    <?php endif; ?>

    <?php
    $typeLabels = [
        'work' => ['label' => 'Work Experience', 'icon' => 'fa-briefcase', 'color' => '#0066ff'],
        'education' => ['label' => 'Education', 'icon' => 'fa-graduation-cap', 'color' => '#10b981'],
        'certification' => ['label' => 'Certifications', 'icon' => 'fa-certificate', 'color' => '#f59e0b'],
    ];
    ?>

    <?php foreach ($typeLabels as $type => $meta): ?>
    <?php if (!empty($grouped[$type])): ?>
    <div class="dashboard-card" style="margin-bottom:16px;">
        <div class="card-header" style="padding:14px 20px;border-bottom:1px solid var(--border,rgba(255,255,255,0.08));">
            <h3 style="margin:0;font-size:0.95rem;display:flex;align-items:center;gap:8px;">
                <i class="fas <?= $meta['icon'] ?>" style="color:<?= $meta['color'] ?>;"></i>
                <?= $meta['label'] ?>
                <span style="font-size:0.8rem;color:var(--text-muted);font-weight:normal;">(<?= count($grouped[$type]) ?>)</span>
            </h3>
        </div>
        <div class="card-body" style="padding:0;">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:30px;"><input type="checkbox" class="select-all" data-type="<?= $type ?>"></th>
                            <th>Title</th>
                            <th>Company / Institution</th>
                            <th>Location</th>
                            <th>Date Range</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grouped[$type] as $entry): ?>
                        <tr>
                            <td><input type="checkbox" name="selected_ids[]" value="<?= $entry['id'] ?>" class="entry-checkbox type-<?= $type ?>"></td>
                            <td><strong><?= xssClean($entry['title']) ?></strong></td>
                            <td><?= xssClean($entry['company'] ?? '-') ?></td>
                            <td><?= xssClean($entry['location'] ?? '-') ?></td>
                            <td style="white-space:nowrap;">
                                <?= date('M Y', strtotime($entry['start_date'])) ?> - 
                                <?= $entry['is_current'] ? '<em>Present</em>' : ($entry['end_date'] ? date('M Y', strtotime($entry['end_date'])) : '-') ?>
                            </td>
                            <td>
                                <?php if ($entry['is_current']): ?>
                                <span class="badge-success">Current</span>
                                <?php else: ?>
                                <span class="badge-muted">Past</span>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$entry['sort_order'] ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?= baseUrl('admin/experience?edit=' . $entry['id']) ?>" class="btn-action edit" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= baseUrl('admin/experience?delete=' . $entry['id'] . '&token=' . generateCsrfToken()) ?>" 
                                       class="btn-action delete" onclick="return confirm('Delete this entry?')" title="Delete"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>

    <?php if (empty($entries)): ?>
    <div class="dashboard-card">
        <div class="card-body">
            <div class="empty-state-admin">
                <i class="fas fa-briefcase"></i>
                <h3>No experience entries</h3>
                <p>Add your first experience, education, or certification using the form above.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</form>

<!-- Select All Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.select-all').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            var type = this.dataset.type;
            document.querySelectorAll('.type-' + type).forEach(function(cb) {
                cb.checked = checkbox.checked;
            });
        });
    });
});
</script>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
