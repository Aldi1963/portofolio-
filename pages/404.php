<?php
$pageTitle = '404 - Page Not Found';
include TEMPLATES_PATH . '/header.php';
?>

<section class="error-section">
    <div class="container">
        <div class="error-content" data-aos="fade-up">
            <div class="error-code">404</div>
            <h1 class="error-title">Page Not Found</h1>
            <p class="error-desc">The page you're looking for doesn't exist or has been moved.</p>
            <a href="<?= baseUrl() ?>" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</section>

<?php include TEMPLATES_PATH . '/footer.php'; ?>
