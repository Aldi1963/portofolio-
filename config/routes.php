<?php
/**
 * Application Routes Configuration
 * Maps URL paths to controllers/pages
 */

$routes = [
    // Public routes
    '' => 'home',
    'home' => 'home',
    'about' => 'about',
    'portfolio' => 'portfolio',
    'portfolio/detail' => 'portfolio-detail',
    'portfolio/pdf' => 'portfolio-pdf',
    'blog' => 'blog',
    'blog/detail' => 'blog-detail',
    'services' => 'services',
    'contact' => 'contact',
    'sitemap.xml' => 'sitemap',
    'feed.xml' => 'feed',
    
    // Auth routes
    'admin/login' => 'admin/login',
    'admin/logout' => 'admin/logout',
    'admin/google-callback' => 'admin/google-callback',
    
    // Admin routes
    'admin' => 'admin/dashboard',
    'admin/dashboard' => 'admin/dashboard',
    'admin/projects' => 'admin/projects',
    'admin/projects/create' => 'admin/projects-create',
    'admin/projects/edit' => 'admin/projects-edit',
    'admin/projects/delete' => 'admin/projects-delete',
    'admin/blog' => 'admin/blog',
    'admin/blog/create' => 'admin/blog-create',
    'admin/blog/edit' => 'admin/blog-edit',
    'admin/blog/delete' => 'admin/blog-delete',
    'admin/services' => 'admin/services',
    'admin/services/create' => 'admin/services-create',
    'admin/services/edit' => 'admin/services-edit',
    'admin/services/delete' => 'admin/services-delete',
    'admin/testimonials' => 'admin/testimonials',
    'admin/testimonials/create' => 'admin/testimonials-create',
    'admin/testimonials/edit' => 'admin/testimonials-edit',
    'admin/testimonials/delete' => 'admin/testimonials-delete',
    'admin/messages' => 'admin/messages',
    'admin/messages/view' => 'admin/messages-view',
    'admin/messages/delete' => 'admin/messages-delete',
    'admin/settings' => 'admin/settings',
    'admin/change-password' => 'admin/change-password',
    'admin/categories' => 'admin/categories',
    'admin/comments' => 'admin/comments',
    'admin/activity-log' => 'admin/activity-log',
    'admin/backup' => 'admin/backup',
    'admin/experience' => 'admin/experience',
    'admin/users' => 'admin/users',
    
    // API routes
    'api/projects' => 'api/projects',
    'api/blog' => 'api/blog',
    'api/contact' => 'api/contact',
    'api/newsletter' => 'api/newsletter',
    'api/comments' => 'api/comments',
    'api/visitors' => 'api/visitors',
];

return $routes;
