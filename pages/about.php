<?php
/**
 * About Page
 * Bio, skills, timeline, download CV
 */
$pageTitle = lang('about_title');
$pageDescription = 'Learn more about ' . getSetting('owner_name', 'Aldi') . ' - ' . getSetting('owner_title', 'Full Stack Developer');
include TEMPLATES_PATH . '/header.php';

// Get skills
try {
    $skills = db()->fetchAll("SELECT * FROM skills WHERE is_active = 1 ORDER BY sort_order ASC");
} catch (Exception $e) {
    $skills = [];
}

// Get experience/timeline
try {
    $experiences = db()->fetchAll("SELECT * FROM experience ORDER BY start_date DESC");
} catch (Exception $e) {
    $experiences = [];
}

// Group skills by category
$skillGroups = [];
foreach ($skills as $skill) {
    $skillGroups[$skill['category']][] = $skill;
}
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="page-header-content" data-aos="fade-up">
            <span class="page-subtitle">Get to know me</span>
            <h1 class="page-title"><?= lang('about_title') ?></h1>
            <div class="breadcrumb">
                <a href="<?= baseUrl() ?>">Home</a>
                <span class="separator"><i class="fas fa-chevron-right"></i></span>
                <span class="current">About</span>
            </div>
        </div>
    </div>
</section>

<!-- About Intro Section -->
<section class="section about-intro">
    <div class="container">
        <div class="about-grid">
            <!-- Profile Image -->
            <div class="about-image" data-aos="fade-right">
                <div class="image-wrapper glass">
                    <?php $avatar = getSetting('owner_avatar'); ?>
                    <?php if (!empty($avatar)): ?>
                    <img src="<?= uploadUrl($avatar) ?>" alt="<?= getSetting('owner_name') ?>">
                    <?php else: ?>
                    <div class="image-placeholder">
                        <i class="fas fa-user"></i>
                    </div>
                    <?php endif; ?>
                    <div class="image-decoration"></div>
                </div>
                <!-- Experience badge -->
                <div class="experience-badge glass">
                    <span class="badge-number"><?= getSetting('stats_experience', '5') ?>+</span>
                    <span class="badge-text">Years<br>Experience</span>
                </div>
            </div>

            <!-- About Content -->
            <div class="about-content" data-aos="fade-left">
                <span class="section-subtitle">About Me</span>
                <h2 class="about-name"><?= getSetting('owner_name', 'Aldi') ?></h2>
                <p class="about-title-role"><?= getSetting('owner_title', 'Full Stack Developer') ?></p>
                
                <div class="about-bio">
                    <p><?= getSetting('owner_bio', 'Passionate developer with years of experience building modern web applications.') ?></p>
                </div>

                <!-- Quick Info -->
                <div class="about-info-grid">
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-user"></i> Name</span>
                        <span class="info-value"><?= getSetting('owner_name', 'Aldi') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-envelope"></i> Email</span>
                        <span class="info-value"><?= getSetting('owner_email', 'hello@aldidev.com') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-phone"></i> Phone</span>
                        <span class="info-value"><?= getSetting('owner_phone', '+62 812 3456 7890') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><i class="fas fa-map-marker-alt"></i> Location</span>
                        <span class="info-value"><?= getSetting('owner_address', 'Jakarta, Indonesia') ?></span>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="about-buttons">
                    <?php $cv = getSetting('owner_cv'); ?>
                    <?php if (!empty($cv)): ?>
                    <a href="<?= uploadUrl($cv) ?>" class="btn btn-primary" download>
                        <i class="fas fa-download"></i> <?= lang('about_download_cv') ?>
                    </a>
                    <?php endif; ?>
                    <a href="<?= baseUrl('contact') ?>" class="btn btn-outline">
                        <i class="fas fa-paper-plane"></i> <?= lang('hero_cta_secondary') ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Skills Section -->
<section class="section skills-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">What I Know</span>
            <h2 class="section-title"><?= lang('about_skills') ?></h2>
            <p class="section-desc">Technologies and tools I work with every day</p>
        </div>

        <!-- Skill Category Tabs -->
        <div class="skill-tabs" data-aos="fade-up">
            <button class="skill-tab active" data-category="all">All</button>
            <?php foreach (array_keys($skillGroups) as $category): ?>
            <button class="skill-tab" data-category="<?= $category ?>"><?= ucfirst($category) ?></button>
            <?php endforeach; ?>
        </div>

        <!-- Skills Grid -->
        <div class="skills-grid">
            <?php foreach ($skills as $index => $skill): ?>
            <div class="skill-item" data-category="<?= $skill['category'] ?>" data-aos="fade-up" data-aos-delay="<?= ($index % 6) * 50 ?>">
                <div class="skill-header">
                    <div class="skill-icon" style="color: <?= $skill['color'] ?>">
                        <i class="<?= $skill['icon'] ?? 'fas fa-code' ?>"></i>
                    </div>
                    <div class="skill-info">
                        <h4 class="skill-name"><?= xssClean($skill['name']) ?></h4>
                        <span class="skill-percent"><?= $skill['percentage'] ?>%</span>
                    </div>
                </div>
                <div class="skill-bar">
                    <div class="skill-progress" data-progress="<?= $skill['percentage'] ?>" style="--progress-color: <?= $skill['color'] ?>"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Timeline Section -->
<section class="section timeline-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">My Journey</span>
            <h2 class="section-title"><?= lang('about_experience') ?> & <?= lang('about_education') ?></h2>
            <p class="section-desc">Professional experience and educational background</p>
        </div>

        <!-- Timeline Filter -->
        <div class="timeline-tabs" data-aos="fade-up">
            <button class="timeline-tab active" data-type="all">All</button>
            <button class="timeline-tab" data-type="work"><i class="fas fa-briefcase"></i> Work</button>
            <button class="timeline-tab" data-type="education"><i class="fas fa-graduation-cap"></i> Education</button>
            <button class="timeline-tab" data-type="certification"><i class="fas fa-certificate"></i> Certifications</button>
        </div>

        <!-- Timeline -->
        <div class="timeline">
            <?php foreach ($experiences as $index => $exp): ?>
            <div class="timeline-item <?= $index % 2 === 0 ? 'left' : 'right' ?>" 
                 data-type="<?= $exp['type'] ?>" 
                 data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                <div class="timeline-marker">
                    <i class="fas fa-<?= $exp['type'] === 'work' ? 'briefcase' : ($exp['type'] === 'education' ? 'graduation-cap' : 'certificate') ?>"></i>
                </div>
                <div class="timeline-content glass">
                    <div class="timeline-header">
                        <span class="timeline-date">
                            <i class="fas fa-calendar-alt"></i>
                            <?= formatDate($exp['start_date'], 'M Y') ?> - 
                            <?= $exp['is_current'] ? '<span class="badge-current">Present</span>' : formatDate($exp['end_date'], 'M Y') ?>
                        </span>
                        <span class="timeline-type badge-<?= $exp['type'] ?>"><?= ucfirst($exp['type']) ?></span>
                    </div>
                    <h3 class="timeline-title"><?= xssClean($exp['title']) ?></h3>
                    <p class="timeline-company">
                        <i class="fas fa-building"></i> <?= xssClean($exp['company'] ?? '') ?>
                        <?php if (!empty($exp['location'])): ?>
                        <span class="timeline-location">
                            <i class="fas fa-map-marker-alt"></i> <?= xssClean($exp['location']) ?>
                        </span>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($exp['description'])): ?>
                    <p class="timeline-desc"><?= xssClean($exp['description']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include TEMPLATES_PATH . '/footer.php'; ?>
