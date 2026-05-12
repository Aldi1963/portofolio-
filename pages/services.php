<?php
/**
 * Services Page
 * Service cards, pricing, FAQ, testimonials
 */
$pageTitle = lang('services_title');
$pageDescription = 'Professional web development services including custom websites, web applications, UI/UX design, and maintenance.';
include TEMPLATES_PATH . '/header.php';

// Get services
try {
    $services = db()->fetchAll("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC");
} catch (Exception $e) {
    $services = [];
}

// Get FAQs
try {
    $faqs = db()->fetchAll("SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC");
} catch (Exception $e) {
    $faqs = [];
}

// Get testimonials
try {
    $testimonials = db()->fetchAll("SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 4");
} catch (Exception $e) {
    $testimonials = [];
}
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="page-header-content" data-aos="fade-up">
            <span class="page-subtitle">What I Offer</span>
            <h1 class="page-title"><?= lang('services_title') ?></h1>
            <p class="page-desc">High-quality digital solutions tailored to your needs and budget.</p>
            <div class="breadcrumb">
                <a href="<?= baseUrl() ?>">Home</a>
                <span class="separator"><i class="fas fa-chevron-right"></i></span>
                <span class="current">Services</span>
            </div>
        </div>
    </div>
</section>

<!-- Services Section -->
<section class="section services-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">Services</span>
            <h2 class="section-title">Pricing Plans</h2>
            <p class="section-desc">Choose the right plan for your project. All plans include consultation and dedicated support.</p>
        </div>

        <div class="pricing-grid">
            <?php foreach ($services as $index => $service): ?>
            <div class="pricing-card glass <?= $service['is_popular'] ? 'popular' : '' ?>" data-aos="fade-up" data-aos-delay="<?= $index * 100 ?>">
                <?php if ($service['is_popular']): ?>
                <div class="popular-badge">
                    <i class="fas fa-fire"></i> <?= lang('services_popular') ?>
                </div>
                <?php endif; ?>
                
                <div class="pricing-icon">
                    <i class="<?= $service['icon'] ?>"></i>
                </div>
                
                <h3 class="pricing-title"><?= xssClean($service['title']) ?></h3>
                <p class="pricing-desc"><?= truncateText(xssClean($service['description']), 120) ?></p>
                
                <div class="pricing-price">
                    <?php if (!empty($service['price'])): ?>
                    <span class="price-amount"><?= formatCurrency($service['price']) ?></span>
                    <span class="price-unit">/ <?= xssClean($service['price_unit']) ?></span>
                    <?php else: ?>
                    <span class="price-amount">Custom</span>
                    <span class="price-unit">Contact for quote</span>
                    <?php endif; ?>
                </div>
                
                <!-- Features -->
                <?php if (!empty($service['features'])): ?>
                <ul class="pricing-features">
                    <?php foreach (explode(',', $service['features']) as $feature): ?>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span><?= xssClean(trim($feature)) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                
                <a href="<?= baseUrl('contact') ?>?service=<?= urlencode($service['title']) ?>" 
                   class="btn <?= $service['is_popular'] ? 'btn-primary' : 'btn-outline' ?> btn-block">
                    <i class="fas fa-paper-plane"></i> <?= lang('services_get_started') ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Process Section -->
<section class="section process-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">How It Works</span>
            <h2 class="section-title">My Working Process</h2>
            <p class="section-desc">A streamlined approach to deliver your project on time and within budget.</p>
        </div>

        <div class="process-grid">
            <div class="process-card" data-aos="fade-up" data-aos-delay="0">
                <div class="process-number">01</div>
                <div class="process-icon"><i class="fas fa-comments"></i></div>
                <h3>Discovery & Planning</h3>
                <p>Understanding your requirements, goals, and target audience through detailed consultation.</p>
            </div>
            <div class="process-card" data-aos="fade-up" data-aos-delay="100">
                <div class="process-number">02</div>
                <div class="process-icon"><i class="fas fa-pencil-ruler"></i></div>
                <h3>Design & Prototype</h3>
                <p>Creating wireframes and visual designs that align with your brand identity and user needs.</p>
            </div>
            <div class="process-card" data-aos="fade-up" data-aos-delay="200">
                <div class="process-number">03</div>
                <div class="process-icon"><i class="fas fa-code"></i></div>
                <h3>Development</h3>
                <p>Building your project with clean code, modern technologies, and best practices.</p>
            </div>
            <div class="process-card" data-aos="fade-up" data-aos-delay="300">
                <div class="process-number">04</div>
                <div class="process-icon"><i class="fas fa-rocket"></i></div>
                <h3>Launch & Support</h3>
                <p>Deploying your project and providing ongoing support and maintenance.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<?php if (!empty($testimonials)): ?>
<section class="section testimonials-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">Testimonials</span>
            <h2 class="section-title">Client Reviews</h2>
            <p class="section-desc">What clients say about my services and work quality.</p>
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
                            <?= xssClean($testimonial['client_position'] ?? '') ?>
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

<!-- FAQ Section -->
<?php if (!empty($faqs)): ?>
<section class="section faq-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-subtitle">FAQ</span>
            <h2 class="section-title"><?= lang('services_faq') ?></h2>
            <p class="section-desc">Common questions about my services and work process.</p>
        </div>

        <div class="faq-container" data-aos="fade-up">
            <?php foreach ($faqs as $index => $faq): ?>
            <div class="faq-item glass">
                <button class="faq-question" aria-expanded="false">
                    <span><?= xssClean($faq['question']) ?></span>
                    <i class="fas fa-plus faq-icon"></i>
                </button>
                <div class="faq-answer">
                    <p><?= xssClean($faq['answer']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="section cta-section">
    <div class="container">
        <div class="cta-card glass" data-aos="fade-up">
            <div class="cta-content">
                <h2 class="cta-title">Ready to Start Your Project?</h2>
                <p class="cta-desc">Let's discuss your project and find the perfect solution. Free consultation included!</p>
                <div class="cta-buttons">
                    <a href="<?= baseUrl('contact') ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i> Get Free Quote
                    </a>
                    <?php if (!empty(config('whatsapp_number'))): ?>
                    <a href="https://wa.me/<?= config('whatsapp_number') ?>?text=<?= urlencode('Hi! I would like to discuss a project.') ?>" 
                       target="_blank" class="btn btn-outline btn-lg">
                        <i class="fab fa-whatsapp"></i> WhatsApp Me
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include TEMPLATES_PATH . '/footer.php'; ?>
