<?php
/**
 * RSS Feed (RSS 2.0)
 * Outputs latest published blog posts as RSS XML
 */
header('Content-Type: application/rss+xml; charset=UTF-8');

$siteName = getSetting('site_name', APP_NAME);
$siteDesc = getSetting('site_description', 'Portfolio website');
$baseUrl = APP_URL;

// Get latest published posts
try {
    $posts = db()->fetchAll(
        "SELECT b.*, c.name as category_name 
         FROM blogs b 
         LEFT JOIN categories c ON b.category_id = c.id 
         WHERE b.status = 'published' AND (b.published_at IS NULL OR b.published_at <= NOW())
         ORDER BY b.published_at DESC 
         LIMIT 20"
    );
} catch (Exception $e) {
    $posts = [];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title><?= htmlspecialchars($siteName) ?></title>
    <link><?= $baseUrl ?></link>
    <description><?= htmlspecialchars($siteDesc) ?></description>
    <language>en</language>
    <lastBuildDate><?= date('r') ?></lastBuildDate>
    <atom:link href="<?= $baseUrl ?>/feed.xml" rel="self" type="application/rss+xml"/>
    <?php foreach ($posts as $post): ?>
    <item>
        <title><?= htmlspecialchars($post['title']) ?></title>
        <link><?= $baseUrl ?>/blog/detail/<?= $post['slug'] ?></link>
        <description><?= htmlspecialchars(truncateText(strip_tags($post['excerpt'] ?? $post['content']), 300)) ?></description>
        <pubDate><?= date('r', strtotime($post['published_at'] ?? $post['created_at'])) ?></pubDate>
        <guid isPermaLink="true"><?= $baseUrl ?>/blog/detail/<?= $post['slug'] ?></guid>
        <?php if (!empty($post['category_name'])): ?>
        <category><?= htmlspecialchars($post['category_name']) ?></category>
        <?php endif; ?>
    </item>
    <?php endforeach; ?>
</channel>
</rss>
<?php exit; ?>
