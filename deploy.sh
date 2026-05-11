#!/bin/bash
# ============================================================
# PORTFOLIO WEBSITE - ONE-CLICK VPS INSTALLER
# Tested on: Ubuntu 20.04 / 22.04 / 24.04, Debian 11/12
# 
# USAGE:
#   curl -sL https://raw.githubusercontent.com/Aldi1963/portofolio-/feature/portfolio-website/deploy.sh | bash
#
#   OR download & run:
#   wget https://raw.githubusercontent.com/Aldi1963/portofolio-/feature/portfolio-website/deploy.sh
#   chmod +x deploy.sh && ./deploy.sh
# ============================================================

set -e

# ============ CONFIGURATION ============
DOMAIN="$(curl -s ifconfig.me 2>/dev/null || echo '0.0.0.0')"
DB_NAME="portfolio_db"
DB_USER="portfolio_user"
DB_PASS="Prtfl_$(openssl rand -hex 6)"
APP_DIR="/var/www/portfolio"
REPO_URL="https://github.com/Aldi1963/portofolio-.git"
BRANCH="feature/portfolio-website"
# ========================================

# Colors
R='\033[0;31m'; G='\033[0;32m'; Y='\033[1;33m'; B='\033[0;34m'; C='\033[0;36m'; NC='\033[0m'

clear
echo -e "${B}"
echo "╔══════════════════════════════════════════════════════╗"
echo "║                                                      ║"
echo "║   🚀 PORTFOLIO WEBSITE - VPS AUTO INSTALLER          ║"
echo "║   PHP Native + MySQL | Production Ready              ║"
echo "║                                                      ║"
echo "╚══════════════════════════════════════════════════════╝"
echo -e "${NC}"
echo ""
echo -e "${C}Server IP:${NC} $DOMAIN"
echo -e "${C}Database:${NC}  $DB_NAME"
echo -e "${C}Install to:${NC} $APP_DIR"
echo ""
echo -e "${Y}Starting installation in 3 seconds...${NC}"
sleep 3

# ============ STEP 1: Update System ============
echo ""
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${Y}[1/9]${NC} Updating system packages..."
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
export DEBIAN_FRONTEND=noninteractive
apt update -qq && apt upgrade -y -qq
echo -e "${G}✓ System updated${NC}"

# ============ STEP 2: Install Packages ============
echo ""
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${Y}[2/9]${NC} Installing Apache, PHP 8.x, MySQL..."
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Detect PHP version available
PHP_VER=""
for v in 8.3 8.2 8.1 8.0; do
    if apt-cache show php${v} &>/dev/null; then
        PHP_VER=$v
        break
    fi
done

if [ -z "$PHP_VER" ]; then
    # Add PHP PPA if needed
    apt install -y -qq software-properties-common
    add-apt-repository -y ppa:ondrej/php
    apt update -qq
    PHP_VER="8.2"
fi

apt install -y -qq \
    apache2 \
    mysql-server \
    php${PHP_VER} \
    php${PHP_VER}-mysql \
    php${PHP_VER}-mbstring \
    php${PHP_VER}-xml \
    php${PHP_VER}-fileinfo \
    php${PHP_VER}-curl \
    php${PHP_VER}-gd \
    php${PHP_VER}-zip \
    php${PHP_VER}-intl \
    libapache2-mod-php${PHP_VER} \
    git \
    unzip \
    curl

echo -e "${G}✓ All packages installed (PHP ${PHP_VER})${NC}"

# ============ STEP 3: Configure Apache ============
echo ""
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${Y}[3/9]${NC} Configuring Apache..."
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

a2enmod rewrite headers expires deflate 2>/dev/null
systemctl restart apache2
echo -e "${G}✓ Apache configured with rewrite, headers, expires, deflate${NC}"

# ============ STEP 4: Configure MySQL ============
echo ""
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${Y}[4/9]${NC} Setting up MySQL database..."
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

systemctl start mysql
systemctl enable mysql

mysql -e "DROP DATABASE IF EXISTS ${DB_NAME};"
mysql -e "CREATE DATABASE ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "DROP USER IF EXISTS '${DB_USER}'@'localhost';"
mysql -e "CREATE USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo -e "${G}✓ Database '${DB_NAME}' created${NC}"
echo -e "${G}✓ User '${DB_USER}' created${NC}"

# ============ STEP 5: Clone Repository ============
echo ""
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${Y}[5/9]${NC} Downloading portfolio website..."
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

rm -rf ${APP_DIR}
git clone -b ${BRANCH} --depth 1 ${REPO_URL} ${APP_DIR}
echo -e "${G}✓ Repository cloned to ${APP_DIR}${NC}"

# ============ STEP 6: Import Database ============
echo ""
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${Y}[6/9]${NC} Importing database..."
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

mysql -u ${DB_USER} -p"${DB_PASS}" ${DB_NAME} < ${APP_DIR}/database/portfolio_db.sql
echo -e "${G}✓ Database imported successfully${NC}"

# ============ STEP 7: Configure Application ============
echo ""
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${Y}[7/9]${NC} Configuring application..."
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Create .env (minimal - everything else managed from admin panel)
cat > ${APP_DIR}/.env << EOF
# Database (required)
DB_HOST=localhost
DB_NAME=${DB_NAME}
DB_USER=${DB_USER}
DB_PASS=${DB_PASS}

# Application (required)
APP_NAME=MyPortfolio
APP_URL=http://${DOMAIN}
APP_ENV=production
APP_DEBUG=false
APP_LANG=id

# Security defaults
SESSION_LIFETIME=3600
CSRF_TOKEN_LIFETIME=3600
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_TIME=900
EOF

# Create upload directories
mkdir -p ${APP_DIR}/uploads/{images,projects,blog,testimonials,settings}
mkdir -p ${APP_DIR}/cache

# Set permissions
chown -R www-data:www-data ${APP_DIR}
chmod -R 755 ${APP_DIR}
chmod -R 775 ${APP_DIR}/uploads
chmod -R 775 ${APP_DIR}/cache
chmod 600 ${APP_DIR}/.env

# Mark as installed & remove installer
echo "$(date '+%Y-%m-%d %H:%M:%S')" > ${APP_DIR}/cache/.installed
rm -f ${APP_DIR}/install.php
rm -f ${APP_DIR}/deploy.sh
rm -f ${APP_DIR}/vps-install.sh

echo -e "${G}✓ Application configured${NC}"
echo -e "${G}✓ Directories & permissions set${NC}"

# ============ STEP 8: Configure Virtual Host ============
echo ""
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${Y}[8/9]${NC} Setting up Apache Virtual Host..."
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

cat > /etc/apache2/sites-available/portfolio.conf << EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    DocumentRoot ${APP_DIR}
    
    <Directory ${APP_DIR}>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # Block access to sensitive files
    <FilesMatch "\.(env|sql|md|git|sh|log)$">
        Require all denied
    </FilesMatch>
    
    # Block access to sensitive directories
    <Directory ${APP_DIR}/config>
        Require all denied
    </Directory>
    <Directory ${APP_DIR}/includes>
        Require all denied
    </Directory>
    <Directory ${APP_DIR}/database>
        Require all denied
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/portfolio_error.log
    CustomLog \${APACHE_LOG_DIR}/portfolio_access.log combined
</VirtualHost>
EOF

a2dissite 000-default.conf 2>/dev/null || true
a2ensite portfolio.conf
systemctl reload apache2

echo -e "${G}✓ Virtual Host configured${NC}"

# ============ STEP 9: Firewall ============
echo ""
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${Y}[9/9]${NC} Configuring firewall..."
echo -e "${B}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if command -v ufw &>/dev/null; then
    ufw allow 22/tcp 2>/dev/null || true
    ufw allow 80/tcp 2>/dev/null || true
    ufw allow 443/tcp 2>/dev/null || true
    echo -e "${G}✓ Firewall rules added (22, 80, 443)${NC}"
else
    echo -e "${Y}⚠ UFW not found, skipping firewall config${NC}"
fi

# ============ DONE! ============
echo ""
echo ""
echo -e "${G}╔══════════════════════════════════════════════════════╗${NC}"
echo -e "${G}║                                                      ║${NC}"
echo -e "${G}║   ✅ INSTALLATION COMPLETE!                          ║${NC}"
echo -e "${G}║                                                      ║${NC}"
echo -e "${G}╚══════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${C}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "  ${G}🌐 Website:${NC}    http://${DOMAIN}"
echo -e "  ${G}🔐 Admin:${NC}      http://${DOMAIN}/admin/login"
echo -e "  ${G}👤 Username:${NC}   admin"
echo -e "  ${G}🔑 Password:${NC}   admin123"
echo -e "${C}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${C}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "  ${Y}Database Credentials (save this!):${NC}"
echo -e "  DB Name:     ${DB_NAME}"
echo -e "  DB User:     ${DB_USER}"
echo -e "  DB Password: ${DB_PASS}"
echo -e "${C}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${Y}⚠️  PENTING - Lakukan setelah install:${NC}"
echo -e "  1. Buka http://${DOMAIN}/admin/login"
echo -e "  2. Login dengan admin / admin123"
echo -e "  3. Segera ganti password di: Admin → Change Password"
echo -e "  4. Atur SMTP & integrasi di: Admin → Settings"
echo -e ""
echo -e "${Y}📧 Setup SMTP Gmail:${NC}"
echo -e "  Admin → Settings → tab Email/SMTP → isi credential"
echo -e ""
echo -e "${Y}🔒 (Opsional) Install SSL gratis:${NC}"
echo -e "  apt install certbot python3-certbot-apache -y"
echo -e "  certbot --apache -d yourdomain.com"
echo ""
echo -e "${G}Selamat! Website Anda sudah live! 🎉${NC}"
echo ""
