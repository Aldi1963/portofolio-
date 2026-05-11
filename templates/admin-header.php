<?php
/**
 * Admin Dashboard Header Template
 */
requireAuth();
$adminPage = $adminPage ?? 'dashboard';
$adminTitle = $adminTitle ?? 'Dashboard';
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $adminTitle ?> - Admin Panel</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- TinyMCE Rich Text Editor -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.querySelector('.richtext')) {
            tinymce.init({
                selector: '.richtext',
                height: 400,
                skin: 'oxide-dark',
                content_css: 'dark',
                plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
                toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | code fullscreen | removeformat help',
                menubar: 'file edit view insert format tools table help',
                branding: false,
                promotion: false,
                image_advtab: true,
                automatic_uploads: false,
                file_picker_types: 'image',
                content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; color: #e0e0e0; background: #1a1a2e; } a { color: #0066ff; }',
                setup: function(editor) {
                    editor.on('change', function() {
                        editor.save();
                    });
                }
            });
        }
    });
    </script>
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="admin-body">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="admin-sidebar">
        <div class="sidebar-header">
            <a href="<?= baseUrl('admin') ?>" class="sidebar-brand">
                &lt;<span class="brand-highlight"><?= getSetting('owner_name', 'Aldi') ?></span>/&gt;
            </a>
            <button class="sidebar-close" id="sidebar-close"><i class="fas fa-times"></i></button>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="sidebar-menu">
                <li class="menu-label">Main</li>
                <li class="menu-item <?= $adminPage === 'dashboard' ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/dashboard') ?>">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="menu-label">Content</li>
                <li class="menu-item <?= $adminPage === 'projects' ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/projects') ?>">
                        <i class="fas fa-folder-open"></i>
                        <span>Projects</span>
                    </a>
                </li>
                <li class="menu-item <?= $adminPage === 'blog' ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/blog') ?>">
                        <i class="fas fa-newspaper"></i>
                        <span>Blog Posts</span>
                    </a>
                </li>
                <li class="menu-item <?= $adminPage === 'services' ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/services') ?>">
                        <i class="fas fa-concierge-bell"></i>
                        <span>Services</span>
                    </a>
                </li>
                <li class="menu-item <?= $adminPage === 'categories' ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/categories') ?>">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </a>
                </li>
                <li class="menu-item <?= $adminPage === 'testimonials' ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/testimonials') ?>">
                        <i class="fas fa-quote-right"></i>
                        <span>Testimonials</span>
                    </a>
                </li>
                
                <li class="menu-label">Communication</li>
                <li class="menu-item <?= $adminPage === 'messages' ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/messages') ?>">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                        <?php 
                        try {
                            $unreadCount = db()->count('contacts', 'is_read = 0');
                            if ($unreadCount > 0):
                        ?>
                        <span class="badge"><?= $unreadCount ?></span>
                        <?php endif; } catch(Exception $e) {} ?>
                    </a>
                </li>
                <li class="menu-item <?= $adminPage === 'comments' ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/comments') ?>">
                        <i class="fas fa-comments"></i>
                        <span>Comments</span>
                        <?php 
                        try {
                            $pendingComments = db()->count('comments', 'is_approved = 0');
                            if ($pendingComments > 0):
                        ?>
                        <span class="badge"><?= $pendingComments ?></span>
                        <?php endif; } catch(Exception $e) {} ?>
                    </a>
                </li>
                
                <li class="menu-label">System</li>
                <li class="menu-item <?= $adminPage === 'activity-log' ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/activity-log') ?>">
                        <i class="fas fa-history"></i>
                        <span>Activity Log</span>
                    </a>
                </li>
                <li class="menu-item <?= $adminPage === 'settings' ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/settings') ?>">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= baseUrl('admin/change-password') ?>">
                        <i class="fas fa-key"></i>
                        <span>Change Password</span>
                    </a>
                </li>
                <li class="menu-item <?= $adminPage === 'backup' ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/backup') ?>">
                        <i class="fas fa-database"></i>
                        <span>Backup</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="<?= baseUrl() ?>" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>View Site</span>
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <!-- Top Bar -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" id="sidebar-toggle">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title"><?= $adminTitle ?></h1>
            </div>
            <div class="topbar-right">
                <div class="admin-user">
                    <div class="user-avatar">
                        <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= uploadUrl($user['avatar']) ?>" alt="<?= xssClean($user['full_name']) ?>">
                        <?php else: ?>
                        <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="user-dropdown">
                        <button class="user-dropdown-toggle">
                            <span class="user-name"><?= xssClean($user['full_name'] ?? 'Admin') ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown-menu">
                            <a href="<?= baseUrl('admin/settings') ?>"><i class="fas fa-cog"></i> Settings</a>
                            <a href="<?= baseUrl('admin/logout') ?>"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <?php if ($flash = getFlash()): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
            <span><?= $flash['message'] ?></span>
            <button class="alert-close">&times;</button>
        </div>
        <?php endif; ?>

        <!-- Page Content -->
        <div class="admin-content">
