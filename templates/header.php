<?php
/**
 * Public Header Template
 * Includes meta tags, CSS, and navigation
 */
$pageTitle = $pageTitle ?? '';
$pageDescription = $pageDescription ?? '';
$pageImage = $pageImage ?? '';
$pageType = $pageType ?? 'website';
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="<?= APP_LANG ?>" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <?= metaTags($pageTitle, $pageDescription, $pageImage, $pageType) ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= asset('images/favicon.png') ?>">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- AOS Animation -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    
    <?php if (!empty(config('ga_tracking_id'))): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= config('ga_tracking_id') ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= config('ga_tracking_id') ?>');
    </script>
    <?php endif; ?>
</head>
<body class="<?= $bodyClass ?>">
    <!-- Preloader -->
    <div id="preloader">
        <div class="loader">
            <div class="loader-inner"></div>
        </div>
    </div>

    <!-- Theme Toggle (floating) -->
    <button id="theme-toggle" class="theme-toggle" aria-label="Toggle theme">
        <i class="fas fa-moon"></i>
        <i class="fas fa-sun"></i>
    </button>

    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="<?= baseUrl() ?>" class="navbar-brand">
                <span class="brand-text">&lt;<span class="brand-highlight"><?= getSetting('owner_name', 'Aldi') ?></span>/&gt;</span>
            </a>
            
            <button class="navbar-toggler" id="navbar-toggler" aria-label="Toggle navigation">
                <span class="toggler-line"></span>
                <span class="toggler-line"></span>
                <span class="toggler-line"></span>
            </button>
            
            <div class="navbar-menu" id="navbar-menu">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="<?= baseUrl() ?>" class="nav-link <?= $url === '' || $url === 'home' ? 'active' : '' ?>">
                            <?= lang('nav_home') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= baseUrl('about') ?>" class="nav-link <?= $url === 'about' ? 'active' : '' ?>">
                            <?= lang('nav_about') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= baseUrl('portfolio') ?>" class="nav-link <?= strpos($url, 'portfolio') === 0 ? 'active' : '' ?>">
                            <?= lang('nav_portfolio') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= baseUrl('blog') ?>" class="nav-link <?= strpos($url, 'blog') === 0 ? 'active' : '' ?>">
                            <?= lang('nav_blog') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= baseUrl('services') ?>" class="nav-link <?= $url === 'services' ? 'active' : '' ?>">
                            <?= lang('nav_services') ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= baseUrl('contact') ?>" class="nav-link <?= $url === 'contact' ? 'active' : '' ?>">
                            <?= lang('nav_contact') ?>
                        </a>
                    </li>
                </ul>
                <a href="<?= baseUrl('contact') ?>" class="btn btn-primary btn-nav">
                    <i class="fas fa-paper-plane"></i> Hire Me
                </a>
            </div>
        </div>
    </nav>
