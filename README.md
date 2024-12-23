
## Langkah-langkah Instalasi

1. **Extract File Zip**
    - Download file zip dari sumber yang disediakan.
    - Extract file zip ke dalam direktori proyek Anda.

2. **Buka File Zip dengan Text Editor**
    - Gunakan text editor pilihan Anda (contoh: VSCode, Sublime Text, atau Notepad++).
    - Buka direktori proyek yang telah diextract.

3. **Siapkan Database**
    - Buat database baru di server database Anda (contoh: MySQL, PostgreSQL, atau SQLite).

4. **Jalankan `cp .env.example .env`, lalu Masukkan Konfigurasi Database**
    - Salin file `.env.example` menjadi `.env`.
    - Buka file `.env` dan masukkan informasi konfigurasi database Anda.

5. **Jalankan `composer update`**
    - Jalankan perintah `composer update` untuk menginstal semua dependensi yang diperlukan.

6. **Jalankan `php artisan key:generate`**
    - Jalankan perintah `php artisan key:generate` untuk menghasilkan kunci aplikasi.

7. **Jalankan `php artisan migrate`**
    - Jalankan perintah `php artisan migrate` untuk membuat tabel-tabel di database.

8. **Jalankan `php artisan make:filament-user`, lalu Masukkan Informasi User**
    - Jalankan perintah `php artisan make:filament-user` dan masukkan informasi pengguna yang diminta.

9. **Jalankan `php artisan serve`, Buka Web di URL `/admin`**
    - Jalankan perintah `php artisan serve`.
    - Buka browser dan akses `http://localhost:8000/admin`.

10. **Project Siap Digunakan**
    - Proyek Laravel Anda sekarang siap digunakan.

    Catatan untuk cara install Filament Shield

    1. install library :  composer require bezhansalleh/filament-shield
    2. php artisan shield:setup
    3. php artisan shield:install
    4. php artisan shield:generate --all (untuk generate policy dari semua model) -> ini yang sering lupa, menyebabkan menu2 nya ilang
    5. php artisan shield:super-admin (untuk assign role super_admin ke user tertentu)

    Ketika nambah model baru, biasanya menu model itu ga muncul di side bar, itu karena file policy dari model itu belum dibuat. Misal buat model baru, namanya "Product". Buat dulu policy product, caranya

    php artisan make:policy ProductPolicy --model=Product
    php artisan make:filament-user


================
APP_NAME=PRESENSI-SKANSAPUNG
APP_ENV=production
APP_KEY=base64:eeZMn4H8HBv2EI1N2xwbv3x6LSHjYFBEzM7brLYP9fU=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://presensi.sijasmkn1punggelan.org

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=single
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_db_solid
DB_USERNAME=skansa
DB_PASSWORD=1q2w3e4r5_YRK

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=presensi.sijasmkn1punggelan.org

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database

CACHE_STORE=database
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
FILAMENT_ASSET_VERSION=1
FILAMENT_ASSET_PATH=/filament/assets






=======================
Berikut adalah langkah-langkah untuk mengoptimalkan aplikasi Laravel Anda sebelum deployment ke production:

1. **Optimasi Autoloader**
```bash
composer install --optimize-autoloader --no-dev
```

2. **Cache Configuration dan Route**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

3. **Optimasi Database**
Tambahkan index pada kolom yang sering dicari di migration:
```php:database/migrations/example_migration.php
// ... existing code ...
$table->index('column_name');
// ... existing code ...
```

4. **Update `.env` untuk Production**
```env:.env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

CACHE_DRIVER=redis    # Gunakan Redis untuk caching
SESSION_DRIVER=redis  # Gunakan Redis untuk session
QUEUE_CONNECTION=redis # Gunakan Redis untuk queue

# Optimasi Database
DB_HOST=your_production_db_host
```

5. **Implementasi Caching**
```php:app/Http/Controllers/ExampleController.php
use Illuminate\Support\Facades\Cache;

public function index()
{
    return Cache::remember('key', 3600, function () {
        return YourModel::expensive()->query()->get();
    });
}
```

6. **Konfigurasi Logging**
```php:config/logging.php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => 14,
],
```

7. **Implementasi Rate Limiting**
```php:routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    // API routes here
});
```

8. **Optimasi Assets**
- Gunakan Laravel Mix untuk minify CSS/JS
```javascript:webpack.mix.js
mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .version();
```

9. **Security Checklist**
```php:config/session.php
return [
    'secure' => true,     // Untuk HTTPS
    'http_only' => true,  // Mencegah akses JavaScript ke cookie
    'same_site' => 'lax', // Proteksi CSRF
];
```

10. **Tambahkan Headers Security**
```php:app/Http/Middleware/SecurityHeaders.php
namespace App\Http\Middleware;

class SecurityHeaders
{
    public function handle($request, $closure)
    {
        $response = $closure($request);
        
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        return $response;
    }
}
```

11. **Implementasi Queue untuk Task Berat**
```php:app/Jobs/HeavyTask.php
class HeavyTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public function handle()
    {
        // Heavy processing here
    }
}
```

12. **Monitoring Setup**
```bash
# Install Telescope untuk development monitoring
composer require laravel/telescope --dev
php artisan telescope:install
```

Langkah tambahan sebelum deployment:

1. **Periksa Dependencies**
```bash
composer audit
npm audit
```

2. **Backup Database**
```bash
php artisan backup:run
```

3. **Testing**
```bash
php artisan test
```

4. **Periksa Storage Links**
```bash
php artisan storage:link
```

5. **Maintenance Mode saat Deploy**
```bash
php artisan down
# Deploy process
php artisan up
```

Setelah deployment ke VPS:

1. **Setup Supervisor untuk Queue**
```ini:/etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan queue:work
autostart=true
autorestart=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/worker.log
```

2. **Setup Cron untuk Scheduled Tasks**
```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

3. **Setup Redis (jika digunakan)**
```bash
sudo apt-get install redis-server
sudo systemctl enable redis-server
```

Pastikan untuk selalu:
- Melakukan backup regular
- Memonitor error logs
- Mengupdate dependencies secara berkala
- Mengimplementasikan CI/CD untuk deployment yang aman

Pesan error tersebut muncul karena tidak ada file `package-lock.json` di project Anda. Mari kita perbaiki dengan langkah-langkah berikut:

1. **Pertama, generate package-lock.json**
```bash
npm install --package-lock-only
```

2. **Kemudian install dependencies**
```bash
npm install
```

3. **Setelah itu, baru jalankan audit**
```bash
npm audit
```

Jika masih mengalami masalah, coba hapus file yang ada dan mulai dari awal:
```bash
# Hapus node_modules dan package-lock.json jika ada
rm -rf node_modules package-lock.json

# Install ulang
npm install

# Jalankan audit
npm audit
```

Jika Anda menggunakan Windows, gunakan perintah ini untuk menghapus:
```bash
rd /s /q node_modules
del package-lock.json
```

Pastikan juga bahwa file `package.json` Anda ada dan valid. Jika tidak ada, Anda bisa membuatnya dengan:
```bash
npm init -y
```

Setelah itu, tambahkan dependencies yang diperlukan dan lakukan instalasi ulang.



Saya akan membantu Anda memeriksa dan mempersiapkan aplikasi untuk production. Mari ikuti langkah-langkah berikut:

1. **Periksa dan Update Dependencies**
```bash
# Update composer dependencies
composer update

# Install dependencies production only
composer install --optimize-autoloader --no-dev

# Update npm packages
npm update

# Install dan build assets
npm install
npm run build
```

2. **Optimasi Laravel**
```bash
# Clear semua cache dulu
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Kemudian generate cache baru
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
php artisan optimize
```

3. **Periksa file `.env`**
```env
# Ubah setting ini di .env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Cache dan Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Database
DB_HOST=your_production_db_host
DB_DATABASE=your_production_db
DB_USERNAME=your_production_username
DB_PASSWORD=your_production_password

# Mail (jika menggunakan)
MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your@email.com
MAIL_FROM_NAME="${APP_NAME}"
```

4. **Periksa File yang Perlu Di-ignore**
```gitignore:.gitignore
/node_modules
/public/hot
/public/storage
/storage/*.key
/vendor
.env
.env.backup
.phpunit.result.cache
docker-compose.override.yml
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.idea
/.vscode
```

5. **Periksa Storage dan Permissions**
```bash
# Create storage link
php artisan storage:link

# Set permissions (di server)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

6. **Jalankan Tests (jika ada)**
```bash
php artisan test
```

7. **Buat Backup Database**
```bash
# Jika menggunakan package backup
php artisan backup:run

# Atau gunakan MySQL dump
mysqldump -u root -p database_name > backup.sql
```

8. **File yang Perlu Diupload ke Production**
```plaintext
Folder dan file yang perlu diupload:
- app/
- bootstrap/
- config/
- database/
- lang/
- public/
- resources/
- routes/
- storage/
- tests/
- vendor/
- .env.example (rename jadi .env di server)
- artisan
- composer.json
- composer.lock
- package.json
- webpack.mix.js (jika menggunakan Laravel Mix)
- vite.config.js (jika menggunakan Vite)
```

9. **Checklist Sebelum Upload**
- [ ] Semua credentials sudah diupdate di `.env`
- [ ] Debug mode dimatikan
- [ ] Assets sudah di-build
- [ ] Database sudah dibackup
- [ ] Storage permissions sudah benar
- [ ] Cache sudah dioptimasi
- [ ] Tests sudah dijalankan
- [ ] Sensitive files sudah di-ignore

10. **Setelah Upload ke Server**
```bash
# Di server production
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

11. **Setup Queue Worker (jika menggunakan)**
```bash
# Install supervisor
sudo apt-get install supervisor

# Buat konfigurasi
sudo nano /etc/supervisor/conf.d/laravel-worker.conf

# Isi dengan:
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work
autostart=true
autorestart=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
```

12. **Setup Cron untuk Scheduled Tasks**
```bash
# Edit crontab
crontab -e

# Tambahkan
* * * * * cd /path/to/your/project && php artisan schedule:run >> /dev/null 2>&1
```

Apakah Anda ingin saya membantu memeriksa file atau konfigurasi tertentu dalam project Anda?

