<?php
/**
 * Home Page
 * Hero section, stats, featured projects, testimonials
 */
$pageTitle = '';
$pageDescription = getSetting('site_description');
include TEMPLATES_PATH . '/header.php';

// Person Schema Markup
echo schemaMarkup('Person', [
    'name' => getSetting('owner_name', 'Aldi'),
    'url' => APP_URL,
    'jobTitle' => getSetting('owner_title', 'Full Stack Developer'),
    'email' => getSetting('owner_email', ''),
    'description' => getSetting('owner_bio', ''),
    'sameAs' => array_values(array_filter([
        getSetting('social_github'),
        getSetting('social_linkedin'),
        getSetting('social_twitter'),
        getSetting('social_instagram'),
    ]))
]);

// Get featured projects
try {
    $featuredProjects = db()->fetchAll(
        "SELECT p.*, c.name as category_name FROM projects p 
         LEFT JOIN categories c ON p.category_id = c.id 
         WHERE p.is_active = 1 AND p.is_featured = 1 
         ORDER BY p.sort_order ASC LIMIT 6"
    );
} catch (Exception $e) {
    $featuredProjects = [];
}

// Get testimonials
try {
    $testimonials = db()->fetchAll(
        "SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 4"
    );
} catch (Exception $e) {
    $testimonials = [];
}

// Get typing texts
$typingTexts = getSetting('hero_typing_texts', 'Web Developer,UI/UX Designer,Freelancer');
?>

<!-- Hero Section -->
<section class="hero" id="hero">
    <!-- Particle background -->
    <div class="hero-particles" id="particles"></div>
    
    <!-- Gradient overlay -->
    <div class="hero-overlay"></div>
    
    <div class="container">
        <div class="hero-content" data-aos="fade-up" data-aos-duration="1000">
            
            <h1 class="hero-title">
                <?= getSetting('hero_title', 'Hi, I am Aldi') ?>
            </h1>
            
            <div class="hero-typing">
                <span class="typing-prefix">I'm a </span>
                <span class="typing-text" id="typing-text" data-texts="<?= htmlspecialchars($typingTexts) ?>"></span>
                <span class="typing-cursor">|</span>
            </div>
            
            <p class="hero-description" data-aos="fade-up" data-aos-delay="400">
                <?= getSetting('hero_description', 'I craft beautiful, functional, and scalable digital experiences.') ?>
            </p>
            
            <div class="hero-buttons" data-aos="fade-up" data-aos-delay="600">
                <a href="<?= baseUrl('portfolio') ?>" class="btn btn-primary btn-lg">
                    <i class="fas fa-briefcase"></i>
                    <?= lang('hero_cta') ?>
                </a>
                <a href="<?= baseUrl('contact') ?>" class="btn btn-outline btn-lg">
                    <i class="fas fa-paper-plane"></i>
                    <?= lang('hero_cta_secondary') ?>
                </a>
            </div>
            
            <!-- Social Links -->
            <div class="hero-social" data-aos="fade-up" data-aos-delay="800">
                <?php if ($github = getSetting('social_github')): ?>
                <a href="<?= $github ?>" target="_blank" rel="noopener" class="social-link" aria-label="GitHub">
                    <i class="fab fa-github"></i>
                </a>
                <?php endif; ?>
                <?php if ($linkedin = getSetting('social_linkedin')): ?>
                <a href="<?= $linkedin ?>" target="_blank" rel="noopener" class="social-link" aria-label="LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                <?php endif; ?>
                <?php if ($twitter = getSetting('social_twitter')): ?>
                <a href="<?= $twitter ?>" target="_blank" rel="noopener" class="social-link" aria-label="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <?php endif; ?>
                <?php if ($instagram = getSetting('social_instagram')): ?>
                <a href="<?= $instagram ?>" target="_blank" rel="noopener" class="social-link" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Scroll indicator -->
        <div class="hero-scroll" data-aos="fade-up" data-aos-delay="1000">
            <a href="#stats" class="scroll-indicator">
                <span class="scroll-text">Scroll Down</span>
                <span class="scroll-icon">
                    <i class="fas fa-chevron-down"></i>
                </span>
            </a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section" id="stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card glass" data-aos="fade-up" data-aos-delay="0">
                <div class="stat-icon">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="stat-number" data-count="<?= getSetting('stats_projects', '50') ?>">0</div>
                <div class="stat-label"><?= lang('stats_projects') ?></div>
            </div>
            <div class="stat-card glass" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number" data-count="<?= getSetting('stats_clients', '30') ?>">0</div>
                <div class="stat-label"><?= lang('stats_clients') ?></div>
            </div>
            <div class="stat-card glass" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number" data-count="<?= getSetting('stats_experience', '5') ?>">0</div>
                <div class="stat-label"><?= lang('stats_experience') ?></div>
            </div>
            <div class="stat-card glass" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-number" data-count="<?= getSetting('stats_awards', '12') ?>">0</div>
                <div class="stat-label"><?= lang('stats_awards') ?></div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Projects Section -->
<section class="section featured-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">Portfolio</span>
            <h2 class="section-title">Featured Projects</h2>
            <p class="section-desc">Some of my recent work that I'm proud of</p>
        </div>
        
        <div class="projects-grid">
            <?php foreach ($featuredProjects as $index => $project): ?>
            <div class="project-card glass" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                <div class="project-image">
                    <img src="<?= !empty($project['thumbnail']) ? uploadUrl($project['thumbnail']) : asset('images/project-placeholder.jpg') ?>" 
                         alt="<?= xssClean($project['title']) ?>" loading="lazy">
                    <div class="project-overlay">
                        <div class="project-actions">
                            <?php if (!empty($project['demo_url'])): ?>
                            <a href="<?= $project['demo_url'] ?>" target="_blank" class="project-btn" title="Live Demo">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <?php endif; ?>
                            <?php if (!empty($project['github_url'])): ?>
                            <a href="<?= $project['github_url'] ?>" target="_blank" class="project-btn" title="Source Code">
                                <i class="fab fa-github"></i>
                            </a>
                            <?php endif; ?>
                            <a href="<?= baseUrl('portfolio/detail/' . $project['id']) ?>" class="project-btn" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="project-info">
                    <span class="project-category"><?= xssClean($project['category_name'] ?? 'Project') ?></span>
                    <h3 class="project-title"><?= xssClean($project['title']) ?></h3>
                    <p class="project-desc"><?= truncateText($project['short_description'] ?? $project['description'], 100) ?></p>
                    <div class="project-tech">
                        <?php 
                        $techs = explode(',', $project['technologies'] ?? '');
                        foreach (array_slice($techs, 0, 4) as $tech): 
                        ?>
                        <span class="tech-badge"><?= trim($tech) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="section-footer" data-aos="fade-up">
            <a href="<?= baseUrl('portfolio') ?>" class="btn btn-outline">
                <?= lang('view_all') ?> <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<?php if (!empty($testimonials)): ?>
<section class="section testimonials-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">Testimonials</span>
            <h2 class="section-title">What Clients Say</h2>
            <p class="section-desc">Feedback from people I've worked with</p>
        </div>
        
        <div class="testimonials-grid">
            <?php foreach ($testimonials as $index => $testimonial): ?>
            <div class="testimonial-card glass" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                <div class="testimonial-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?= $i <= $testimonial['rating'] ? 'active' : '' ?>"></i>
                    <?php endfor; ?>
                </div>
                <p class="testimonial-content">"<?= xssClean($testimonial['content']) ?>"</p>
                <div class="testimonial-author">
                    <div class="author-avatar">
                        <?php if (!empty($testimonial['client_avatar'])): ?>
                        <img src="<?= uploadUrl($testimonial['client_avatar']) ?>" alt="<?= xssClean($testimonial['client_name']) ?>">
                        <?php else: ?>
                        <div class="avatar-placeholder"><?= strtoupper(substr($testimonial['client_name'], 0, 1)) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="author-info">
                        <h4 class="author-name"><?= xssClean($testimonial['client_name']) ?></h4>
                        <span class="author-role">
                            <?= xssClean($testimonial['client_position']) ?>
                            <?php if (!empty($testimonial['client_company'])): ?>
                            at <?= xssClean($testimonial['client_company']) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="section cta-section">
    <div class="container">
        <div class="cta-card glass" data-aos="fade-up">
            <div class="cta-content">
                <h2 class="cta-title">Have a Project in Mind?</h2>
                <p class="cta-desc">Let's work together to turn your ideas into reality. I'm currently available for freelance projects.</p>
                <div class="cta-buttons">
                    <a href="<?= baseUrl('contact') ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-rocket"></i> Let's Talk
                    </a>
                    <a href="<?= baseUrl('services') ?>" class="btn btn-outline btn-lg">
                        <i class="fas fa-list"></i> My Services
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include TEMPLATES_PATH . '/footer.php'; ?>
