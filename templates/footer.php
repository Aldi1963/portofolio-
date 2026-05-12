<?php
/**
 * Public Footer Template
 */
?>
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand -->
                <div class="footer-col" data-aos="fade-up">
                    <a href="<?= baseUrl() ?>" class="footer-brand">
                        &lt;<span class="brand-highlight"><?= getSetting('owner_name', 'Aldi') ?></span>/&gt;
                    </a>
                    <p class="footer-desc">
                        <?= getSetting('owner_bio', 'Passionate developer crafting digital experiences.') ?>
                    </p>
                    <div class="footer-social">
                        <?php if ($github = getSetting('social_github')): ?>
                        <a href="<?= $github ?>" target="_blank" rel="noopener" aria-label="GitHub"><i class="fab fa-github"></i></a>
                        <?php endif; ?>
                        <?php if ($linkedin = getSetting('social_linkedin')): ?>
                        <a href="<?= $linkedin ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <?php endif; ?>
                        <?php if ($twitter = getSetting('social_twitter')): ?>
                        <a href="<?= $twitter ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if ($instagram = getSetting('social_instagram')): ?>
                        <a href="<?= $instagram ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if ($dribbble = getSetting('social_dribbble')): ?>
                        <a href="<?= $dribbble ?>" target="_blank" rel="noopener" aria-label="Dribbble"><i class="fab fa-dribbble"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-col" data-aos="fade-up" data-aos-delay="100">
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?= baseUrl('about') ?>"><?= lang('nav_about') ?></a></li>
                        <li><a href="<?= baseUrl('portfolio') ?>"><?= lang('nav_portfolio') ?></a></li>
                        <li><a href="<?= baseUrl('blog') ?>"><?= lang('nav_blog') ?></a></li>
                        <li><a href="<?= baseUrl('services') ?>"><?= lang('nav_services') ?></a></li>
                        <li><a href="<?= baseUrl('contact') ?>"><?= lang('nav_contact') ?></a></li>
                    </ul>
                </div>

                <!-- Services -->
                <div class="footer-col" data-aos="fade-up" data-aos-delay="200">
                    <h4 class="footer-title">Services</h4>
                    <ul class="footer-links">
                        <li><a href="<?= baseUrl('services') ?>">Web Development</a></li>
                        <li><a href="<?= baseUrl('services') ?>">UI/UX Design</a></li>
                        <li><a href="<?= baseUrl('services') ?>">Mobile App</a></li>
                        <li><a href="<?= baseUrl('services') ?>">Consulting</a></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div class="footer-col" data-aos="fade-up" data-aos-delay="300">
                    <h4 class="footer-title"><?= lang('footer_newsletter') ?></h4>
                    <p class="footer-desc">Get the latest updates and articles delivered to your inbox.</p>
                    <form class="newsletter-form" id="newsletter-form">
                        <div class="newsletter-input-group">
                            <input type="email" name="email" placeholder="<?= lang('footer_newsletter_placeholder') ?>" required>
                            <button type="submit" aria-label="Subscribe">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p><?= getSetting('footer_text', '&copy; ' . date('Y') . ' ' . getSetting('owner_name', 'Aldi') . '. All rights reserved.') ?></p>
                <p class="footer-credit">Crafted with <i class="fas fa-heart text-accent"></i> & <i class="fas fa-coffee text-accent"></i></p>
            </div>
        </div>
    </footer>

    <!-- WhatsApp Floating Button -->
    <?php if (!empty(config('whatsapp_number'))): ?>
    <a href="https://wa.me/<?= config('whatsapp_number') ?>?text=<?= urlencode(getSetting('whatsapp_message', 'Hello!')) ?>" 
       class="whatsapp-float" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
        <i class="fab fa-whatsapp"></i>
    </a>
    <?php endif; ?>

    <!-- Back to Top -->
    <button id="back-to-top" class="back-to-top" aria-label="Back to top">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- Toast Notification -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
    
    <?php if (!empty(config('recaptcha_site_key'))): ?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= config('recaptcha_site_key') ?>"></script>
    <?php endif; ?>

    <!-- Service Worker Registration -->
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js').then(function(registration) {
                console.log('SW registered:', registration.scope);
            }).catch(function(error) {
                console.log('SW registration failed:', error);
            });
        });
    }
    </script>
</body>
</html>
