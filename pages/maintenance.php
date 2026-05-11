<?php
/**
 * Maintenance Mode Page
 * Shown when maintenance_mode is enabled and user is not admin
 */
$siteName = getSetting('site_name', APP_NAME);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance - <?= htmlspecialchars($siteName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #0a0a0f;
            color: #e0e0e0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .maintenance-container {
            text-align: center;
            padding: 40px 24px;
            max-width: 600px;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        .maintenance-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 32px;
            background: rgba(0, 102, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(0, 102, 255, 0.2);
            animation: pulse 2s ease-in-out infinite;
        }
        .maintenance-icon i {
            font-size: 48px;
            color: #0066ff;
        }
        .maintenance-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #fff, #a0a0b0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .maintenance-message {
            font-size: 1.1rem;
            color: #a0a0b0;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .maintenance-info {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 24px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            font-size: 0.9rem;
            color: #6b6b80;
        }
        .maintenance-info i {
            color: #f59e0b;
        }
        .bg-glow {
            position: fixed;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.15;
            pointer-events: none;
        }
        .bg-glow-1 {
            top: -100px;
            right: -100px;
            background: #0066ff;
        }
        .bg-glow-2 {
            bottom: -100px;
            left: -100px;
            background: #6600cc;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
    </style>
</head>
<body>
    <div class="bg-glow bg-glow-1"></div>
    <div class="bg-glow bg-glow-2"></div>
    
    <div class="maintenance-container">
        <div class="maintenance-icon">
            <i class="fas fa-wrench"></i>
        </div>
        <h1 class="maintenance-title">Under Maintenance</h1>
        <p class="maintenance-message">
            We're currently performing scheduled maintenance to improve your experience. 
            The site will be back online shortly. Thank you for your patience!
        </p>
        <div class="maintenance-info">
            <i class="fas fa-clock"></i>
            <span>Estimated downtime: ~30 minutes</span>
        </div>
    </div>
</body>
</html>
