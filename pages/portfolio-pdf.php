<?php
/**
 * Portfolio PDF Export Page
 * Clean, print-optimized layout for all active projects
 * Users can use browser's "Save as PDF" from print dialog
 */

// Get all active projects
try {
    $projects = db()->fetchAll(
        "SELECT p.*, c.name as category_name 
         FROM projects p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.is_active = 1 
         ORDER BY p.is_featured DESC, p.sort_order ASC, p.created_at DESC"
    );
} catch (Exception $e) {
    $projects = [];
}

$ownerName = getSetting('owner_name', 'Portfolio');
$ownerTitle = getSetting('owner_title', 'Developer');
$ownerEmail = getSetting('owner_email', '');
$siteName = getSetting('site_name', APP_NAME);
?>
<!DOCTYPE html>
<html lang="<?= APP_LANG ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio - <?= htmlspecialchars($ownerName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #ffffff;
            color: #1a1a2e;
            line-height: 1.6;
            padding: 40px;
        }
        .pdf-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 24px;
            border-bottom: 2px solid #0066ff;
        }
        .pdf-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }
        .pdf-header p {
            font-size: 1rem;
            color: #555;
        }
        .pdf-header .contact {
            font-size: 0.85rem;
            color: #777;
            margin-top: 8px;
        }
        .print-btn {
            display: inline-block;
            padding: 12px 24px;
            background: #0066ff;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 30px;
            text-decoration: none;
        }
        .print-btn:hover { background: #0052cc; }
        .print-btn i { margin-right: 8px; }
        .back-link {
            display: inline-block;
            margin-bottom: 30px;
            margin-right: 12px;
            padding: 12px 24px;
            background: #f0f0f0;
            color: #333;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: none;
        }
        .back-link:hover { background: #e0e0e0; }
        .actions-bar { text-align: center; margin-bottom: 30px; }
        .project-item {
            margin-bottom: 28px;
            padding: 20px 24px;
            border: 1px solid #e5e5e5;
            border-radius: 8px;
            page-break-inside: avoid;
        }
        .project-item h3 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 4px;
        }
        .project-meta {
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 8px;
        }
        .project-meta span { margin-right: 16px; }
        .project-desc {
            font-size: 0.9rem;
            color: #444;
            margin-bottom: 10px;
        }
        .project-tech {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }
        .tech-tag {
            display: inline-block;
            padding: 2px 10px;
            background: #f0f4ff;
            color: #0066ff;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .pdf-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
            font-size: 0.8rem;
            color: #999;
        }

        /* Print styles */
        @media print {
            body { padding: 20px; }
            .actions-bar { display: none !important; }
            .project-item { border: 1px solid #ddd; box-shadow: none; }
            .pdf-header { border-bottom-color: #333; }
            a { text-decoration: none; color: inherit; }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <!-- Action Buttons -->
    <div class="actions-bar">
        <a href="<?= baseUrl('portfolio') ?>" class="back-link"><i class="fas fa-arrow-left"></i> Back to Portfolio</a>
        <button class="print-btn" onclick="window.print()"><i class="fas fa-print"></i> Print / Save as PDF</button>
    </div>

    <!-- Header -->
    <div class="pdf-header">
        <h1><?= htmlspecialchars($ownerName) ?></h1>
        <p><?= htmlspecialchars($ownerTitle) ?></p>
        <?php if ($ownerEmail): ?>
        <div class="contact"><?= htmlspecialchars($ownerEmail) ?><?php if ($website = getSetting('social_github')): ?> | <?= htmlspecialchars($website) ?><?php endif; ?></div>
        <?php endif; ?>
    </div>

    <!-- Projects -->
    <?php if (empty($projects)): ?>
    <p style="text-align:center;color:#999;">No projects available.</p>
    <?php else: ?>
    <?php foreach ($projects as $project): ?>
    <div class="project-item">
        <h3><?= htmlspecialchars($project['title']) ?></h3>
        <div class="project-meta">
            <span><i class="fas fa-tag"></i> <?= htmlspecialchars($project['category_name'] ?? 'Project') ?></span>
            <?php if (!empty($project['client_name'])): ?>
            <span><i class="fas fa-user"></i> <?= htmlspecialchars($project['client_name']) ?></span>
            <?php endif; ?>
            <?php if (!empty($project['project_date'])): ?>
            <span><i class="fas fa-calendar"></i> <?= formatDate($project['project_date'], 'F Y') ?></span>
            <?php endif; ?>
        </div>
        <p class="project-desc"><?= truncateText(strip_tags($project['description'] ?? $project['short_description'] ?? ''), 250) ?></p>
        <?php if (!empty($project['technologies'])): ?>
        <div class="project-tech">
            <?php foreach (explode(',', $project['technologies']) as $tech): ?>
            <span class="tech-tag"><?= htmlspecialchars(trim($tech)) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Footer -->
    <div class="pdf-footer">
        <p>Generated from <?= htmlspecialchars($siteName) ?> | <?= date('d M Y') ?></p>
    </div>
</body>
</html>
