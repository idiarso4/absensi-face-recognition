# 🔧 TROUBLESHOOTING GUIDE
## Sistem Absensi SMKN 1 Punggelan

*Panduan mengatasi masalah umum dan troubleshooting*

---

## 📋 **TABLE OF CONTENTS**
1. [Quick Diagnosis](#quick-diagnosis)
2. [Installation Issues](#installation-issues)
3. [Database Issues](#database-issues)
4. [Authentication Issues](#authentication-issues)
5. [Attendance Issues](#attendance-issues)
6. [API Issues](#api-issues)
7. [Performance Issues](#performance-issues)
8. [Security Issues](#security-issues)
9. [File Upload Issues](#file-upload-issues)
10. [Email Issues](#email-issues)
11. [Frontend Issues](#frontend-issues)
12. [Deployment Issues](#deployment-issues)
13. [Monitoring & Logs](#monitoring--logs)
14. [Emergency Procedures](#emergency-procedures)

---

## 🔍 **QUICK DIAGNOSIS**

### **System Health Check Script**
```bash
#!/bin/bash
# health_check.sh

echo "=== Sistem Absensi Health Check ==="
echo "Timestamp: $(date)"
echo

# Check PHP
echo "1. PHP Status:"
php --version | head -1
if php -r "echo 'PHP OK';" 2>/dev/null; then
    echo "✓ PHP is working"
else
    echo "✗ PHP is not working"
fi
echo

# Check Laravel
echo "2. Laravel Status:"
if php artisan --version >/dev/null 2>&1; then
    echo "✓ Laravel is accessible"
    php artisan --version
else
    echo "✗ Laravel is not accessible"
fi
echo

# Check Database
echo "3. Database Connection:"
if php artisan tinker --execute="echo 'Database OK';" >/dev/null 2>&1; then
    echo "✓ Database connection successful"
else
    echo "✗ Database connection failed"
fi
echo

# Check Storage
echo "4. Storage Permissions:"
if [ -w "storage/" ]; then
    echo "✓ Storage directory is writable"
else
    echo "✗ Storage directory is not writable"
fi
echo

# Check Queue
echo "5. Queue Status:"
if pgrep -f "queue:work" >/dev/null; then
    echo "✓ Queue worker is running"
else
    echo "✗ Queue worker is not running"
fi
echo

# Check Web Server
echo "6. Web Server Status:"
if curl -s http://localhost:8000 >/dev/null; then
    echo "✓ Web server is responding"
else
    echo "✗ Web server is not responding"
fi
echo

echo "=== Health Check Complete ==="
```

### **Automated Diagnosis**
```php
// routes/web.php - Add diagnostic route
Route::get('/diagnostics', function () {
    $diagnostics = [];

    // PHP Version
    $diagnostics['php_version'] = PHP_VERSION;

    // Laravel Version
    $diagnostics['laravel_version'] = app()->version();

    // Database Connection
    try {
        DB::connection()->getPdo();
        $diagnostics['database'] = 'Connected';
    } catch (\Exception $e) {
        $diagnostics['database'] = 'Failed: ' . $e->getMessage();
    }

    // Storage Permissions
    $diagnostics['storage_writable'] = is_writable(storage_path());

    // Cache Status
    $diagnostics['cache_working'] = Cache::store()->getStore() ? 'Working' : 'Failed';

    // Queue Status
    $diagnostics['queue_size'] = DB::table('jobs')->count();

    // Recent Errors
    $diagnostics['recent_errors'] = \Illuminate\Support\Facades\Log::getLogger()
        ->getHandlers()[0]->getRecords();

    return view('diagnostics', compact('diagnostics'));
});
```

---

## 🛠️ **INSTALLATION ISSUES**

### **Composer Issues**

#### **Problem: Composer memory limit**
```
Fatal error: Allowed memory size exhausted
```

**Solution:**
```bash
# Increase memory limit
php -d memory_limit=-1 /usr/local/bin/composer install

# Or add to composer.json
"config": {
    "sort-packages": true
},
"scripts": {
    "post-install-cmd": [
        "php artisan optimize:clear"
    ]
}
```

#### **Problem: Package conflicts**
```
Your requirements could not be resolved to an installable set of packages
```

**Solutions:**
```bash
# Clear composer cache
composer clear-cache

# Update composer
composer self-update

# Try with --ignore-platform-reqs
composer install --ignore-platform-reqs

# Check for conflicting versions
composer why package/name
composer why-not package/name
```

### **NPM Issues**

#### **Problem: Node version conflicts**
```
ERROR: npm is known not to run on Node.js v10.19.0
```

**Solution:**
```bash
# Install Node Version Manager
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc

# Install correct Node version
nvm install 18
nvm use 18

# Update npm
npm install -g npm@latest
```

#### **Problem: NPM install fails**
```
npm ERR! code ENOTFOUND
npm ERR! errno ENOTFOUND
```

**Solutions:**
```bash
# Clear npm cache
npm cache clean --force

# Use different registry
npm config set registry https://registry.npmjs.org/

# Try with different network
npm install --verbose

# Check network connectivity
ping registry.npmjs.org
```

### **Permission Issues**

#### **Problem: Storage not writable**
```
Error: Unable to write to storage directory
```

**Solutions:**
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data bootstrap/cache/
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# For development
sudo chown -R $USER:www-data storage/
sudo chown -R $USER:www-data bootstrap/cache/
```

---

## 🗄️ **DATABASE ISSUES**

### **Connection Issues**

#### **Problem: Database connection refused**
```
SQLSTATE[HY000] [2002] Connection refused
```

**Solutions:**
```bash
# Check if MySQL is running
sudo systemctl status mysql

# Start MySQL if stopped
sudo systemctl start mysql

# Check MySQL error logs
sudo tail -f /var/log/mysql/error.log

# Test connection manually
mysql -u username -p -h localhost database_name
```

#### **Problem: Access denied for user**
```
SQLSTATE[HY000] [1698] Access denied for user 'user'@'localhost'
```

**Solutions:**
```sql
-- Login as root and fix permissions
mysql -u root -p

-- Create user with proper permissions
CREATE USER 'absensi_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON absensi_production.* TO 'absensi_user'@'localhost';
FLUSH PRIVILEGES;

-- Or fix existing user
ALTER USER 'absensi_user'@'localhost' IDENTIFIED WITH mysql_native_password BY 'secure_password';
```

### **Migration Issues**

#### **Problem: Migration fails**
```
SQLSTATE[42S01]: Base table or view already exists
```

**Solutions:**
```bash
# Reset migrations
php artisan migrate:reset

# Fresh migration
php artisan migrate:fresh

# Status check
php artisan migrate:status

# Force migration
php artisan migrate --force
```

#### **Problem: Foreign key constraint fails**
```
SQLSTATE[23000]: Integrity constraint violation
```

**Solutions:**
```bash
# Check foreign key constraints
php artisan tinker
Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys('table_name');

# Disable foreign key checks temporarily
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
# Run migrations
DB::statement('SET FOREIGN_KEY_CHECKS=1;');
```

### **Data Issues**

#### **Problem: Data corruption**
```
SQLSTATE[HY000]: Incorrect string value
```

**Solutions:**
```sql
-- Check database charset
SHOW VARIABLES LIKE 'character_set%';

-- Convert database to utf8mb4
ALTER DATABASE absensi_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Convert tables
ALTER TABLE table_name CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 🔐 **AUTHENTICATION ISSUES**

### **Login Issues**

#### **Problem: User cannot login to admin panel**
```
User is not approved or does not have required role
```

**Solutions:**
```php
// Check user status in tinker
php artisan tinker

$user = App\Models\User::where('email', 'user@example.com')->first();
echo "Approved: " . ($user->is_approved ? 'Yes' : 'No') . "\n";
echo "Roles: " . $user->roles->pluck('name')->join(', ') . "\n";

// Approve user manually
$user->update(['is_approved' => true]);
$user->assignRole('employee');
```

#### **Problem: Password reset not working**
```
Invalid token or expired
```

**Solutions:**
```bash
# Clear password reset tokens
php artisan tinker
DB::table('password_resets')->truncate();

# Check mail configuration
php artisan config:show mail

# Test email sending
php artisan tinker
Mail::raw('Test email', function($msg) { $msg->to('test@example.com')->subject('Test'); });
```

### **API Authentication Issues**

#### **Problem: Invalid API token**
```
Unauthenticated
```

**Solutions:**
```bash
# Check token format
# Should be: Bearer your_token_here

# Verify token exists
php artisan tinker
$user = App\Models\User::find(1);
$tokens = $user->tokens;
foreach($tokens as $token) {
    echo $token->name . ': ' . $token->plain_text_token . "\n";
}

// Regenerate token
$user->tokens()->delete(); // Remove old tokens
$token = $user->createToken('API Token');
echo $token->plainTextToken;
```

---

## 📊 **ATTENDANCE ISSUES**

### **GPS Validation Issues**

#### **Problem: Location validation fails**
```
Location is outside office radius
```

**Solutions:**
```php
// Check office settings
php artisan tinker

$office = App\Models\Office::first();
echo "Office: {$office->name}\n";
echo "Location: {$office->latitude}, {$office->longitude}\n";
echo "Radius: {$office->radius} meters\n";

// Test distance calculation
$userLat = -6.2089;
$userLng = 106.8457;
$distance = // Implement Haversine formula
echo "Distance: {$distance} meters\n";
```

#### **Problem: GPS accuracy issues**
```
GPS coordinates are inaccurate
```

**Solutions:**
```javascript
// Improve GPS accuracy in mobile app
navigator.geolocation.getCurrentPosition(
    function(position) {
        console.log('Accuracy:', position.coords.accuracy);
        if (position.coords.accuracy > 100) {
            alert('GPS accuracy is low. Please wait for better signal.');
        }
    },
    null,
    {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 300000
    }
);
```

### **Time Tracking Issues**

#### **Problem: Check-in/out times are wrong**
```
Time zone mismatch
```

**Solutions:**
```php
// Check application timezone
php artisan tinker
echo config('app.timezone');

// Set correct timezone in .env
APP_TIMEZONE=Asia/Jakarta

// Update existing records
DB::table('attendance')
    ->whereNull('updated_at')
    ->update(['updated_at' => DB::raw('created_at')]);
```

#### **Problem: Automatic check-out not working**
```
Scheduled job not running
```

**Solutions:**
```bash
# Check if scheduler is running
crontab -l | grep artisan

# Add to crontab if missing
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1

# Test scheduler manually
php artisan schedule:run

# Check scheduled commands
php artisan schedule:list
```

---

## 🌐 **API ISSUES**

### **Rate Limiting Issues**

#### **Problem: API rate limit exceeded**
```
429 Too Many Requests
```

**Solutions:**
```php
// Check rate limiting configuration
php artisan config:show throttle

// Temporarily increase limits for testing
// In RouteServiceProvider.php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(1000); // Increased for testing
});

// Clear rate limiter cache
Cache::store('redis')->flush();
```

### **CORS Issues**

#### **Problem: CORS error in browser**
```
Access to XMLHttpRequest blocked by CORS policy
```

**Solutions:**
```php
// Update CORS configuration in config/cors.php
'allowed_origins' => ['*'], // For development
'allowed_origins' => ['https://yourdomain.com'], // For production

'allowed_headers' => ['*'],
'allowed_methods' => ['*'],
'supports_credentials' => true,
```

### **API Response Issues**

#### **Problem: API returns HTML instead of JSON**
```
Unexpected token < in JSON
```

**Solutions:**
```php
// Check if request accepts JSON
// Add Accept header: application/json

// Check for authentication middleware
Route::middleware(['auth:sanctum'])->group(function () {
    // API routes here
});

// Verify API routes are properly defined
php artisan route:list --path=api
```

---

## ⚡ **PERFORMANCE ISSUES**

### **Slow Page Loads**

#### **Problem: Pages load slowly**
```
Page load time > 3 seconds
```

**Solutions:**
```bash
# Enable Laravel optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check database queries
php artisan tinker
DB::enableQueryLog();
// Visit slow page
dd(DB::getQueryLog());

# Add database indexes
php artisan tinker
Schema::table('attendance', function (Blueprint $table) {
    $table->index(['user_id', 'date']);
});
```

### **Memory Issues**

#### **Problem: Out of memory errors**
```
Allowed memory size exhausted
```

**Solutions:**
```php
// Increase memory limit in php.ini
memory_limit = 512M

// Or in .htaccess
php_value memory_limit 512M

// Optimize memory usage in code
$attendances = Attendance::select('id', 'user_id', 'date')
    ->where('user_id', $userId)
    ->chunk(100, function ($chunk) {
        // Process chunk
    });
```

### **Database Performance**

#### **Problem: Slow database queries**
```
Query execution time > 1 second
```

**Solutions:**
```sql
-- Add indexes
CREATE INDEX idx_attendance_user_date ON attendance (user_id, date);
CREATE INDEX idx_attendance_check_in ON attendance (check_in_time);

-- Analyze query performance
EXPLAIN SELECT * FROM attendance WHERE user_id = 1 AND date >= '2023-01-01';

-- Optimize query
SELECT a.id, a.date, a.check_in_time, u.name
FROM attendance a
JOIN users u ON a.user_id = u.id
WHERE a.user_id = 1
ORDER BY a.date DESC
LIMIT 50;
```

---

## 🔒 **SECURITY ISSUES**

### **Permission Issues**

#### **Problem: Access denied errors**
```
403 Forbidden
```

**Solutions:**
```php
// Check user permissions
php artisan tinker

$user = App\Models\User::find(1);
echo "Roles: " . $user->roles->pluck('name')->join(', ') . "\n";
echo "Permissions: " . $user->getAllPermissions()->pluck('name')->join(', ') . "\n";

// Assign missing permissions
$user->assignRole('admin');
$user->givePermissionTo('view attendance');
```

### **CSRF Issues**

#### **Problem: CSRF token mismatch**
```
419 Page Expired
```

**Solutions:**
```php
// Check CSRF token in forms
<form method="POST">
    @csrf
    <!-- form fields -->
</form>

// Clear application cache
php artisan cache:clear
php artisan config:clear

// Check session configuration
php artisan config:show session
```

### **SSL Issues**

#### **Problem: SSL certificate errors**
```
SSL certificate problem
```

**Solutions:**
```bash
# Check certificate expiry
openssl s_client -connect yourdomain.com:443 -servername yourdomain.com < /dev/null 2>/dev/null | openssl x509 -noout -dates

# Renew Let's Encrypt certificate
sudo certbot renew

# Check certificate chain
openssl s_client -connect yourdomain.com:443 -servername yourdomain.com
```

---

## 📁 **FILE UPLOAD ISSUES**

### **Upload Size Issues**

#### **Problem: File too large**
```
File size exceeds limit
```

**Solutions:**
```php
// Update PHP limits in php.ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300

// Update Laravel configuration
'max_size' => 10240, // 10MB in KB

// Check nginx limits
client_max_body_size 10M;
```

### **Permission Issues**

#### **Problem: Cannot write uploaded files**
```
Unable to save file
```

**Solutions:**
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage/
chmod -R 775 storage/

# Check disk space
df -h

# Verify upload directory exists
ls -la storage/app/public/
```

### **Image Processing Issues**

#### **Problem: Image processing fails**
```
GD library not available
```

**Solutions:**
```bash
# Install GD extension
sudo apt-get install php8.2-gd

# Restart web server
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm

# Check GD support
php -r "var_dump(gd_info());"
```

---

## 📧 **EMAIL ISSUES**

### **Email Delivery Issues**

#### **Problem: Emails not sending**
```
Connection could not be established
```

**Solutions:**
```bash
# Check mail configuration
php artisan config:show mail

# Test email configuration
php artisan tinker
Mail::raw('Test email body', function($msg) {
    $msg->to('test@example.com')->subject('Test Email');
});

# Check SMTP server connectivity
telnet smtp.gmail.com 587
```

### **Email Template Issues**

#### **Problem: Email templates not rendering**
```
View not found
```

**Solutions:**
```php
// Publish mail templates
php artisan vendor:publish --tag=laravel-mail

// Clear view cache
php artisan view:clear

// Check template paths
ls -la resources/views/vendor/mail/
```

---

## 🎨 **FRONTEND ISSUES**

### **JavaScript Issues**

#### **Problem: JavaScript errors**
```
Uncaught ReferenceError
```

**Solutions:**
```bash
# Compile assets
npm run dev

# Clear browser cache
# Or add version to asset URLs
<script src="{{ mix('js/app.js') }}"></script>

# Check for console errors
# Open browser dev tools → Console
```

### **CSS Issues**

#### **Problem: Styles not loading**
```
Stylesheet not found
```

**Solutions:**
```bash
# Compile CSS assets
npm run dev

# Publish assets
php artisan vendor:publish --tag=laravel-assets

# Clear asset cache
php artisan cache:clear
```

### **Responsive Issues**

#### **Problem: Mobile layout broken**
```
Layout not responsive
```

**Solutions:**
```css
/* Check viewport meta tag */
<meta name="viewport" content="width=device-width, initial-scale=1">

/* Use responsive classes */
<div class="container mx-auto px-4">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- content -->
    </div>
</div>
```

---

## 🚀 **DEPLOYMENT ISSUES**

### **Environment Issues**

#### **Problem: Environment variables not loaded**
```
Environment variable not found
```

**Solutions:**
```bash
# Check .env file exists
ls -la .env

# Clear config cache
php artisan config:clear
php artisan cache:clear

# Verify environment variables
php artisan tinker
dd(env('APP_NAME'));
```

### **Build Issues**

#### **Problem: Assets not building**
```
Build failed
```

**Solutions:**
```bash
# Clear node modules and rebuild
rm -rf node_modules package-lock.json
npm install

# Check Node version
node --version
npm --version

# Build for production
npm run build
```

### **Service Issues**

#### **Problem: Services not starting**
```
Failed to start service
```

**Solutions:**
```bash
# Check service status
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo systemctl status mysql

# Check service logs
sudo journalctl -u nginx -f
sudo journalctl -u php8.2-fpm -f

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

---

## 📊 **MONITORING & LOGS**

### **Log Analysis**

#### **Problem: Finding errors in logs**
```
Where are the error logs?
```

**Solutions:**
```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Web server logs
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# PHP logs
tail -f /var/log/php8.2-fpm.log

# System logs
tail -f /var/log/syslog

# Search for specific errors
grep "ERROR" storage/logs/laravel.log
grep "exception" storage/logs/laravel.log
```

### **Performance Monitoring**

#### **Problem: High resource usage**
```
CPU/Memory usage high
```

**Solutions:**
```bash
# Check system resources
top
htop
free -h

# Check PHP processes
ps aux | grep php

# Check database connections
mysql -e "SHOW PROCESSLIST;"

# Monitor with tools
sudo apt install -y sysstat
sar -u 1 5  # CPU usage
sar -r 1 5  # Memory usage
```

### **Database Monitoring**

#### **Problem: Database performance issues**
```
Slow queries
```

**Solutions:**
```sql
-- Check running queries
SHOW PROCESSLIST;

-- Check slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;

-- Analyze query performance
EXPLAIN SELECT * FROM attendance WHERE user_id = 1;

-- Check table statistics
ANALYZE TABLE attendance;
```

---

## 🚨 **EMERGENCY PROCEDURES**

### **System Down**

#### **Immediate Actions:**
```bash
# 1. Check system status
uptime
df -h
free -h

# 2. Restart critical services
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
sudo systemctl restart mysql

# 3. Check application health
curl -I http://localhost:8000

# 4. Check logs for errors
tail -50 /var/log/nginx/error.log
tail -50 storage/logs/laravel.log
```

### **Data Loss**

#### **Recovery Steps:**
```bash
# 1. Stop the application
php artisan down

# 2. Restore from backup
mysql -u username -p database_name < backup.sql

# 3. Verify data integrity
php artisan tinker
Attendance::count();
User::count();

# 4. Bring application back up
php artisan up
```

### **Security Breach**

#### **Response Steps:**
```bash
# 1. Isolate the system
# Disconnect from network if necessary

# 2. Change all passwords
# Generate new application key
php artisan key:generate

# 3. Revoke all user sessions
php artisan tinker
DB::table('personal_access_tokens')->delete();

# 4. Audit recent activity
grep "login" storage/logs/laravel.log | tail -20

# 5. Update security measures
# Implement additional security controls
```

### **Contact Information**

#### **Emergency Contacts:**
- **Lead Developer**: Idiarso Simbang
- **Email**: idiarsosimbang@gmail.com
- **Phone**: +62 812-3456-7890
- **Backup Contact**: [Secondary contact]

#### **Escalation Procedure:**
1. Try basic troubleshooting steps
2. Check documentation and known issues
3. Contact primary developer
4. Escalate to system administrator if needed
5. Involve external support if required

---

## 📝 **COMMON ERROR CODES**

| Error Code | Description | Solution |
|------------|-------------|----------|
| 500 | Internal Server Error | Check Laravel logs |
| 502 | Bad Gateway | Check PHP-FPM status |
| 503 | Service Unavailable | Check application maintenance mode |
| 504 | Gateway Timeout | Check PHP execution time limits |
| 419 | Page Expired | Check CSRF token |
| 403 | Forbidden | Check user permissions |
| 404 | Not Found | Check routes and URLs |
| 429 | Too Many Requests | Check rate limiting |

---

## 🔧 **USEFUL COMMANDS**

### **Laravel Commands:**
```bash
# Clear all caches
php artisan optimize:clear

# Run diagnostics
php artisan about

# Check routes
php artisan route:list

# Check configuration
php artisan config:show
```

### **System Commands:**
```bash
# Check disk usage
df -h

# Check memory usage
free -h

# Check running processes
ps aux | grep php

# Check network connections
netstat -tlnp
```

### **Database Commands:**
```sql
-- Check database size
SELECT table_schema, SUM(data_length + index_length) / 1024 / 1024 AS size_mb
FROM information_schema.tables
GROUP BY table_schema;

-- Check table sizes
SELECT table_name, (data_length + index_length) / 1024 / 1024 AS size_mb
FROM information_schema.tables
WHERE table_schema = 'your_database'
ORDER BY size_mb DESC;
```

---

*This troubleshooting guide should help resolve most common issues. For complex problems, don't hesitate to contact the development team.*