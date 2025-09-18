# 🚀 DEPLOYMENT GUIDE
## Sistem Absensi SMKN 1 Punggelan

*Panduan lengkap untuk deployment sistem absensi ke production*

---

## 📋 **TABLE OF CONTENTS**
1. [Prerequisites](#prerequisites)
2. [Environment Setup](#environment-setup)
3. [Database Setup](#database-setup)
4. [Application Deployment](#application-deployment)
5. [Web Server Configuration](#web-server-configuration)
6. [SSL Configuration](#ssl-configuration)
7. [Performance Optimization](#performance-optimization)
8. [Monitoring Setup](#monitoring-setup)
9. [Backup Strategy](#backup-strategy)
10. [Rollback Procedures](#rollback-procedures)
11. [Troubleshooting](#troubleshooting)

---

## 📋 **PREREQUISITES**

### **Server Requirements**
- **OS**: Ubuntu 22.04 LTS or CentOS 8+
- **CPU**: 2+ cores (4+ recommended)
- **RAM**: 4GB minimum (8GB+ recommended)
- **Storage**: 50GB+ SSD storage
- **Network**: 100Mbps+ internet connection

### **Software Requirements**
- **Web Server**: Nginx 1.20+ or Apache 2.4+
- **PHP**: 8.2+ with required extensions
- **Database**: MySQL 8.0+ or PostgreSQL 13+
- **Node.js**: 18+ (for asset compilation)
- **Redis**: 6+ (optional, for caching/queues)

### **PHP Extensions**
```bash
# Required PHP extensions
php8.2-cli php8.2-fpm php8.2-mysql php8.2-pgsql php8.2-sqlite3 \
php8.2-redis php8.2-memcached php8.2-xml php8.2-curl php8.2-zip \
php8.2-gd php8.2-mbstring php8.2-bcmath php8.2-intl
```

### **Security Tools**
- **Firewall**: UFW or firewalld
- **SSL Certificate**: Let's Encrypt or commercial SSL
- **Monitoring**: Prometheus + Grafana (optional)

---

## 🛠️ **ENVIRONMENT SETUP**

### **1. Server Preparation**
```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install essential tools
sudo apt install -y curl wget git unzip software-properties-common

# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP and extensions
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-mysql \
php8.2-pgsql php8.2-sqlite3 php8.2-redis php8.2-memcached \
php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-mbstring \
php8.2-bcmath php8.2-intl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install Nginx
sudo apt install -y nginx

# Install MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation

# Install Redis (optional)
sudo apt install -y redis-server
```

### **2. User Setup**
```bash
# Create deployment user
sudo useradd -m -s /bin/bash deploy
sudo usermod -aG www-data deploy
sudo usermod -aG sudo deploy

# Setup SSH key authentication
sudo mkdir -p /home/deploy/.ssh
sudo chown deploy:deploy /home/deploy/.ssh
sudo chmod 700 /home/deploy/.ssh

# Add your public key to authorized_keys
echo "your_public_key_here" | sudo tee /home/deploy/.ssh/authorized_keys
sudo chown deploy:deploy /home/deploy/.ssh/authorized_keys
sudo chmod 600 /home/deploy/.ssh/authorized_keys
```

### **3. Directory Structure**
```bash
# Create application directory
sudo mkdir -p /var/www/absensi
sudo chown deploy:www-data /var/www/absensi
sudo chmod 755 /var/www/absensi

# Create shared directories
sudo mkdir -p /var/www/absensi/shared/{storage,bootstrap/cache}
sudo chown -R deploy:www-data /var/www/absensi/shared
sudo chmod -R 775 /var/www/absensi/shared

# Create logs directory
sudo mkdir -p /var/log/absensi
sudo chown deploy:www-data /var/log/absensi
```

---

## 🗄️ **DATABASE SETUP**

### **1. MySQL Configuration**
```bash
# Create database and user
sudo mysql -u root -p

CREATE DATABASE absensi_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'absensi_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON absensi_production.* TO 'absensi_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### **2. Database Optimization**
```sql
-- MySQL optimization for production
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
query_cache_size = 256M
query_cache_type = 1
tmp_table_size = 256M
max_heap_table_size = 256M
```

### **3. PostgreSQL Setup (Alternative)**
```bash
# Install PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Create database and user
sudo -u postgres psql

CREATE DATABASE absensi_production;
CREATE USER absensi_user WITH ENCRYPTED PASSWORD 'strong_password_here';
GRANT ALL PRIVILEGES ON DATABASE absensi_production TO absensi_user;
ALTER USER absensi_user CREATEDB;
\q
```

---

## 📦 **APPLICATION DEPLOYMENT**

### **1. Code Deployment**
```bash
# As deploy user
su - deploy

# Clone repository
cd /var/www/absensi
git clone https://github.com/idiarso4/absensi-face-recognition.git .

# Or if using deployment keys
git clone git@github.com:idiarso4/absensi-face-recognition.git .

# Create releases directory structure
mkdir -p releases shared
cd releases
```

### **2. Environment Configuration**
```bash
# Copy environment file
cp .env.example .env.production

# Edit production environment
nano .env.production

# Production .env content
APP_NAME="Sistem Absensi SMKN 1 Punggelan"
APP_ENV=production
APP_KEY=base64:your_app_key_here
APP_DEBUG=false
APP_URL=https://absensi.smkn1punggelan.sch.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_production
DB_USERNAME=absensi_user
DB_PASSWORD=your_secure_password

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email@domain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email@domain.com
MAIL_FROM_NAME="Sistem Absensi SMKN 1 Punggelan"
```

### **3. Dependency Installation**
```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies
npm ci

# Build assets for production
npm run build

# Or for development builds
npm run dev
```

### **4. Application Setup**
```bash
# Generate application key
php artisan key:generate

# Create symbolic links
php artisan storage:link

# Run database migrations
php artisan migrate --force

# Seed the database
php artisan db:seed --force

# Setup Filament admin panel
php artisan shield:install --force
php artisan shield:generate --all --panel=admin --no-interaction

# Clear and cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R deploy:www-data .
```

---

## 🌐 **WEB SERVER CONFIGURATION**

### **1. Nginx Configuration**
```nginx
# /etc/nginx/sites-available/absensi
server {
    listen 80;
    server_name absensi.smkn1punggelan.sch.id;
    root /var/www/absensi/current/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

    # Handle PHP files
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Handle static files
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Handle Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # API rate limiting
    location /api/ {
        limit_req zone=api burst=10 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Admin panel protection
    location /admin {
        # Additional security measures can be added here
        try_files $uri $uri/ /index.php?$query_string;
    }
}

# Rate limiting zones
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;
```

### **2. PHP-FPM Configuration**
```ini
# /etc/php/8.2/fpm/pool.d/absensi.conf
[absensi]

user = deploy
group = www-data
listen = /var/run/php/php8.2-fpm-absensi.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.process_idle_timeout = 10s

php_admin_value[upload_max_filesize] = 10M
php_admin_value[post_max_size] = 10M
php_admin_value[max_execution_time] = 300
php_admin_value[memory_limit] = 256M

env[APP_ENV] = production
env[APP_KEY] = your_app_key_here
```

### **3. Apache Configuration (Alternative)**
```apache
# /etc/apache2/sites-available/absensi.conf
<VirtualHost *:80>
    ServerName absensi.smkn1punggelan.sch.id
    DocumentRoot /var/www/absensi/current/public

    <Directory /var/www/absensi/current/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/absensi_error.log
    CustomLog ${APACHE_LOG_DIR}/absensi_access.log combined

    # Security headers
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options nosniff
</VirtualHost>
```

---

## 🔒 **SSL CONFIGURATION**

### **1. Let's Encrypt SSL**
```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d absensi.smkn1punggelan.sch.id

# Setup auto-renewal
sudo crontab -e
# Add this line:
# 0 12 * * * /usr/bin/certbot renew --quiet
```

### **2. SSL Configuration**
```nginx
# Update Nginx config for SSL
server {
    listen 443 ssl http2;
    server_name absensi.smkn1punggelan.sch.id;

    ssl_certificate /etc/letsencrypt/live/absensi.smkn1punggelan.sch.id/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/absensi.smkn1punggelan.sch.id/privkey.pem;

    # SSL security settings
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # HSTS
    add_header Strict-Transport-Security "max-age=63072000; includeSubDomains; preload" always;

    # Rest of your configuration...
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name absensi.smkn1punggelan.sch.id;
    return 301 https://$server_name$request_uri;
}
```

---

## ⚡ **PERFORMANCE OPTIMIZATION**

### **1. Laravel Optimization**
```bash
# Optimize Laravel for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize Composer autoloader
composer install --optimize-autoloader --no-dev

# Pre-compile assets
npm run build
```

### **2. Database Optimization**
```sql
-- Create indexes for better performance
CREATE INDEX idx_users_email ON users (email);
CREATE INDEX idx_users_is_approved ON users (is_approved);
CREATE INDEX idx_attendance_user_date ON attendance (user_id, date);
CREATE INDEX idx_attendance_check_in ON attendance (check_in_time);
CREATE INDEX idx_leaves_user_status ON leaves (user_id, status);

-- Optimize tables
OPTIMIZE TABLE users, attendance, leaves, offices, schedules, shifts;
```

### **3. Caching Strategy**
```php
// Cache frequently accessed data
Cache::remember('offices', 3600, function () {
    return Office::all();
});

Cache::remember('schedules', 1800, function () {
    return Schedule::with('shifts')->get();
});
```

### **4. Queue Configuration**
```bash
# Setup queue worker
php artisan queue:work --sleep=3 --tries=3 --max-jobs=1000 --timeout=90

# Setup supervisor for queue management
sudo apt install -y supervisor

# Create supervisor config
echo "[program:absensi-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/absensi/current/artisan queue:work --sleep=3 --tries=3 --max-jobs=1000
directory=/var/www/absensi/current
autostart=true
autorestart=true
numprocs=2
user=deploy
redirect_stderr=true
stdout_logfile=/var/log/absensi/queue.log" > /etc/supervisor/conf.d/absensi-queue.conf

sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start absensi-queue:*
```

---

## 📊 **MONITORING SETUP**

### **1. Application Monitoring**
```bash
# Install Laravel Telescope (optional)
composer require laravel/telescope
php artisan telescope:install
php artisan migrate

# Setup basic monitoring
php artisan make:command MonitorSystem

# MonitorSystem command
class MonitorSystem extends Command
{
    public function handle()
    {
        // Check database connection
        try {
            DB::connection()->getPdo();
            $this->info('Database: OK');
        } catch (\Exception $e) {
            $this->error('Database: FAILED');
        }

        // Check storage permissions
        if (is_writable(storage_path())) {
            $this->info('Storage: OK');
        } else {
            $this->error('Storage: FAILED');
        }

        // Check queue status
        $pendingJobs = DB::table('jobs')->count();
        $this->info("Pending jobs: {$pendingJobs}");
    }
}
```

### **2. Server Monitoring**
```bash
# Install monitoring tools
sudo apt install -y htop iotop sysstat

# Setup log rotation
echo "/var/log/absensi/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 deploy www-data
}" > /etc/logrotate.d/absensi

# Monitor disk usage
df -h
du -sh /var/www/absensi
```

### **3. Health Check Endpoint**
```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'services' => [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ]
    ]);
});

private function checkDatabase()
{
    try {
        DB::connection()->getPdo();
        return 'ok';
    } catch (\Exception $e) {
        return 'error';
    }
}
```

---

## 💾 **BACKUP STRATEGY**

### **1. Database Backup**
```bash
# Create backup script
cat > /var/www/absensi/backup.sh << 'EOF'
#!/bin/bash

# Database backup
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/www/backups"
DB_NAME="absensi_production"
DB_USER="absensi_user"
DB_PASS="your_password"

mkdir -p $BACKUP_DIR

# MySQL backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/db_$DATE.sql

# Keep only last 30 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete

echo "Backup completed: $BACKUP_DIR/db_$DATE.sql.gz"
EOF

chmod +x /var/www/absensi/backup.sh
```

### **2. File Backup**
```bash
# Backup application files
tar -czf /var/www/backups/files_$(date +%Y%m%d_%H%M%S).tar.gz \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/logs' \
    /var/www/absensi/current
```

### **3. Automated Backups**
```bash
# Setup cron for daily backups
crontab -e
# Add these lines:
# 0 2 * * * /var/www/absensi/backup.sh
# 0 3 * * * tar -czf /var/www/backups/files_$(date +\%Y\%m\%d).tar.gz /var/www/absensi/current
```

### **4. Backup Testing**
```bash
# Test database restore
mysql -u absensi_user -p absensi_production < backup.sql

# Test file restore
tar -xzf files_backup.tar.gz -C /tmp/restore_test
```

---

## 🔄 **ROLLBACK PROCEDURES**

### **1. Application Rollback**
```bash
# As deploy user
cd /var/www/absensi

# List available releases
ls -la releases/

# Switch to previous release
ln -sfn releases/20231201_120000 current

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Restart services
sudo systemctl reload nginx
sudo systemctl reload php8.2-fpm
```

### **2. Database Rollback**
```bash
# Rollback migrations
php artisan migrate:rollback --step=1

# Or rollback to specific migration
php artisan migrate:rollback --path=database/migrations/2023_12_01_000000_create_attendance_table.php
```

### **3. Emergency Rollback Script**
```bash
cat > /var/www/absensi/rollback.sh << 'EOF'
#!/bin/bash

echo "Starting emergency rollback..."

# Stop queue workers
sudo supervisorctl stop absensi-queue:*

# Switch to maintenance mode
php artisan down

# Rollback application
cd /var/www/absensi
PREVIOUS_RELEASE=$(ls releases/ | sort | tail -2 | head -1)
ln -sfn releases/$PREVIOUS_RELEASE current

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rollback database if needed
php artisan migrate:rollback --step=1

# Restart services
sudo systemctl reload nginx
sudo systemctl reload php8.2-fpm
sudo supervisorctl start absensi-queue:*

# Exit maintenance mode
php artisan up

echo "Rollback completed."
EOF

chmod +x /var/www/absensi/rollback.sh
```

---

## 🔧 **TROUBLESHOOTING**

### **Common Issues**

#### **1. Permission Issues**
```bash
# Fix storage permissions
sudo chown -R deploy:www-data /var/www/absensi/shared
sudo chmod -R 775 /var/www/absensi/shared

# Fix log permissions
sudo chown -R deploy:www-data /var/log/absensi
```

#### **2. Database Connection Issues**
```bash
# Check MySQL service
sudo systemctl status mysql

# Test database connection
php artisan tinker
DB::connection()->getPdo();
exit
```

#### **3. PHP-FPM Issues**
```bash
# Check PHP-FPM status
sudo systemctl status php8.2-fpm

# Check PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log
```

#### **4. Nginx Issues**
```bash
# Test Nginx configuration
sudo nginx -t

# Check Nginx logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log
```

#### **5. Application Issues**
```bash
# Check Laravel logs
tail -f /var/log/absensi/laravel.log

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Run diagnostics
php artisan about
```

### **Performance Issues**

#### **1. High CPU Usage**
```bash
# Check processes
htop

# Check PHP-FPM pool
sudo systemctl status php8.2-fpm

# Optimize PHP-FPM settings
# Adjust pm.max_children based on server resources
```

#### **2. High Memory Usage**
```bash
# Check memory usage
free -h

# Check largest processes
ps aux --sort=-%mem | head -10

# Optimize Laravel caching
php artisan config:cache
php artisan route:cache
```

#### **3. Slow Database Queries**
```bash
# Enable query logging
php artisan tinker
DB::enableQueryLog();
// Run some queries
dd(DB::getQueryLog());
```

### **Security Issues**

#### **1. SSL Certificate Expiry**
```bash
# Check certificate expiry
openssl s_client -connect absensi.smkn1punggelan.sch.id:443 -servername absensi.smkn1punggelan.sch.id < /dev/null 2>/dev/null | openssl x509 -noout -dates

# Renew certificate
sudo certbot renew
```

#### **2. Failed Login Attempts**
```bash
# Check authentication logs
grep "Failed login" /var/log/absensi/laravel.log

# Implement rate limiting if needed
# Check throttle middleware configuration
```

---

## 📞 **SUPPORT**

### **Emergency Contacts**
- **Lead Developer**: Idiarso Simbang
- **Email**: idiarsosimbang@gmail.com
- **Phone**: +62 812-3456-7890

### **Monitoring Alerts**
- Set up alerts for:
  - Server downtime
  - High CPU/memory usage
  - Database connection failures
  - SSL certificate expiry
  - Disk space warnings

### **Documentation Updates**
- Keep this deployment guide updated
- Document any custom configurations
- Update contact information as needed

---

*This deployment guide should be reviewed and updated regularly to reflect changes in the application and infrastructure.*