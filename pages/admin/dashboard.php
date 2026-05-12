<?php
/**
 * Admin Dashboard
 */
$adminPage = 'dashboard';
$adminTitle = 'Dashboard';

// Get stats
try {
    $totalProjects = db()->count('projects');
    $totalPosts = db()->count('blogs');
    $totalMessages = db()->count('contacts');
    $unreadMessages = db()->count('contacts', 'is_read = 0');
    $totalVisitors = db()->count('visitors');
    $todayVisitors = db()->count('visitors', 'DATE(visited_at) = CURDATE()');
    
    // Recent messages
    $recentMessages = db()->fetchAll(
        "SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5"
    );
    
    // Recent projects
    $recentProjects = db()->fetchAll(
        "SELECT p.*, c.name as category_name FROM projects p 
         LEFT JOIN categories c ON p.category_id = c.id 
         ORDER BY p.created_at DESC LIMIT 5"
    );
    
    // Visitor stats (last 7 days)
    $visitorStats = db()->fetchAll(
        "SELECT DATE(visited_at) as date, COUNT(*) as count 
         FROM visitors 
         WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
         GROUP BY DATE(visited_at) 
         ORDER BY date ASC"
    );
    
    // Browser stats
    $browserStats = db()->fetchAll(
        "SELECT browser, COUNT(*) as count 
         FROM visitors 
         WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         GROUP BY browser 
         ORDER BY count DESC LIMIT 5"
    );
    
} catch (Exception $e) {
    $totalProjects = $totalPosts = $totalMessages = $unreadMessages = $totalVisitors = $todayVisitors = 0;
    $recentMessages = $recentProjects = $visitorStats = $browserStats = [];
}

include TEMPLATES_PATH . '/admin-header.php';
?>

<!-- Dashboard Stats -->
<div class="dashboard-stats">
    <div class="stat-card-admin">
        <div class="stat-card-icon bg-primary">
            <i class="fas fa-folder-open"></i>
        </div>
        <div class="stat-card-info">
            <h3><?= $totalProjects ?></h3>
            <p>Total Projects</p>
        </div>
    </div>
    <div class="stat-card-admin">
        <div class="stat-card-icon bg-success">
            <i class="fas fa-newspaper"></i>
        </div>
        <div class="stat-card-info">
            <h3><?= $totalPosts ?></h3>
            <p>Blog Posts</p>
        </div>
    </div>
    <div class="stat-card-admin">
        <div class="stat-card-icon bg-warning">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-card-info">
            <h3><?= $totalMessages ?></h3>
            <p>Messages <span class="badge-sm"><?= $unreadMessages ?> new</span></p>
        </div>
    </div>
    <div class="stat-card-admin">
        <div class="stat-card-icon bg-info">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-card-info">
            <h3><?= formatNumber($totalVisitors) ?></h3>
            <p>Total Visitors <span class="badge-sm"><?= $todayVisitors ?> today</span></p>
        </div>
    </div>
</div>

<!-- Dashboard Grid -->
<div class="dashboard-grid">
    <!-- Visitor Chart -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-chart-area"></i> Visitors (Last 7 Days)</h3>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="visitors-chart" 
                    data-labels='<?= json_encode(array_column($visitorStats, 'date')) ?>'
                    data-values='<?= json_encode(array_column($visitorStats, 'count')) ?>'>
                </canvas>
                <!-- Simple bar chart fallback -->
                <div class="simple-chart">
                    <?php foreach ($visitorStats as $stat): ?>
                    <div class="chart-bar-group">
                        <div class="chart-bar" style="height: <?= min(100, ($stat['count'] / max(1, max(array_column($visitorStats, 'count')))) * 100) ?>%">
                            <span class="chart-value"><?= $stat['count'] ?></span>
                        </div>
                        <span class="chart-label"><?= date('d/m', strtotime($stat['date'])) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Browser Stats -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-globe"></i> Browsers</h3>
        </div>
        <div class="card-body">
            <div class="browser-stats">
                <?php foreach ($browserStats as $browser): ?>
                <div class="browser-item">
                    <span class="browser-name"><?= xssClean($browser['browser']) ?></span>
                    <div class="browser-bar">
                        <div class="browser-progress" style="width: <?= min(100, ($browser['count'] / max(1, $totalVisitors)) * 100) ?>%"></div>
                    </div>
                    <span class="browser-count"><?= $browser['count'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Data -->
<div class="dashboard-grid">
    <!-- Recent Messages -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-envelope"></i> Recent Messages</h3>
            <a href="<?= baseUrl('admin/messages') ?>" class="card-link">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($recentMessages)): ?>
            <p class="empty-text">No messages yet.</p>
            <?php else: ?>
            <div class="message-list">
                <?php foreach ($recentMessages as $msg): ?>
                <div class="message-item <?= !$msg['is_read'] ? 'unread' : '' ?>">
                    <div class="message-avatar">
                        <?= strtoupper(substr($msg['name'], 0, 1)) ?>
                    </div>
                    <div class="message-info">
                        <h4><?= xssClean($msg['name']) ?></h4>
                        <p><?= truncateText($msg['subject'], 40) ?></p>
                        <span class="message-time"><?= timeAgo($msg['created_at']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Projects -->
    <div class="dashboard-card">
        <div class="card-header">
            <h3><i class="fas fa-folder-open"></i> Recent Projects</h3>
            <a href="<?= baseUrl('admin/projects') ?>" class="card-link">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($recentProjects)): ?>
            <p class="empty-text">No projects yet.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProjects as $proj): ?>
                        <tr>
                            <td><strong><?= truncateText(xssClean($proj['title']), 30) ?></strong></td>
                            <td><span class="badge-category"><?= xssClean($proj['category_name'] ?? '-') ?></span></td>
                            <td><?= formatDate($proj['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include TEMPLATES_PATH . '/admin-footer.php'; ?>
