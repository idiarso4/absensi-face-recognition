# 🛠️ PANDUAN PENGEMBANGAN
## Sistem Absensi SMKN 1 Punggelan

*Untuk Developer yang Akan Melanjutkan Pengembangan*

---

## 🚀 **QUICK START**

### **Prerequisites**
```bash
# System Requirements
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- SQLite or PostgreSQL
- Git

# Install Dependencies
composer install
npm install
```

### **Environment Setup**
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Setup database
touch database/database.sqlite
# or configure PostgreSQL in .env

# Run migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed

# Install Filament
php artisan filament:install

# Setup Filament Shield
php artisan shield:install
php artisan shield:generate --all --panel=admin --no-interaction

# Build assets
npm run build
# or for development
npm run dev
```

### **Start Development Server**
```bash
# Laravel server
php artisan serve

# Filament panel
# Access at: http://localhost:8000/admin

# Registration page
# Access at: http://localhost:8000/register
```

---

## 📁 **PROJECT STRUCTURE**

```
├── app/
│   ├── Filament/              # Admin panel resources
│   │   ├── Resources/         # CRUD resources
│   │   └── Pages/            # Custom pages
│   ├── Http/Controllers/     # HTTP controllers
│   ├── Models/               # Eloquent models
│   └── Providers/            # Service providers
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── resources/
│   ├── views/                # Blade templates
│   └── css/                  # Custom styles
├── routes/                   # Route definitions
├── config/                   # Configuration files
├── public/                   # Public assets
├── storage/                  # File storage
├── tests/                    # Test files
├── docs/                     # Documentation
├── ROADMAP.md               # Development roadmap
├── CHECKLIST.md             # Detailed checklist
└── TODO.md                  # Task management
```

---

## 🔧 **DEVELOPMENT WORKFLOW**

### **1. Branching Strategy**
```bash
# Create feature branch
git checkout -b feature/nama-fitur

# Create bugfix branch
git checkout -b bugfix/nama-bug

# Create hotfix branch
git checkout -b hotfix/nama-hotfix
```

### **2. Code Standards**
- Follow PSR-12 coding standards
- Use meaningful variable and method names
- Add PHPDoc comments for classes and methods
- Write descriptive commit messages

### **3. Testing**
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=TestName

# Generate test coverage
php artisan test --coverage
```

### **4. Code Quality**
```bash
# Run PHP CS Fixer
./vendor/bin/php-cs-fixer fix

# Run Laravel Pint
./vendor/bin/pint

# Run static analysis
./vendor/bin/phpstan analyse
```

---

## 🎨 **UI/UX DEVELOPMENT**

### **Filament Admin Panel**
```php
// Creating a new resource
php artisan make:filament-resource ResourceName

// Creating a custom page
php artisan make:filament-page PageName

// Creating a widget
php artisan make:filament-widget WidgetName
```

### **Styling Guidelines**
- Use Tailwind CSS classes
- Follow Filament design system
- Ensure mobile responsiveness
- Maintain consistent color scheme

### **Form Validation**
```php
// In Filament forms
TextInput::make('email')
    ->email()
    ->required()
    ->unique(table: 'users', column: 'email'),

// Custom validation rules
->rules(['required', 'email', 'unique:users,email'])
```

---

## 🔐 **SECURITY BEST PRACTICES**

### **Authentication**
- Always use Filament's built-in authentication
- Implement proper role-based access control
- Use Sanctum for API authentication
- Enable email verification for production

### **Data Protection**
```php
// Hash sensitive data
Hash::make($password);

// Encrypt sensitive data
Crypt::encrypt($sensitiveData);

// Use proper validation
$request->validate([
    'email' => 'required|email',
    'password' => 'required|min:8',
]);
```

### **Security Headers**
```php
// In middleware or service provider
$this->headers->set('X-Frame-Options', 'SAMEORIGIN');
$this->headers->set('X-Content-Type-Options', 'nosniff');
$this->headers->set('X-XSS-Protection', '1; mode=block');
```

---

## 🗄️ **DATABASE DEVELOPMENT**

### **Migration Best Practices**
```php
// Create migration
php artisan make:migration create_table_name

// Migration structure
public function up()
{
    Schema::create('table_name', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });
}
```

### **Model Relationships**
```php
// In User model
public function attendances()
{
    return $this->hasMany(Attendance::class);
}

// In Attendance model
public function user()
{
    return $this->belongsTo(User::class);
}
```

### **Query Optimization**
```php
// Use eager loading
$users = User::with('attendances')->get();

// Use pagination
$users = User::paginate(15);

// Index important columns
$table->index('email');
$table->index(['created_at', 'updated_at']);
```

---

## 📡 **API DEVELOPMENT**

### **API Structure**
```php
// Routes in api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('attendances', AttendanceController::class);
});

// Controller structure
class AttendanceController extends Controller
{
    public function index()
    {
        return AttendanceResource::collection(
            Attendance::paginate()
        );
    }
}
```

### **API Resources**
```php
// Create API resource
php artisan make:resource AttendanceResource

// Resource structure
class AttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user' => $this->user->name,
            'check_in' => $this->check_in,
            'created_at' => $this->created_at,
        ];
    }
}
```

---

## 📧 **EMAIL & NOTIFICATIONS**

### **Email Configuration**
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@domain.com
```

### **Sending Emails**
```php
// Using Mailable
php artisan make:mail WelcomeEmail

// In controller
Mail::to($user->email)->send(new WelcomeEmail($user));

// Queue emails for better performance
Mail::to($user->email)->queue(new WelcomeEmail($user));
```

---

## 🧪 **TESTING STRATEGY**

### **Unit Tests**
```php
// Test file location: tests/Unit/
class UserTest extends TestCase
{
    public function test_user_has_attendances()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Attendance::class, $user->attendances->first());
    }
}
```

### **Feature Tests**
```php
// Test file location: tests/Feature/
class RegistrationTest extends TestCase
{
    public function test_user_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'is_approved' => false,
        ]);
    }
}
```

### **Browser Tests**
```php
// Using Laravel Dusk
class AdminLoginTest extends DuskTestCase
{
    public function test_admin_can_login()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/login')
                    ->type('email', 'admin@admin.com')
                    ->type('password', 'password')
                    ->press('Sign In')
                    ->assertPathIs('/admin');
        });
    }
}
```

---

## 🚀 **DEPLOYMENT GUIDE**

### **Production Checklist**
- [ ] Set APP_ENV=production in .env
- [ ] Configure production database
- [ ] Set APP_KEY for encryption
- [ ] Configure mail settings
- [ ] Set APP_URL correctly
- [ ] Run php artisan config:cache
- [ ] Run php artisan route:cache
- [ ] Run php artisan view:cache
- [ ] Set proper file permissions

### **Server Requirements**
```bash
# Ubuntu/Debian server setup
sudo apt update
sudo apt install php8.2 php8.2-cli php8.2-fpm php8.2-mysql php8.2-xml php8.2-mbstring
sudo apt install nginx
sudo apt install composer
sudo apt install nodejs npm
```

### **Nginx Configuration**
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/absensi/public;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

---

## 📊 **MONITORING & LOGGING**

### **Application Monitoring**
```php
// In AppServiceProvider
public function boot()
{
    // Log all database queries in development
    if (app()->environment('local')) {
        DB::listen(function ($query) {
            Log::info($query->sql, $query->bindings);
        });
    }
}
```

### **Error Handling**
```php
// In app/Exceptions/Handler.php
public function render($request, Throwable $exception)
{
    if ($request->is('api/*')) {
        return response()->json([
            'error' => 'Something went wrong',
            'message' => $exception->getMessage(),
        ], 500);
    }

    return parent::render($request, $exception);
}
```

---

## 🔧 **USEFUL COMMANDS**

### **Laravel Commands**
```bash
# Clear all caches
php artisan optimize:clear

# Generate IDE helper
php artisan ide-helper:generate

# List all routes
php artisan route:list

# List all commands
php artisan list
```

### **Filament Commands**
```bash
# Create resource
php artisan make:filament-resource ResourceName

# Generate shield permissions
php artisan shield:generate --all --panel=admin

# Clear Filament cache
php artisan filament:cache-components
```

### **Development Tools**
```bash
# Start development server
php artisan serve

# Watch file changes
npm run watch

# Run tests
php artisan test

# Check code style
./vendor/bin/pint --test
```

---

## 📚 **RESOURCES & LEARNING**

### **Official Documentation**
- [Laravel Documentation](https://laravel.com/docs/11.x)
- [Filament Documentation](https://filamentphp.com/docs/3.x)
- [Filament Shield](https://filamentphp.com/plugins/bezhansalleh-shield)
- [Tailwind CSS](https://tailwindcss.com/docs)

### **Community Resources**
- [Laravel News](https://laravel-news.com)
- [Filament Discord](https://discord.gg/filament)
- [Laracasts](https://laracasts.com)
- [Stack Overflow](https://stackoverflow.com/questions/tagged/laravel)

### **Best Practices**
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [PHP The Right Way](https://phptherightway.com)
- [OWASP Security Guidelines](https://owasp.org/www-project-top-ten/)

---

## 🆘 **TROUBLESHOOTING**

### **Common Issues**

**Issue: Class not found**
```bash
# Solution
composer dump-autoload
php artisan config:clear
```

**Issue: Permission denied**
```bash
# Solution
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

**Issue: Database connection failed**
```bash
# Check .env file
# Run migrations
php artisan migrate
```

**Issue: Assets not loading**
```bash
# Clear cache and rebuild
php artisan optimize:clear
npm run build
```

### **Debugging Tools**
```php
// Debug queries
DB::enableQueryLog();
dd(DB::getQueryLog());

// Debug variables
dd($variable);

// Log messages
Log::info('Debug message', ['data' => $data]);
```

---

## 📞 **SUPPORT & CONTACT**

### **Development Team**
- **Lead Developer**: Mas-Idi
- **Email**: idiarsosimbang@gmail.com
- **Repository**: https://github.com/idiarso4/absensi-face-recognition

### **Emergency Contacts**
- **Server Issues**: IT Administrator SMKN 1 Punggelan
- **Security Issues**: Report immediately to development team
- **Data Loss**: Follow backup recovery procedures

---

## 📈 **PERFORMANCE OPTIMIZATION**

### **Database Optimization**
- Use database indexes for frequently queried columns
- Implement eager loading to prevent N+1 queries
- Use pagination for large datasets
- Consider database caching

### **Application Optimization**
- Enable OPcache in production
- Use CDN for static assets
- Implement Redis for session and cache storage
- Minify and compress assets

### **Code Optimization**
- Avoid unnecessary database queries
- Use collections efficiently
- Implement proper error handling
- Write clean, maintainable code

---

*Panduan ini akan diperbarui seiring perkembangan project. Pastikan untuk membaca changelog di ROADMAP.md untuk update terbaru.*