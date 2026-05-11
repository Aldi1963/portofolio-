<?php
/**
 * Admin - Create Project
 */
$adminPage = 'projects';
$adminTitle = 'Add Project';

// Get categories
try {
    $categories = db()->fetchAll("SELECT * FROM categories WHERE type = 'project' AND is_active = 1 ORDER BY sort_order ASC");
} catch (Exception $e) {
    $categories = [];
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken()) {
        setFlash('error', 'Invalid security token.');
        redirect(baseUrl('admin/projects/create'));
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
    
    // Validation
    if (empty($title)) {
        setFlash('error', 'Project title is required.');
        redirect(baseUrl('admin/projects/create'));
    }
    
    // Generate slug
    $slug = createSlug($title);
    $existingSlug = db()->fetch("SELECT id FROM projects WHERE slug = ?", [$slug]);
    if ($existingSlug) $slug .= '-' . time();
    
    // Handle thumbnail upload
    $thumbnail = '';
    if (!empty($_FILES['thumbnail']['tmp_name'])) {
        $upload = uploadFile($_FILES['thumbnail'], 'projects');
        if ($upload['success']) {
            $thumbnail = $upload['path'];
        } else {
            setFlash('error', $upload['message']);
            redirect(baseUrl('admin/projects/create'));
        }
    }
    
    try {
        $projectId = db()->insert('projects', [
            'category_id' => $categoryId ?: null,
            'title' => $title,
            'slug' => $slug,
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
        ]);
        
        // Handle multiple images
        if (!empty($_FILES['images']['tmp_name'][0])) {
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
                            'sort_order' => $key
                        ]);
                    }
                }
            }
        }
        
        setFlash('success', 'Project created successfully!');
        logActivity('create_project', 'Created project: ' . $title);
        redirect(baseUrl('admin/projects'));
    } catch (Exception $e) {
        setFlash('error', 'Failed to create project: ' . ($e->getMessage()));
        redirect(baseUrl('admin/projects/create'));
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
                    <input type="text" id="title" name="title" required placeholder="e.g. E-Commerce Platform">
                </div>
                <div class="form-group col-4">
                    <label for="category_id">Category</label>
                    <select id="category_id" name="category_id">
                        <option value="">Select Category</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= xssClean($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="short_description">Short Description</label>
                <input type="text" id="short_description" name="short_description" maxlength="300" placeholder="Brief project overview (max 300 chars)">
            </div>
            
            <div class="form-group">
                <label for="description">Full Description *</label>
                <textarea id="description" name="description" rows="10" class="richtext" placeholder="Detailed project description..."></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="demo_url">Demo URL</label>
                    <input type="url" id="demo_url" name="demo_url" placeholder="https://demo.example.com">
                </div>
                <div class="form-group col-6">
                    <label for="github_url">GitHub URL</label>
                    <input type="url" id="github_url" name="github_url" placeholder="https://github.com/user/repo">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-6">
                    <label for="technologies">Technologies (comma-separated)</label>
                    <input type="text" id="technologies" name="technologies" placeholder="PHP, MySQL, JavaScript, React">
                </div>
                <div class="form-group col-3">
                    <label for="client_name">Client Name</label>
                    <input type="text" id="client_name" name="client_name" placeholder="Client name">
                </div>
                <div class="form-group col-3">
                    <label for="project_date">Project Date</label>
                    <input type="date" id="project_date" name="project_date">
                </div>
            </div>
            
            <div class="form-group">
                <label for="thumbnail">Thumbnail Image</label>
                <input type="file" id="thumbnail" name="thumbnail" accept="image/*" class="file-input">
                <small class="form-help">Recommended: 800x600px, max 5MB (JPG, PNG, WebP)</small>
            </div>
            
            <div class="form-group">
                <label for="images">Additional Images</label>
                <input type="file" id="images" name="images[]" accept="image/*" multiple class="file-input">
                <small class="form-help">Select multiple images for project gallery</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_featured" value="1">
                        <span>Featured Project</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Active (Visible on site)</span>
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Project
                </button>
                <a href="<?= baseUrl('admin/projects') ?>" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
