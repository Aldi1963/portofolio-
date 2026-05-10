<?php
/**
 * Admin - Projects List
 */
$adminPage = 'projects';
$adminTitle = 'Projects';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (!verifyCsrfToken($_GET['token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
    } else {
        try {
            // Delete project images first
            $images = db()->fetchAll("SELECT image_path FROM project_images WHERE project_id = ?", [$_GET['delete']]);
            foreach ($images as $img) {
                deleteFile($img['image_path']);
            }
            // Delete thumbnail
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
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
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
        <?= paginationHtml($pagination, baseUrl('admin/projects')) ?>
        <?php endif; ?>
    </div>
</div>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
