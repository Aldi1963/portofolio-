-- =============================================
-- Seed: Project Categories & Dummy Projects
-- Run: mysql -u root portfolio_db < database/seed_projects.sql
-- =============================================

-- Insert project categories
INSERT INTO `categories` (`name`, `slug`, `type`, `color`, `icon`, `sort_order`, `is_active`) VALUES
('Web App', 'web-app', 'project', '#ff6b00', 'fas fa-globe', 1, 1),
('Mobile App', 'mobile-app', 'project', '#3b82f6', 'fas fa-mobile-alt', 2, 1),
('UI/UX Design', 'ui-ux-design', 'project', '#8b5cf6', 'fas fa-palette', 3, 1)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Insert dummy projects
INSERT INTO `projects` (`category_id`, `title`, `slug`, `short_description`, `description`, `thumbnail`, `demo_url`, `github_url`, `technologies`, `client_name`, `project_date`, `is_featured`, `is_active`, `sort_order`) VALUES
(
    (SELECT id FROM categories WHERE slug = 'web-app' LIMIT 1),
    'E-Commerce Platform',
    'e-commerce-platform',
    'Full-featured online store with payment integration and admin dashboard.',
    'A modern e-commerce platform built from scratch with PHP. Features include product catalog, shopping cart, Midtrans payment gateway, order management, and a comprehensive admin panel for managing products, orders, and customers.',
    'uploads/projects/ecommerce-app.png',
    'https://demo.example.com/ecommerce',
    'https://github.com/example/ecommerce',
    'PHP, MySQL, JavaScript, Bootstrap, Midtrans API',
    'PT Toko Digital',
    '2025-12-15',
    1, 1, 1
),
(
    (SELECT id FROM categories WHERE slug = 'web-app' LIMIT 1),
    'Task Manager Pro',
    'task-manager-pro',
    'Collaborative task management app with real-time updates and team features.',
    'A productivity tool for teams to manage projects and tasks. Includes Kanban boards, deadline tracking, file attachments, team chat, and progress analytics. Built with a focus on speed and user experience.',
    'uploads/projects/task-manager.png',
    'https://demo.example.com/taskmanager',
    'https://github.com/example/taskmanager',
    'PHP, MySQL, Vue.js, WebSocket, Tailwind CSS',
    'Startup Produktif',
    '2025-10-20',
    1, 1, 2
),
(
    (SELECT id FROM categories WHERE slug = 'mobile-app' LIMIT 1),
    'Social Media Dashboard',
    'social-media-dashboard',
    'Analytics dashboard for managing multiple social media accounts.',
    'A centralized dashboard to monitor and manage social media presence across platforms. Features include post scheduling, engagement analytics, audience insights, and automated reporting with beautiful data visualizations.',
    'uploads/projects/social-media.png',
    'https://demo.example.com/social',
    'https://github.com/example/social',
    'React Native, Node.js, MongoDB, Chart.js, REST API',
    'Agency Kreatif',
    '2025-08-10',
    1, 1, 3
),
(
    (SELECT id FROM categories WHERE slug = 'web-app' LIMIT 1),
    'Weather Forecast App',
    'weather-forecast-app',
    'Beautiful weather app with 7-day forecast and location-based alerts.',
    'A sleek weather application that provides accurate forecasts using OpenWeatherMap API. Features include current conditions, hourly/daily forecasts, severe weather alerts, interactive maps, and multiple location support.',
    'uploads/projects/weather-app.png',
    'https://demo.example.com/weather',
    'https://github.com/example/weather',
    'JavaScript, OpenWeatherMap API, CSS3, PWA, Geolocation',
    'Personal Project',
    '2025-06-05',
    0, 1, 4
),
(
    (SELECT id FROM categories WHERE slug = 'ui-ux-design' LIMIT 1),
    'Blog Platform',
    'blog-platform',
    'Modern blogging platform with markdown editor and SEO optimization.',
    'A content management system designed for bloggers and writers. Features include a distraction-free markdown editor, SEO tools, social sharing, comment system, newsletter integration, and beautiful responsive themes.',
    'uploads/projects/blog-platform.png',
    'https://demo.example.com/blog',
    'https://github.com/example/blog',
    'PHP, MySQL, Markdown, TailwindCSS, Alpine.js',
    'Media Online',
    '2025-04-18',
    1, 1, 5
),
(
    (SELECT id FROM categories WHERE slug = 'mobile-app' LIMIT 1),
    'Fitness Tracker',
    'fitness-tracker',
    'Health & fitness tracking app with workout plans and progress charts.',
    'A comprehensive fitness companion app that tracks workouts, nutrition, and health metrics. Includes custom workout plans, calorie counter, progress photos, achievement badges, and integration with wearable devices.',
    'uploads/projects/fitness-tracker.png',
    'https://demo.example.com/fitness',
    'https://github.com/example/fitness',
    'Flutter, Firebase, Dart, Google Fit API, Charts',
    'GymFit Indonesia',
    '2025-02-28',
    0, 1, 6
);
