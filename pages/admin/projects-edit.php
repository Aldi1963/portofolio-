<?php
/**
 * Admin - Edit Project
 */
$adminPage = 'projects';
$adminTitle = 'Edit Project';

$projectId = (int)(get('id') ?: 0);
if (!$projectId) redirect(baseUrl('admin/projects'));

try {
    $project = db()->fetch("SELECT * FROM projects WHERE id = ?", [$projectId]);
    $projectImages = db()->fetchAll("SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order ASC", [$projectId]);
    $categories = db()->fetchAll("SELECT * FROM categories WHERE type = 'project' AND is_active = 1 ORDER BY sort_order ASC");
} catch (Exception $e) {
    setFlash('error', 'Project not found.');
    redirect(baseUrl('admin/projects'));
}

if (!$project) {
    setFlash('error', 'Project not found.');
    redirect(baseUrl('admin/projects'));
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/projects/edit?id=' . $projectId));
    }
    
    $title = post('title');
    $categoryId = (int)post('category_id');
    $shortDesc = post('short_description');
    $description = sanitizeHtml($_POST['description'] ?? '');
    $demoUrl = post('demo_url');
    $githubUrl = post('github_url');
    $technologies = post('technologies');
    $clientName = post('client_name');
    $projectDate = post('project_date');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($title)) {
        setFlash('error', 'Project title is required.');
        redirect(baseUrl('admin/projects/edit?id=' . $projectId));
    }
    
    // Handle thumbnail
    $thumbnail = $project['thumbnail'];
    if (!empty($_FILES['thumbnail']['tmp_name'])) {
        $upload = uploadFile($_FILES['thumbnail'], 'projects');
        if ($upload['success']) {
            if (!empty($project['thumbnail'])) deleteFile($project['thumbnail']);
            $thumbnail = $upload['path'];
        }
    }
    
    try {
        db()->update('projects', [
            'category_id' => $categoryId ?: null,
            'title' => $title,
            'short_description' => $shortDesc,
            'description' => $description,
            'thumbnail' => $thumbnail,
            'demo_url' => $demoUrl,
            'github_url' => $githubUrl,
            'technologies' => $technologies,
            'client_name' => $clientName,
            'project_date' => $projectDate ?: null,
            'is_featured' => $isFeatured,
            'is_active' => $isActive,
        ], 'id = ?', [$projectId]);
        
        // Handle new images
        if (!empty($_FILES['images']['tmp_name'][0])) {
            $sortOrder = count($projectImages);
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {
                if (!empty($tmp)) {
                    $file = [
                        'tmp_name' => $tmp,
                        'name' => $_FILES['images']['name'][$key],
                        'size' => $_FILES['images']['size'][$key],
                        'error' => $_FILES['images']['error'][$key],
                    ];
                    $imgUpload = uploadFile($file, 'projects');
                    if ($imgUpload['success']) {
                        db()->insert('project_images', [
                            'project_id' => $projectId,
                            'image_path' => $imgUpload['path'],
                            'sort_order' => $sortOrder++
                        ]);
                    }
                }
            }
        }
        
        setFlash('success', 'Project updated successfully!');
        redirect(baseUrl('admin/projects'));
    } catch (Exception $e) {
        setFlash('error', 'Failed to update project.');
        redirect(baseUrl('admin/projects/edit?id=' . $projectId));
    }
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<div class="admin-toolbar">
    <a href="<?= baseUrl('admin/projects') ?>" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Projects
    </a>
</div>

<div class="dashboard-card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <?= csrfField() ?>
            
            <div class="form-row">
                <div class="form-group col-8">
                    <label for="title">Project Title *</label>
                    <input type="text" id="title" name="title" value="<?= xssClean($project['title']) ?>" required>
                </div>
                <div class="form-group col-4">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $project['category_id'] ? 'selected' : '' ?>><?= xssClean($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="short_description">Short Description</label>
                <input type="text" id="short_description" name="short_description" value="<?= xssClean($project['short_description'] ?? '') ?>" maxlength="300">
            </div>
            
            <div class="form-group">
                <label for="description">Full Description *</label>
                <textarea id="description" name="description" rows="10" class="richtext"><?= $project['description'] ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="demo_url">Demo URL</label>
                    <input type="url" id="demo_url" name="demo_url" value="<?= xssClean($project['demo_url'] ?? '') ?>">
                </div>
                <div class="form-group col-6">
                    <label for="github_url">GitHub URL</label>
                    <input type="url" id="github_url" name="github_url" value="<?= xssClean($project['github_url'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="technologies">Technologies</label>
                    <input type="text" id="technologies" name="technologies" value="<?= xssClean($project['technologies'] ?? '') ?>">
                </div>
                <div class="form-group col-3">
                    <label for="client_name">Client Name</label>
                    <input type="text" id="client_name" name="client_name" value="<?= xssClean($project['client_name'] ?? '') ?>">
                </div>
                <div class="form-group col-3">
                    <label for="project_date">Project Date</label>
                    <input type="date" id="project_date" name="project_date" value="<?= $project['project_date'] ?? '' ?>">
                </div>
            </div>
            
            <!-- Current Thumbnail -->
            <?php if (!empty($project['thumbnail'])): ?>
            <div class="form-group">
                <label>Current Thumbnail</label>
                <div class="current-image">
                    <img src="<?= uploadUrl($project['thumbnail']) ?>" alt="Current thumbnail" style="max-height:150px;border-radius:8px;">
                </div>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="thumbnail">Change Thumbnail</label>
                <input type="file" id="thumbnail" name="thumbnail" accept="image/*" class="file-input">
            </div>
            
            <!-- Current Gallery -->
            <?php if (!empty($projectImages)): ?>
            <div class="form-group">
                <label>Current Gallery</label>
                <div class="gallery-grid-admin">
                    <?php foreach ($projectImages as $img): ?>
                    <div class="gallery-item-admin">
                        <img src="<?= uploadUrl($img['image_path']) ?>" alt="">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="images">Add More Images</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple class="file-input">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_featured" value="1" <?= $project['is_featured'] ? 'checked' : '' ?>>
                        <span>Featured Project</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" <?= $project['is_active'] ? 'checked' : '' ?>>
                        <span>Active (Visible on site)</span>
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Project</button>
                <a href="<?= baseUrl('admin/projects') ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
