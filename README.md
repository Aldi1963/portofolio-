# Portfolio Website - Professional Modern PHP

A professional, modern portfolio website built with **PHP Native + MySQL** featuring glassmorphism UI, dark/light mode, full admin dashboard, blog system, and more. Production-ready for freelancers, web developers, designers, and professionals.

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-green)

## Features

### Frontend
- Modern dark/light theme with glassmorphism design
- Fully responsive (mobile-first)
- Hero section with typing animation & particle effects
- Portfolio grid with category filter & AJAX load more
- Blog system with search, categories, tags, and comments
- Services page with pricing cards & FAQ accordion
- Contact form with validation & email notification
- Smooth scroll, AOS animations, counter animations
- Dynamic SEO meta tags & Open Graph support
- Auto-generated sitemap.xml
- WhatsApp floating button
- Newsletter subscription
- Multi-language (ID/EN)

### Admin Dashboard
- Secure login with rate limiting
- Dashboard with visitor statistics
- CRUD: Projects, Blog, Services, Testimonials
- Message inbox management
- Site settings manager
- Image upload with validation
- CSRF protection on all forms

### Security
- PDO Prepared Statements (SQL injection prevention)
- CSRF token protection
- XSS filtering & output escaping
- Password hashing (bcrypt)
- Rate limiting on login & forms
- Secure session configuration
- Security headers (X-Frame, HSTS, etc.)
- File upload MIME validation

## Requirements

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite enabled
- PHP Extensions: pdo, pdo_mysql, mbstring, fileinfo, json

## Quick Installation

### Method 1: Auto Installer (Recommended)

1. Upload all files to your web server
2. Open `http://yourdomain.com/install.php` in browser
3. Fill in database credentials
4. Click "Install Now"
5. **Delete `install.php` after installation**

### Method 2: Manual Installation

1. **Clone the repository:**
```bash
git clone https://github.com/Aldi1963/portofolio-.git
cd portofolio-
```

2. **Create MySQL database:**
```bash
mysql -u root -p -e "CREATE DATABASE portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
```

3. **Import database:**
```bash
mysql -u root -p portfolio_db < database/portfolio_db.sql
```

4. **Configure environment:**
```bash
cp .env.example .env
# Edit .env with your database credentials and site URL
```

5. **Set permissions:**
```bash
chmod 755 uploads/ cache/
chmod 644 .env
```

6. **Configure Apache Virtual Host:**
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/portofolio-
    
    <Directory /var/www/portofolio->
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

7. **Enable mod_rewrite:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## VPS Deployment (Ubuntu/Debian)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install LAMP stack
sudo apt install apache2 mysql-server php8.1 php8.1-mysql php8.1-mbstring php8.1-xml php8.1-fileinfo libapache2-mod-php8.1 -y

# Enable modules
sudo a2enmod rewrite
sudo systemctl restart apache2

# Clone project
cd /var/www
sudo git clone https://github.com/Aldi1963/portofolio-.git portfolio
sudo chown -R www-data:www-data portfolio/
sudo chmod -R 755 portfolio/uploads portfolio/cache

# Set up database
sudo mysql -u root -e "CREATE DATABASE portfolio_db; CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'your_password'; GRANT ALL ON portfolio_db.* TO 'portfolio_user'@'localhost'; FLUSH PRIVILEGES;"
sudo mysql -u root portfolio_db < /var/www/portfolio/database/portfolio_db.sql

# Configure .env
cd /var/www/portfolio
sudo cp .env.example .env
sudo nano .env  # Update DB credentials and APP_URL

# Set up Apache Virtual Host
sudo nano /etc/apache2/sites-available/portfolio.conf
# Add VirtualHost config pointing to /var/www/portfolio

sudo a2ensite portfolio.conf
sudo systemctl reload apache2
```

## cPanel Shared Hosting

1. Upload files to `public_html/` via File Manager or FTP
2. Create database in cPanel > MySQL Databases
3. Import `database/portfolio_db.sql` via phpMyAdmin
4. Edit `.env` with database credentials
5. Ensure `.htaccess` is present in root
6. Access `yourdomain.com/install.php` or your site directly

## Default Admin Credentials

| Field | Value |
|-------|-------|
| URL | `yourdomain.com/admin/login` |
| Username | `admin` |
| Password | `admin123` |

**Change your password immediately after first login!**

## Project Structure

```
portofolio-/
├── assets/
│   ├── css/          # Stylesheets
│   ├── js/           # JavaScript files
│   └── images/       # Static images
├── config/
│   ├── app.php       # Application config & env loader
│   ├── database.php  # PDO database class
│   ├── routes.php    # URL routing map
│   └── lang/         # Language files (en, id)
├── database/
│   └── portfolio_db.sql  # Full database schema + seed data
├── includes/
│   ├── helpers.php   # Utility functions
│   ├── security.php  # CSRF, XSS, rate limiting
│   └── session.php   # Authentication management
├── pages/
│   ├── home.php      # Homepage
│   ├── about.php     # About page
│   ├── portfolio.php # Portfolio listing
│   ├── blog.php      # Blog listing
│   ├── services.php  # Services & pricing
│   ├── contact.php   # Contact form
│   ├── admin/        # Admin panel pages
│   └── api/          # AJAX API endpoints
├── templates/
│   ├── header.php    # Public header + nav
│   ├── footer.php    # Public footer
│   ├── admin-header.php  # Admin layout header
│   └── admin-footer.php  # Admin layout footer
├── uploads/          # User uploaded files
├── cache/            # Cache files
├── .env              # Environment configuration
├── .htaccess         # Apache rewrite rules
├── index.php         # Application entry point
├── install.php       # Auto installer
├── robots.txt        # Search engine directives
└── README.md         # This file
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/contact` | Submit contact form |
| POST | `/api/newsletter` | Subscribe to newsletter |
| POST | `/api/comments` | Post blog comment |
| GET | `/api/projects?page=&category=` | Load more projects |
| GET | `/api/visitors?period=` | Visitor stats (admin) |

## Configuration

All configuration is in the `.env` file:

| Variable | Description |
|----------|-------------|
| `DB_HOST` | Database server host |
| `DB_NAME` | Database name |
| `DB_USER` | Database username |
| `DB_PASS` | Database password |
| `APP_URL` | Your website URL |
| `APP_LANG` | Language (id/en) |
| `MAIL_*` | SMTP email settings |
| `RECAPTCHA_*` | Google reCAPTCHA keys |
| `WHATSAPP_NUMBER` | WhatsApp number (with country code) |
| `GA_TRACKING_ID` | Google Analytics ID |

## Customization

- **Colors:** Edit CSS variables in `assets/css/style.css`
- **Content:** Use Admin Dashboard > Settings
- **Language:** Edit files in `config/lang/`
- **Pages:** Modify files in `pages/` directory

## Tech Stack

- **Backend:** PHP 8+ Native (No Framework)
- **Database:** MySQL with PDO
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **CSS:** Custom with CSS Variables, Glassmorphism
- **Icons:** Font Awesome 6
- **Fonts:** Inter, JetBrains Mono
- **Animation:** AOS (Animate On Scroll)
- **Architecture:** Simple MVC-like structure

## License

MIT License - Free for personal and commercial use.

## Author

Made with ❤️ by **Aldi**

---

*If you find this useful, please star the repository!*
