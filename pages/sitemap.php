<?php
/**
 * Dynamic Sitemap.xml Generator
 */
header('Content-Type: application/xml; charset=UTF-8');

$baseUrl = APP_URL;

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Static pages
$staticPages = [
    ['url' => '', 'priority' => '1.0', 'changefreq' => 'weekly'],
    ['url' => 'about', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['url' => 'portfolio', 'priority' => '0.9', 'changefreq' => 'weekly'],
    ['url' => 'blog', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['url' => 'services', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ['url' => 'contact', 'priority' => '0.7', 'changefreq' => 'monthly'],
];

foreach ($staticPages as $page) {
    echo "<url>\n";
    echo "  <loc>{$baseUrl}/{$page['url']}</loc>\n";
    echo "  <changefreq>{$page['changefreq']}</changefreq>\n";
    echo "  <priority>{$page['priority']}</priority>\n";
    echo "</url>\n";
}

// Dynamic: Projects
try {
    $projects = db()->fetchAll("SELECT id, updated_at FROM projects WHERE is_active = 1");
    foreach ($projects as $p) {
        echo "<url>\n";
        echo "  <loc>{$baseUrl}/portfolio/detail/{$p['id']}</loc>\n";
        echo "  <lastmod>" . date('Y-m-d', strtotime($p['updated_at'])) . "</lastmod>\n";
        echo "  <changefreq>monthly</changefreq>\n";
        echo "  <priority>0.7</priority>\n";
        echo "</url>\n";
    }
} catch (Exception $e) {}

// Dynamic: Blog posts
try {
    $posts = db()->fetchAll("SELECT slug, updated_at FROM blogs WHERE status = 'published'");
    foreach ($posts as $post) {
        echo "<url>\n";
        echo "  <loc>{$baseUrl}/blog/detail/{$post['slug']}</loc>\n";
        echo "  <lastmod>" . date('Y-m-d', strtotime($post['updated_at'])) . "</lastmod>\n";
        echo "  <changefreq>weekly</changefreq>\n";
        echo "  <priority>0.8</priority>\n";
        echo "</url>\n";
    }
} catch (Exception $e) {}

echo '</urlset>';
exit;
