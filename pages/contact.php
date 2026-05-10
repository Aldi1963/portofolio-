<?php
/**
 * Contact Page
 * Contact form, Google Maps, social links, WhatsApp integration
 */
$pageTitle = lang('contact_title');
$pageDescription = 'Get in touch with me for project inquiries, collaborations, or just to say hello.';
$prefilledService = get('service');
include TEMPLATES_PATH . '/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="page-header-content" data-aos="fade-up">
            <span class="page-subtitle">Get In Touch</span>
            <h1 class="page-title"><?= lang('contact_title') ?></h1>
            <p class="page-desc">Have a project in mind? Let's work together to bring your ideas to life.</p>
            <div class="breadcrumb">
                <a href="<?= baseUrl() ?>">Home</a>
                <span class="separator"><i class="fas fa-chevron-right"></i></span>
                <span class="current">Contact</span>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section class="section contact-section">
    <div class="container">
        <!-- Contact Info Cards -->
        <div class="contact-info-grid">
            <div class="contact-info-card glass" data-aos="fade-up" data-aos-delay="0">
                <div class="info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Email</h3>
                <p><?= getSetting('owner_email', 'hello@aldidev.com') ?></p>
                <a href="mailto:<?= getSetting('owner_email', 'hello@aldidev.com') ?>" class="info-link">Send Email <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="contact-info-card glass" data-aos="fade-up" data-aos-delay="100">
                <div class="info-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <h3>Phone</h3>
                <p><?= getSetting('owner_phone', '+62 812 3456 7890') ?></p>
                <a href="tel:<?= preg_replace('/\s+/', '', getSetting('owner_phone', '')) ?>" class="info-link">Call Now <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="contact-info-card glass" data-aos="fade-up" data-aos-delay="200">
                <div class="info-icon">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <h3>WhatsApp</h3>
                <p>Chat with me directly</p>
                <?php if (!empty(WHATSAPP_NUMBER)): ?>
                <a href="https://wa.me/<?= WHATSAPP_NUMBER ?>?text=<?= urlencode(getSetting('whatsapp_message', 'Hello!')) ?>" target="_blank" class="info-link">Chat Now <i class="fas fa-arrow-right"></i></a>
                <?php endif; ?>
            </div>
            <div class="contact-info-card glass" data-aos="fade-up" data-aos-delay="300">
                <div class="info-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3>Location</h3>
                <p><?= getSetting('owner_address', 'Jakarta, Indonesia') ?></p>
                <span class="info-link">Available Worldwide</span>
            </div>
        </div>

        <!-- Contact Form & Map -->
        <div class="contact-grid">
            <!-- Contact Form -->
            <div class="contact-form-wrapper" data-aos="fade-right">
                <div class="form-card glass">
                    <h2 class="form-title"><i class="fas fa-paper-plane"></i> Send Me a Message</h2>
                    <p class="form-subtitle">Fill out the form and I'll get back to you within 24 hours.</p>
                    
                    <form class="contact-form" id="contact-form">
                        <?= csrfField() ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contact-name"><?= lang('contact_name') ?> *</label>
                                <div class="input-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="contact-name" name="name" placeholder="John Doe" required>
                                </div>
                                <span class="form-error" id="error-name"></span>
                            </div>
                            <div class="form-group">
                                <label for="contact-email"><?= lang('contact_email') ?> *</label>
                                <div class="input-icon">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="contact-email" name="email" placeholder="john@example.com" required>
                                </div>
                                <span class="form-error" id="error-email"></span>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="contact-phone"><?= lang('contact_phone') ?></label>
                                <div class="input-icon">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" id="contact-phone" name="phone" placeholder="+62 812 3456 7890">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="contact-subject"><?= lang('contact_subject') ?> *</label>
                                <div class="input-icon">
                                    <i class="fas fa-tag"></i>
                                    <input type="text" id="contact-subject" name="subject" 
                                           value="<?= !empty($prefilledService) ? 'Inquiry: ' . xssClean($prefilledService) : '' ?>"
                                           placeholder="Project Inquiry" required>
                                </div>
                                <span class="form-error" id="error-subject"></span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact-message"><?= lang('contact_message') ?> *</label>
                            <div class="input-icon textarea-icon">
                                <i class="fas fa-comment-dots"></i>
                                <textarea id="contact-message" name="message" rows="6" 
                                          placeholder="Tell me about your project, timeline, and budget..." required></textarea>
                            </div>
                            <span class="form-error" id="error-message"></span>
                        </div>

                        <?php if (!empty(RECAPTCHA_SITE_KEY)): ?>
                        <input type="hidden" name="recaptcha_token" id="recaptcha-token">
                        <?php endif; ?>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="contact-submit">
                            <i class="fas fa-paper-plane"></i> <?= lang('contact_send') ?>
                            <span class="btn-loader" style="display:none;"><i class="fas fa-spinner fa-spin"></i></span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Map & Social -->
            <div class="contact-sidebar" data-aos="fade-left">
                <!-- Google Maps -->
                <?php $mapsEmbed = getSetting('google_maps_embed'); ?>
                <?php if (!empty($mapsEmbed)): ?>
                <div class="map-card glass">
                    <div class="map-embed">
                        <?= $mapsEmbed ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="map-card glass map-placeholder">
                    <div class="map-placeholder-content">
                        <i class="fas fa-map-marked-alt"></i>
                        <h3><?= getSetting('owner_address', 'Jakarta, Indonesia') ?></h3>
                        <p>Available for remote collaboration worldwide</p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Social Links -->
                <div class="social-card glass">
                    <h3>Connect With Me</h3>
                    <p>Follow me on social media for updates and behind-the-scenes content.</p>
                    <div class="social-links-grid">
                        <?php if ($github = getSetting('social_github')): ?>
                        <a href="<?= $github ?>" target="_blank" rel="noopener" class="social-card-link github">
                            <i class="fab fa-github"></i>
                            <span>GitHub</span>
                        </a>
                        <?php endif; ?>
                        <?php if ($linkedin = getSetting('social_linkedin')): ?>
                        <a href="<?= $linkedin ?>" target="_blank" rel="noopener" class="social-card-link linkedin">
                            <i class="fab fa-linkedin-in"></i>
                            <span>LinkedIn</span>
                        </a>
                        <?php endif; ?>
                        <?php if ($twitter = getSetting('social_twitter')): ?>
                        <a href="<?= $twitter ?>" target="_blank" rel="noopener" class="social-card-link twitter">
                            <i class="fab fa-twitter"></i>
                            <span>Twitter</span>
                        </a>
                        <?php endif; ?>
                        <?php if ($instagram = getSetting('social_instagram')): ?>
                        <a href="<?= $instagram ?>" target="_blank" rel="noopener" class="social-card-link instagram">
                            <i class="fab fa-instagram"></i>
                            <span>Instagram</span>
                        </a>
                        <?php endif; ?>
                        <?php if ($dribbble = getSetting('social_dribbble')): ?>
                        <a href="<?= $dribbble ?>" target="_blank" rel="noopener" class="social-card-link dribbble">
                            <i class="fab fa-dribbble"></i>
                            <span>Dribbble</span>
                        </a>
                        <?php endif; ?>
                        <?php if ($youtube = getSetting('social_youtube')): ?>
                        <a href="<?= $youtube ?>" target="_blank" rel="noopener" class="social-card-link youtube">
                            <i class="fab fa-youtube"></i>
                            <span>YouTube</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Availability Card -->
                <div class="availability-card glass">
                    <div class="availability-status">
                        <span class="status-dot online"></span>
                        <span>Currently Available</span>
                    </div>
                    <p>I'm open to freelance projects and collaborations. Response time: within 24 hours.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include TEMPLATES_PATH . '/footer.php'; ?>
