-- =============================================
-- Portfolio Website Database Schema
-- Version: 1.0.0
-- PHP 8+ / MySQL 5.7+
-- =============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+07:00";

CREATE DATABASE IF NOT EXISTS `portfolio_db` 
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `portfolio_db`;

-- =============================================
-- Table: users (Admin accounts)
-- =============================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `avatar` VARCHAR(255) DEFAULT NULL,
    `role` ENUM('admin', 'editor') DEFAULT 'admin',
    `is_active` TINYINT(1) DEFAULT 1,
    `login_attempts` INT DEFAULT 0,
    `locked_until` DATETIME DEFAULT NULL,
    `last_login` DATETIME DEFAULT NULL,
    `remember_token` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: categories
-- =============================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE,
    `type` ENUM('project', 'blog') NOT NULL DEFAULT 'project',
    `description` TEXT DEFAULT NULL,
    `color` VARCHAR(7) DEFAULT '#0066ff',
    `icon` VARCHAR(50) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: projects
-- =============================================
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(220) NOT NULL UNIQUE,
    `short_description` VARCHAR(300) DEFAULT NULL,
    `description` TEXT NOT NULL,
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `demo_url` VARCHAR(500) DEFAULT NULL,
    `github_url` VARCHAR(500) DEFAULT NULL,
    `technologies` VARCHAR(500) DEFAULT NULL,
    `client_name` VARCHAR(100) DEFAULT NULL,
    `project_date` DATE DEFAULT NULL,
    `is_featured` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `views` INT UNSIGNED DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: project_images
-- =============================================
CREATE TABLE IF NOT EXISTS `project_images` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT UNSIGNED NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(200) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: blogs
-- =============================================
CREATE TABLE IF NOT EXISTS `blogs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(220) NOT NULL UNIQUE,
    `excerpt` VARCHAR(500) DEFAULT NULL,
    `content` LONGTEXT NOT NULL,
    `thumbnail` VARCHAR(255) DEFAULT NULL,
    `tags` VARCHAR(500) DEFAULT NULL,
    `meta_title` VARCHAR(200) DEFAULT NULL,
    `meta_description` VARCHAR(300) DEFAULT NULL,
    `status` ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    `is_featured` TINYINT(1) DEFAULT 0,
    `views` INT UNSIGNED DEFAULT 0,
    `published_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- =============================================
-- Table: comments
-- =============================================
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `blog_id` INT UNSIGNED NOT NULL,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `website` VARCHAR(255) DEFAULT NULL,
    `content` TEXT NOT NULL,
    `is_approved` TINYINT(1) DEFAULT 0,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`blog_id`) REFERENCES `blogs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_id`) REFERENCES `comments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: services
-- =============================================
CREATE TABLE IF NOT EXISTS `services` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(170) NOT NULL UNIQUE,
    `description` TEXT NOT NULL,
    `icon` VARCHAR(100) DEFAULT 'fas fa-code',
    `price` DECIMAL(12,2) DEFAULT NULL,
    `price_unit` VARCHAR(50) DEFAULT 'project',
    `features` TEXT DEFAULT NULL,
    `is_popular` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: testimonials
-- =============================================
CREATE TABLE IF NOT EXISTS `testimonials` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `client_name` VARCHAR(100) NOT NULL,
    `client_position` VARCHAR(100) DEFAULT NULL,
    `client_company` VARCHAR(100) DEFAULT NULL,
    `client_avatar` VARCHAR(255) DEFAULT NULL,
    `content` TEXT NOT NULL,
    `rating` TINYINT UNSIGNED DEFAULT 5,
    `project_id` INT UNSIGNED DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: contacts (Messages)
-- =============================================
CREATE TABLE IF NOT EXISTS `contacts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `is_replied` TINYINT(1) DEFAULT 0,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: settings
-- =============================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `setting_group` VARCHAR(50) DEFAULT 'general',
    `type` ENUM('text', 'textarea', 'image', 'boolean', 'number', 'json', 'password') DEFAULT 'text',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: visitors
-- =============================================
CREATE TABLE IF NOT EXISTS `visitors` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `page_visited` VARCHAR(255) DEFAULT NULL,
    `referrer` VARCHAR(500) DEFAULT NULL,
    `country` VARCHAR(50) DEFAULT NULL,
    `device_type` VARCHAR(20) DEFAULT NULL,
    `browser` VARCHAR(50) DEFAULT NULL,
    `visited_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip` (`ip_address`),
    INDEX `idx_date` (`visited_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: newsletter
-- =============================================
CREATE TABLE IF NOT EXISTS `newsletter` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `is_active` TINYINT(1) DEFAULT 1,
    `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `unsubscribed_at` DATETIME DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: activity_log
-- =============================================
CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `description` VARCHAR(500) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: skills
-- =============================================
CREATE TABLE IF NOT EXISTS `skills` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `percentage` TINYINT UNSIGNED DEFAULT 80,
    `category` VARCHAR(50) DEFAULT 'technical',
    `icon` VARCHAR(100) DEFAULT NULL,
    `color` VARCHAR(7) DEFAULT '#0066ff',
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: experience (Timeline)
-- =============================================
CREATE TABLE IF NOT EXISTS `experience` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `company` VARCHAR(150) DEFAULT NULL,
    `location` VARCHAR(100) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `type` ENUM('work', 'education', 'certification') DEFAULT 'work',
    `start_date` DATE NOT NULL,
    `end_date` DATE DEFAULT NULL,
    `is_current` TINYINT(1) DEFAULT 0,
    `sort_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: faqs
-- =============================================
CREATE TABLE IF NOT EXISTS `faqs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `question` VARCHAR(300) NOT NULL,
    `answer` TEXT NOT NULL,
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- =============================================
-- DEFAULT DATA INSERTS
-- =============================================

-- Default Admin User (password: admin123)
INSERT IGNORE INTO `users` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'admin@portfolio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Aldi Developer', 'admin');

-- Default Categories
INSERT IGNORE INTO `categories` (`name`, `slug`, `type`, `description`, `color`, `sort_order`) VALUES
('Web Development', 'web-development', 'project', 'Website and web application projects', '#0066ff', 1),
('Mobile App', 'mobile-app', 'project', 'Mobile application projects', '#00cc88', 2),
('UI/UX Design', 'ui-ux-design', 'project', 'User interface and experience design', '#ff6600', 3),
('Branding', 'branding', 'project', 'Brand identity and logo design', '#cc00ff', 4),
('Technology', 'technology', 'blog', 'Tech articles and tutorials', '#0066ff', 1),
('Tutorial', 'tutorial', 'blog', 'Step by step guides', '#00cc88', 2),
('Tips & Tricks', 'tips-tricks', 'blog', 'Quick tips for developers', '#ff6600', 3);

-- Default Settings
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `type`) VALUES
('site_name', 'Aldi Portfolio', 'general', 'text'),
('site_tagline', 'Full Stack Developer & Designer', 'general', 'text'),
('site_description', 'Professional portfolio website showcasing my projects, skills, and services as a full stack developer.', 'general', 'textarea'),
('site_keywords', 'web developer, full stack, portfolio, freelancer, designer', 'general', 'text'),
('site_logo', '', 'general', 'image'),
('site_favicon', '', 'general', 'image'),
('owner_name', 'Aldi', 'profile', 'text'),
('owner_title', 'Full Stack Developer', 'profile', 'text'),
('owner_email', 'hello@aldidev.com', 'profile', 'text'),
('owner_phone', '+62 812 3456 7890', 'profile', 'text'),
('owner_address', 'Jakarta, Indonesia', 'profile', 'text'),
('owner_bio', 'Passionate full stack developer with 5+ years of experience building modern web applications. Specialized in PHP, JavaScript, and cloud technologies.', 'profile', 'textarea'),
('owner_avatar', '', 'profile', 'image'),
('owner_cv', '', 'profile', 'text'),
('social_github', 'https://github.com/Aldi1963', 'social', 'text'),
('social_linkedin', 'https://linkedin.com/in/aldi', 'social', 'text'),
('social_twitter', 'https://twitter.com/aldi', 'social', 'text'),
('social_instagram', 'https://instagram.com/aldi', 'social', 'text'),
('social_dribbble', '', 'social', 'text'),
('social_youtube', '', 'social', 'text'),
('hero_title', 'Hi, I am Aldi', 'home', 'text'),
('hero_subtitle', 'Full Stack Developer | UI/UX Designer | Freelancer', 'home', 'text'),
('hero_description', 'I craft beautiful, functional, and scalable digital experiences that make an impact.', 'home', 'textarea'),
('hero_typing_texts', 'Web Developer,UI/UX Designer,Freelancer,Problem Solver', 'home', 'text'),
('stats_projects', '50', 'home', 'number'),
('stats_clients', '30', 'home', 'number'),
('stats_experience', '5', 'home', 'number'),
('stats_awards', '12', 'home', 'number'),
('footer_text', '© 2024 Aldi Portfolio. All rights reserved.', 'general', 'text'),
('google_maps_embed', '', 'contact', 'textarea'),
('whatsapp_default_message', 'Hello! I want to discuss a project with you.', 'contact', 'text'),

-- Default Skills
INSERT IGNORE INTO `skills` (`name`, `percentage`, `category`, `icon`, `color`, `sort_order`) VALUES
('PHP', 90, 'backend', 'fab fa-php', '#777BB4', 1),
('JavaScript', 85, 'frontend', 'fab fa-js', '#F7DF1E', 2),
('MySQL', 88, 'backend', 'fas fa-database', '#4479A1', 3),
('HTML/CSS', 95, 'frontend', 'fab fa-html5', '#E34F26', 4),
('React', 75, 'frontend', 'fab fa-react', '#61DAFB', 5),
('Node.js', 70, 'backend', 'fab fa-node-js', '#339933', 6),
('Laravel', 85, 'backend', 'fab fa-laravel', '#FF2D20', 7),
('Python', 65, 'backend', 'fab fa-python', '#3776AB', 8),
('Git', 88, 'tools', 'fab fa-git-alt', '#F05032', 9),
('Docker', 60, 'tools', 'fab fa-docker', '#2496ED', 10),
('Figma', 80, 'design', 'fab fa-figma', '#F24E1E', 11),
('Tailwind CSS', 85, 'frontend', 'fas fa-wind', '#06B6D4', 12);

-- Default Experience
INSERT IGNORE INTO `experience` (`title`, `company`, `location`, `description`, `type`, `start_date`, `end_date`, `is_current`, `sort_order`) VALUES
('Senior Full Stack Developer', 'Tech Startup Inc.', 'Jakarta, Indonesia', 'Leading development team, building scalable web applications using modern technologies. Managing CI/CD pipelines and cloud infrastructure.', 'work', '2022-01-01', NULL, 1, 1),
('Full Stack Developer', 'Digital Agency Co.', 'Bandung, Indonesia', 'Developed 20+ client websites and web applications. Specialized in PHP, Laravel, and JavaScript frameworks.', 'work', '2020-03-01', '2021-12-31', 0, 2),
('Junior Web Developer', 'Freelance', 'Remote', 'Started freelancing career building WordPress sites and custom PHP applications for small businesses.', 'work', '2019-01-01', '2020-02-28', 0, 3),
('Bachelor of Computer Science', 'Universitas Indonesia', 'Jakarta, Indonesia', 'Graduated with honors. Focused on software engineering and database systems.', 'education', '2015-09-01', '2019-07-01', 0, 4),
('AWS Certified Developer', 'Amazon Web Services', 'Online', 'Associate level certification for cloud development.', 'certification', '2023-06-01', '2026-06-01', 0, 5);

-- Default Services
INSERT IGNORE INTO `services` (`title`, `slug`, `description`, `icon`, `price`, `price_unit`, `features`, `is_popular`, `sort_order`) VALUES
('Web Development', 'web-development', 'Custom website development with modern technologies, responsive design, and optimized performance.', 'fas fa-code', 5000000, 'project', 'Responsive Design,SEO Optimized,Fast Loading,CMS Integration,1 Month Support', 0, 1),
('Full Stack Application', 'full-stack-application', 'Complete web application development from frontend to backend with database design and API development.', 'fas fa-layer-group', 15000000, 'project', 'Custom Architecture,API Development,Database Design,Admin Panel,3 Months Support,Source Code', 1, 2),
('UI/UX Design', 'ui-ux-design', 'Modern and intuitive user interface design with user experience research and prototyping.', 'fas fa-palette', 3000000, 'project', 'Wireframing,Prototyping,User Research,Design System,Figma Source File', 0, 3),
('Maintenance & Support', 'maintenance-support', 'Ongoing website maintenance, updates, bug fixes, and performance optimization.', 'fas fa-tools', 2000000, 'month', 'Bug Fixes,Security Updates,Performance Monitoring,Weekly Backups,Priority Support', 0, 4);

-- Default Testimonials
INSERT IGNORE INTO `testimonials` (`client_name`, `client_position`, `client_company`, `content`, `rating`, `sort_order`) VALUES
('Budi Santoso', 'CEO', 'TechVenture ID', 'Exceptional developer! Delivered our e-commerce platform ahead of schedule with outstanding quality. The code is clean, well-documented, and the performance is incredible.', 5, 1),
('Sarah Williams', 'Product Manager', 'Digital Solutions Co.', 'Working with Aldi was a fantastic experience. He understood our requirements perfectly and delivered a beautiful, functional application that exceeded our expectations.', 5, 2),
('Ahmad Rizky', 'Founder', 'StartupHub', 'Professional, responsive, and incredibly skilled. Aldi transformed our idea into a fully functional SaaS platform. Highly recommended for any web development project.', 5, 3),
('Lisa Chen', 'Marketing Director', 'GlobalBrand Asia', 'The portfolio website Aldi created for us perfectly captures our brand identity. Fast, beautiful, and SEO-optimized. Our organic traffic increased by 200%!', 4, 4);

-- Default Projects
INSERT IGNORE INTO `projects` (`category_id`, `title`, `slug`, `short_description`, `description`, `technologies`, `client_name`, `project_date`, `is_featured`, `sort_order`, `demo_url`, `github_url`) VALUES
(1, 'E-Commerce Platform', 'e-commerce-platform', 'A full-featured e-commerce platform with payment integration and inventory management.', 'Built a complete e-commerce solution featuring product management, shopping cart, secure checkout with multiple payment gateways, order tracking, and admin dashboard. Implemented real-time inventory management and automated email notifications.', 'PHP,Laravel,MySQL,Vue.js,Stripe,Redis', 'TechVenture ID', '2024-01-15', 1, 1, 'https://demo.example.com', 'https://github.com/Aldi1963'),
(1, 'Project Management App', 'project-management-app', 'A collaborative project management tool with real-time updates and team communication.', 'Developed a project management application with features including task boards, time tracking, file sharing, team chat, and automated reporting. Supports real-time collaboration using WebSockets.', 'React,Node.js,MongoDB,Socket.io,AWS', 'Digital Solutions Co.', '2023-11-20', 1, 2, 'https://demo.example.com', 'https://github.com/Aldi1963'),
(3, 'Finance Dashboard UI', 'finance-dashboard-ui', 'A modern financial dashboard with data visualization and analytics.', 'Designed and developed an intuitive financial dashboard with interactive charts, real-time data visualization, portfolio tracking, and customizable widgets. Dark mode supported with smooth animations.', 'Figma,React,D3.js,Tailwind CSS', 'GlobalBrand Asia', '2023-08-10', 1, 3, 'https://demo.example.com', ''),
(2, 'Health & Fitness App', 'health-fitness-app', 'A cross-platform mobile application for health tracking and workout planning.', 'Created a comprehensive health and fitness mobile app with workout planning, calorie tracking, progress photos, social features, and AI-powered recommendations. Available on iOS and Android.', 'React Native,Firebase,Node.js,TensorFlow Lite', 'StartupHub', '2023-05-01', 0, 4, 'https://demo.example.com', 'https://github.com/Aldi1963'),
(4, 'Brand Identity - TechFlow', 'brand-identity-techflow', 'Complete brand identity package for a tech startup.', 'Developed a comprehensive brand identity including logo design, color palette, typography system, business cards, social media templates, and brand guidelines document. Modern, clean, and versatile design that works across all media.', 'Illustrator,Photoshop,Figma,After Effects', 'TechFlow Startup', '2023-03-15', 0, 5, '', ''),
(1, 'Real Estate Platform', 'real-estate-platform', 'A property listing platform with advanced search and virtual tours.', 'Built a real estate platform with property listings, advanced filtering, map integration, virtual 3D tours, mortgage calculator, and agent management. Responsive design optimized for all devices.', 'PHP,MySQL,JavaScript,Google Maps API,Three.js', 'PropertyHub ID', '2024-03-01', 1, 6, 'https://demo.example.com', 'https://github.com/Aldi1963');

-- Default Blog Posts
INSERT IGNORE INTO `blogs` (`category_id`, `user_id`, `title`, `slug`, `excerpt`, `content`, `tags`, `status`, `is_featured`, `published_at`) VALUES
(5, 1, 'Building Modern Web Applications with PHP 8', 'building-modern-web-applications-php-8', 'Discover the latest features in PHP 8 and how to leverage them for building scalable, modern web applications.', '<h2>Introduction</h2><p>PHP 8 brings significant improvements to the language, making it more powerful and expressive than ever. In this article, we will explore the key features and best practices for building modern web applications.</p><h2>Key Features</h2><p>PHP 8 introduces named arguments, union types, attributes, match expressions, and the JIT compiler. These features allow developers to write cleaner, more maintainable code.</p><h2>Best Practices</h2><p>When building modern PHP applications, consider using:</p><ul><li>Strict typing for better code quality</li><li>PSR standards for consistency</li><li>Composer for dependency management</li><li>PDO for database operations</li><li>Environment variables for configuration</li></ul><h2>Conclusion</h2><p>PHP 8 is a modern, powerful language suitable for building enterprise-grade web applications. Embrace the new features and follow best practices for optimal results.</p>', 'PHP,Web Development,Backend,Programming', 'published', 1, NOW()),
(6, 1, 'Getting Started with Tailwind CSS', 'getting-started-tailwind-css', 'A comprehensive guide to using Tailwind CSS for rapid UI development with utility-first approach.', '<h2>What is Tailwind CSS?</h2><p>Tailwind CSS is a utility-first CSS framework that provides low-level utility classes to build custom designs without writing custom CSS. It is highly customizable and efficient.</p><h2>Installation</h2><p>You can install Tailwind via npm or use the CDN for quick prototyping. The framework works great with any build tool and frontend framework.</p><h2>Core Concepts</h2><p>The utility-first approach means you apply pre-existing classes directly in your HTML. This leads to faster development, consistent design, and smaller CSS bundles in production.</p><h2>Responsive Design</h2><p>Tailwind makes responsive design intuitive with mobile-first breakpoint prefixes like sm:, md:, lg:, and xl:.</p>', 'CSS,Frontend,Design,Tailwind', 'published', 0, NOW()),
(7, 1, '10 VS Code Extensions Every Developer Needs', '10-vscode-extensions-every-developer-needs', 'Boost your productivity with these essential VS Code extensions for web development.', '<h2>Essential Extensions</h2><p>Visual Studio Code is the most popular code editor, and its extension ecosystem makes it incredibly powerful. Here are 10 must-have extensions for every web developer.</p><h2>1. Prettier</h2><p>Automatic code formatting that keeps your codebase consistent.</p><h2>2. ESLint</h2><p>JavaScript linting to catch errors and enforce coding standards.</p><h2>3. GitLens</h2><p>Supercharge your Git capabilities within VS Code.</p><h2>4. Auto Rename Tag</h2><p>Automatically rename paired HTML/XML tags.</p><h2>5. Bracket Pair Colorizer</h2><p>Color-code matching brackets for better readability.</p>', 'Tools,VS Code,Productivity,Development', 'published', 0, NOW());

-- Default FAQs
INSERT IGNORE INTO `faqs` (`question`, `answer`, `sort_order`) VALUES
('What technologies do you work with?', 'I specialize in PHP, JavaScript, MySQL, Laravel, React, Vue.js, Node.js, and modern CSS frameworks like Tailwind CSS. I also have experience with cloud services (AWS, GCP) and DevOps tools.', 1),
('How long does a typical project take?', 'Project timelines vary depending on complexity. A simple website takes 1-2 weeks, while a full web application can take 1-3 months. I provide detailed timelines during the proposal phase.', 2),
('Do you offer maintenance after project completion?', 'Yes! I offer ongoing maintenance packages that include bug fixes, security updates, performance monitoring, and feature additions. Support starts from the day of project delivery.', 3),
('What is your payment structure?', 'I typically work with a 50% upfront deposit and 50% upon completion. For larger projects, we can arrange milestone-based payments. I accept bank transfer and various digital payment methods.', 4),
('Can you work with existing codebases?', 'Absolutely! I frequently work with existing projects for bug fixes, feature additions, performance optimization, and code refactoring. I will first review the codebase and provide recommendations.', 5),
('Do you sign NDAs?', 'Yes, I am happy to sign Non-Disclosure Agreements. Client confidentiality and data security are top priorities in all my projects.', 6);

-- =============================================
-- DYNAMIC CONFIG SETTINGS (Mail, Integration, Security)
-- These can be managed from Admin Dashboard
-- =============================================

INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `type`) VALUES
-- Mail Settings
('mail_host', 'smtp.gmail.com', 'mail', 'text'),
('mail_port', '587', 'mail', 'number'),
('mail_username', '', 'mail', 'text'),
('mail_password', '', 'mail', 'password'),
('mail_from', '', 'mail', 'text'),
('mail_from_name', 'MyPortfolio', 'mail', 'text'),
('mail_encryption', 'tls', 'mail', 'text'),

-- Integration Settings
('recaptcha_site_key', '', 'integration', 'text'),
('recaptcha_secret_key', '', 'integration', 'password'),
('ga_tracking_id', '', 'integration', 'text'),
('whatsapp_number', '6281234567890', 'integration', 'text'),
('whatsapp_message', 'Hello! I want to discuss a project with you.', 'integration', 'text'),
('tawk_to_id', '', 'integration', 'text'),
('facebook_pixel_id', '', 'integration', 'text'),

-- Google OAuth Login Settings
('google_oauth_enabled', '0', 'integration', 'boolean'),
('google_client_id', '', 'integration', 'text'),
('google_client_secret', '', 'integration', 'password'),
('google_allowed_emails', '', 'integration', 'textarea'),

-- Security Settings
('session_lifetime', '3600', 'security', 'number'),
('csrf_token_lifetime', '3600', 'security', 'number'),
('login_max_attempts', '5', 'security', 'number'),
('login_lockout_time', '900', 'security', 'number'),
('force_https', '0', 'security', 'boolean'),
('maintenance_mode', '0', 'security', 'boolean');

COMMIT;
