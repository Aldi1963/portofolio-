<?php
/**
 * Admin - Projects List (with Bulk Delete)
 */
$adminPage = 'projects';
$adminTitle = 'Projects';

// Handle single delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (!verifyCsrfToken($_GET['token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        try {
            $images = db()->fetchAll("SELECT image_path FROM project_images WHERE project_id = ?", [$_GET['delete']]);
            foreach ($images as $img) { deleteFile($img['image_path']); }
            $project = db()->fetch("SELECT thumbnail FROM projects WHERE id = ?", [$_GET['delete']]);
            if ($project && $project['thumbnail']) deleteFile($project['thumbnail']);
            db()->delete('project_images', 'project_id = ?', [$_GET['delete']]);
            db()->delete('projects', 'id = ?', [$_GET['delete']]);
            setFlash('success', 'Project deleted successfully.');
        } catch (Exception $e) {
            setFlash('error', 'Failed to delete project.');
        }
    }
    redirect(baseUrl('admin/projects'));
}

// Handle bulk delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/projects'));
    }
    
    $selectedIds = $_POST['selected_ids'] ?? [];
    $action = $_POST['bulk_action'];
    
    if (!empty($selectedIds) && is_array($selectedIds) && $action === 'delete') {
        $count = 0;
        foreach ($selectedIds as $id) {
            $id = (int)$id;
            try {
                $images = db()->fetchAll("SELECT image_path FROM project_images WHERE project_id = ?", [$id]);
                foreach ($images as $img) { deleteFile($img['image_path']); }
                $project = db()->fetch("SELECT thumbnail FROM projects WHERE id = ?", [$id]);
                if ($project && $project['thumbnail']) deleteFile($project['thumbnail']);
                db()->delete('project_images', 'project_id = ?', [$id]);
                db()->delete('projects', 'id = ?', [$id]);
                $count++;
            } catch (Exception $e) {}
        }
        setFlash('success', $count . ' project(s) deleted successfully.');
    } else {
        setFlash('error', 'No items selected or invalid action.');
    }
    redirect(baseUrl('admin/projects'));
}

// Get projects
$currentPage = (int)(get('page') ?: 1);
$perPage = 10;

try {
    $total = db()->count('projects');
    $pagination = paginate($total, $perPage, $currentPage);
    $projects = db()->fetchAll(
        "SELECT p.*, c.name as category_name FROM projects p 
         LEFT JOIN categories c ON p.category_id = c.id 
         ORDER BY p.created_at DESC 
         LIMIT $perPage OFFSET {$pagination['offset']}"
    );
} catch (Exception $e) {
    $projects = [];
    $pagination = paginate(0, $perPage, 1);
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="admin-toolbar">
    <div class="toolbar-left">
        <p class="toolbar-info">Showing <?= count($projects) ?> of <?= $total ?? 0 ?> projects</p>
    </div>
    <div class="toolbar-right">
        <a href="<?= baseUrl('admin/projects/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Project
        </a>
    </div>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <?php if (empty($projects)): ?>
        <div class="empty-state-admin">
            <i class="fas fa-folder-open"></i>
            <h3>No projects yet</h3>
            <p>Create your first project to showcase your work.</p>
            <a href="<?= baseUrl('admin/projects/create') ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Project
            </a>
        </div>
        <?php else: ?>
        <form method="POST" id="bulk-form-projects">
            <?= csrfField() ?>
            
            <!-- Bulk Actions Bar -->
            <div id="bulk-bar-projects" style="display:none;padding:12px 16px;background:rgba(0,102,255,0.08);border:1px solid rgba(0,102,255,0.2);border-radius:8px;margin-bottom:16px;align-items:center;gap:12px;flex-wrap:wrap;">
                <span style="font-size:0.85rem;color:var(--accent);font-weight:600;">
                    <span id="selected-count-projects">0</span> selected
                </span>
                <input type="hidden" name="bulk_action" value="delete">
                <button type="submit" class="btn btn-primary" style="padding:6px 16px;font-size:0.8rem;background:linear-gradient(135deg,#ef4444,#dc2626);" onclick="return confirm('Delete selected projects? This cannot be undone.')">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </div>
            
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:36px;">
                                <input type="checkbox" id="select-all-projects" onchange="toggleAllProjects(this)" style="cursor:pointer;">
                            </th>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Featured</th>
                            <th>Views</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_ids[]" value="<?= $project['id'] ?>" class="row-check-project" onchange="updateBulkBarProjects()" style="cursor:pointer;">
                            </td>
                            <td>
                                <div class="table-thumb">
                                    <img src="<?= !empty($project['thumbnail']) ? uploadUrl($project['thumbnail']) : asset('images/project-placeholder.jpg') ?>" alt="">
                                </div>
                            </td>
                            <td><strong><?= xssClean($project['title']) ?></strong></td>
                            <td><span class="badge-category"><?= xssClean($project['category_name'] ?? '-') ?></span></td>
                            <td>
                                <?php if ($project['is_featured']): ?>
                                <span class="badge-success"><i class="fas fa-star"></i> Yes</span>
                                <?php else: ?>
                                <span class="badge-muted">No</span>
                                <?php endif; ?>
                            </td>
                            <td><?= formatNumber($project['views']) ?></td>
                            <td><?= formatDate($project['created_at']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?= baseUrl('admin/projects/edit?id=' . $project['id']) ?>" class="btn-action edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?= baseUrl('admin/projects?delete=' . $project['id'] . '&token=' . generateCsrfToken()) ?>" 
                                       class="btn-action delete" title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this project?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
        <?= paginationHtml($pagination, baseUrl('admin/projects')) ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleAllProjects(master) {
    document.querySelectorAll('.row-check-project').forEach(cb => cb.checked = master.checked);
    updateBulkBarProjects();
}

function updateBulkBarProjects() {
    const checked = document.querySelectorAll('.row-check-project:checked').length;
    const bar = document.getElementById('bulk-bar-projects');
    document.getElementById('selected-count-projects').textContent = checked;
    bar.style.display = checked > 0 ? 'flex' : 'none';
}
</script>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
