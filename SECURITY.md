# 🔒 SECURITY GUIDELINES
## Sistem Absensi SMKN 1 Punggelan

*Panduan keamanan dan praktik terbaik untuk pengembangan sistem*

---

## 📋 **TABLE OF CONTENTS**
1. [Security Overview](#security-overview)
2. [Authentication & Authorization](#authentication--authorization)
3. [Data Protection](#data-protection)
4. [Input Validation](#input-validation)
5. [API Security](#api-security)
6. [Database Security](#database-security)
7. [File Upload Security](#file-upload-security)
8. [Session Management](#session-management)
9. [Error Handling](#error-handling)
10. [Security Monitoring](#security-monitoring)
11. [Incident Response](#incident-response)
12. [Security Checklist](#security-checklist)

---

## 🔍 **SECURITY OVERVIEW**

### **Security Principles**
- **Defense in Depth**: Multiple layers of security controls
- **Least Privilege**: Users have minimum required permissions
- **Fail-Safe Defaults**: Secure defaults, explicit allow
- **Zero Trust**: Never trust, always verify
- **Security by Design**: Security considerations in all development

### **Risk Assessment**
- **High Risk**: User authentication, sensitive data handling
- **Medium Risk**: File uploads, API endpoints, database operations
- **Low Risk**: Static content, public information display

---

## 🔐 **AUTHENTICATION & AUTHORIZATION**

### **Authentication Requirements**
```php
// Secure password requirements
'password' => [
    'required',
    'string',
    'min:12', // Minimum 12 characters
    'regex:/[a-z]/', // At least one lowercase
    'regex:/[A-Z]/', // At least one uppercase
    'regex:/[0-9]/', // At least one digit
    'regex:/[@$!%*#?&]/', // At least one special character
    'confirmed',
],
```

### **Multi-Factor Authentication (MFA)**
- Implement TOTP-based MFA for admin accounts
- Use secure MFA libraries (e.g., Laravel MFA packages)
- Require MFA for sensitive operations

### **Session Security**
```php
// config/session.php
'secure' => env('SESSION_SECURE_COOKIE', true), // HTTPS only
'http_only' => true, // Prevent XSS attacks
'same_site' => 'strict', // CSRF protection
'lifetime' => env('SESSION_LIFETIME', 120), // 2 hours max
```

### **Authorization Best Practices**
- Use role-based access control (RBAC)
- Implement permission checks on all resources
- Avoid direct model access in views
- Use policy classes for complex authorization

```php
// Policy example
class AttendancePolicy
{
    public function view(User $user, Attendance $attendance)
    {
        return $user->id === $attendance->user_id ||
               $user->hasRole('admin') ||
               $user->hasPermissionTo('view all attendance');
    }
}
```

---

## 🛡️ **DATA PROTECTION**

### **Data Classification**
- **Public**: General information (school name, contact)
- **Internal**: User profiles, attendance records
- **Confidential**: Personal data, biometric information
- **Restricted**: Admin credentials, system configuration

### **Encryption Standards**
```php
// Database encryption
protected $casts = [
    'personal_data' => 'encrypted',
    'biometric_data' => 'encrypted',
];

// File encryption
use Illuminate\Support\Facades\Crypt;

$encrypted = Crypt::encrypt($sensitiveData);
$decrypted = Crypt::decrypt($encrypted);
```

### **Data Retention**
- Implement automatic data cleanup
- Define retention policies per data type
- Comply with local data protection regulations
- Log all data deletion activities

### **GDPR Compliance**
- Implement right to erasure
- Provide data export functionality
- Obtain explicit consent for data processing
- Document data processing activities

---

## ✅ **INPUT VALIDATION**

### **Server-Side Validation**
```php
// Form Request validation
class StoreAttendanceRequest extends FormRequest
{
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'check_in_time' => 'required|date|before:check_out_time',
            'check_out_time' => 'nullable|date|after:check_in_time',
            'location_lat' => 'required|numeric|between:-90,90',
            'location_lng' => 'required|numeric|between:-180,180',
            'photo' => 'nullable|image|max:2048|mimes:jpeg,png,jpg',
        ];
    }

    public function messages()
    {
        return [
            'location_lat.between' => 'Latitude must be between -90 and 90 degrees.',
            'location_lng.between' => 'Longitude must be between -180 and 180 degrees.',
        ];
    }
}
```

### **Client-Side Validation**
- Implement JavaScript validation for better UX
- Never rely solely on client-side validation
- Sanitize all user inputs before processing

### **SQL Injection Prevention**
- Use Eloquent ORM or Query Builder
- Avoid raw SQL queries when possible
- Use parameterized queries for dynamic SQL

```php
// Safe query
$users = DB::table('users')
    ->where('email', $request->email)
    ->where('is_approved', true)
    ->get();

// Avoid this
$users = DB::select("SELECT * FROM users WHERE email = '$email'");
```

---

## 🌐 **API SECURITY**

### **API Authentication**
- Use Laravel Sanctum for API authentication
- Implement token-based authentication
- Set appropriate token expiration times
- Revoke tokens on logout/security events

```php
// API middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('attendance', AttendanceController::class);
    Route::apiResource('leave', LeaveController::class);
});
```

### **Rate Limiting**
```php
// Rate limiting configuration
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// API rate limiting
Route::middleware(['throttle:api'])->group(function () {
    Route::apiResource('attendance', AttendanceController::class);
});
```

### **API Security Headers**
```php
// Middleware for security headers
class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', "default-src 'self'");

        return $response;
    }
}
```

---

## 🗄️ **DATABASE SECURITY**

### **Connection Security**
```env
# Secure database configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_secure
DB_USERNAME=absensi_user
DB_PASSWORD=strong_password_here

# Use SSL for database connections
MYSQL_ATTR_SSL_CA=/path/to/ca.pem
MYSQL_ATTR_SSL_CERT=/path/to/client-cert.pem
MYSQL_ATTR_SSL_KEY=/path/to/client-key.pem
```

### **Query Security**
- Use prepared statements
- Implement query whitelisting
- Log all database operations
- Regular security audits

### **Backup Security**
- Encrypt database backups
- Store backups in secure locations
- Test backup restoration regularly
- Implement backup rotation policies

---

## 📁 **FILE UPLOAD SECURITY**

### **Upload Validation**
```php
// Secure file upload
public function store(Request $request)
{
    $request->validate([
        'avatar' => [
            'required',
            'image',
            'max:2048', // 2MB max
            'mimes:jpeg,png,jpg,gif',
            'dimensions:min_width=100,min_height=100,max_width=2000,max_height=2000',
        ],
    ]);

    // Generate secure filename
    $filename = Str::uuid() . '.' . $request->avatar->extension();

    // Store in secure directory
    $path = $request->avatar->storeAs('avatars', $filename, 'secure');

    return $path;
}
```

### **File Storage Security**
- Store uploaded files outside web root
- Use secure file permissions (644 for files, 755 for directories)
- Implement file type verification (not just extension)
- Regular malware scanning

### **Image Processing Security**
- Strip EXIF data from uploaded images
- Resize images to prevent DoS attacks
- Convert images to safe formats
- Implement image optimization

---

## 🔄 **SESSION MANAGEMENT**

### **Session Configuration**
```php
// config/session.php
return [
    'driver' => env('SESSION_DRIVER', 'database'),
    'lifetime' => env('SESSION_LIFETIME', 120),
    'expire_on_close' => true,
    'encrypt' => true,
    'secure' => env('SESSION_SECURE_COOKIE', true),
    'http_only' => true,
    'same_site' => 'strict',
];
```

### **Session Security Best Practices**
- Regenerate session IDs after login
- Implement session timeout
- Clear sessions on logout
- Monitor for session fixation attacks

```php
// Regenerate session on login
public function login(Request $request)
{
    // Authentication logic...

    $request->session()->regenerate();

    return redirect()->intended('/');
}
```

---

## ⚠️ **ERROR HANDLING**

### **Secure Error Handling**
```php
// Don't expose sensitive information
class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            return response()->json(['message' => 'Resource not found'], 404);
        }

        if (app()->environment('production')) {
            return response()->json(['message' => 'Internal server error'], 500);
        }

        return parent::render($request, $exception);
    }
}
```

### **Logging Security**
- Log security events (failed logins, suspicious activities)
- Don't log sensitive data (passwords, tokens)
- Implement log rotation and secure storage
- Monitor logs for security incidents

```php
// Security event logging
Log::channel('security')->info('Failed login attempt', [
    'email' => $request->email,
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'timestamp' => now(),
]);
```

---

## 👁️ **SECURITY MONITORING**

### **Monitoring Implementation**
- Implement intrusion detection systems
- Monitor for unusual patterns
- Set up alerts for security events
- Regular security audits and penetration testing

### **Security Metrics**
- Failed login attempts
- Unusual API usage patterns
- File upload activities
- Database query anomalies
- Session creation/deletion patterns

### **Log Analysis**
```bash
# Monitor failed login attempts
grep "Failed login" storage/logs/laravel.log | tail -20

# Check for suspicious IP addresses
grep "authentication" storage/logs/laravel.log | awk '{print $1}' | sort | uniq -c | sort -nr
```

---

## 🚨 **INCIDENT RESPONSE**

### **Incident Response Plan**
1. **Detection**: Monitor for security incidents
2. **Assessment**: Evaluate incident severity and impact
3. **Containment**: Isolate affected systems
4. **Eradication**: Remove threats and vulnerabilities
5. **Recovery**: Restore systems and data
6. **Lessons Learned**: Document and improve processes

### **Communication Plan**
- Internal team notification
- User communication for data breaches
- Regulatory reporting if required
- Public disclosure when appropriate

### **Recovery Procedures**
- Backup restoration procedures
- System hardening after incidents
- Password reset for affected users
- Security patch deployment

---

## 📋 **SECURITY CHECKLIST**

### **Development Phase**
- [ ] Input validation on all forms
- [ ] Authentication required for sensitive operations
- [ ] Authorization checks implemented
- [ ] SQL injection prevention
- [ ] XSS protection enabled
- [ ] CSRF protection enabled
- [ ] Secure session configuration
- [ ] Error messages don't leak information
- [ ] Dependencies updated and secure

### **Deployment Phase**
- [ ] HTTPS enabled everywhere
- [ ] Security headers configured
- [ ] Debug mode disabled in production
- [ ] Database credentials secured
- [ ] File permissions set correctly
- [ ] Backup procedures tested
- [ ] Monitoring and logging enabled

### **Maintenance Phase**
- [ ] Regular security updates
- [ ] Security audits performed
- [ ] Penetration testing conducted
- [ ] Incident response plan tested
- [ ] Security training for developers
- [ ] Third-party security reviews

---

## 🔧 **SECURITY TOOLS**

### **Development Tools**
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) - Debug queries and performance
- [PHPStan](https://phpstan.org/) - Static analysis for security issues
- [Psalm](https://psalm.dev/) - Security-focused static analysis
- [OWASP ZAP](https://www.zaproxy.org/) - Web application scanner

### **Security Libraries**
- [Laravel Sanctum](https://laravel.com/docs/sanctum) - API authentication
- [Laravel Shield](https://filamentphp.com/plugins/shield) - Admin panel security
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) - Role/permission management
- [Laravel Security](https://github.com/antonioribeiro/laravel-security) - Security headers

### **Monitoring Tools**
- [Laravel Telescope](https://laravel.com/docs/telescope) - Debug and monitor
- [Sentry](https://sentry.io/) - Error tracking and monitoring
- [Papertrail](https://papertrailapp.com/) - Log management
- [UptimeRobot](https://uptimerobot.com/) - Uptime monitoring

---

## 📞 **SECURITY CONTACTS**

### **Security Team**
- **Lead Developer**: Idiarso Simbang
- **Email**: idiarsosimbang@gmail.com
- **Emergency Contact**: +62 812-3456-7890

### **Reporting Security Issues**
- **Email**: security@smkn1punggelan.sch.id
- **PGP Key**: Available upon request
- **Response Time**: Within 24 hours for critical issues

### **Bug Bounty Program**
- Report security vulnerabilities responsibly
- Include detailed reproduction steps
- Allow reasonable time for fixes
- Recognition for valid reports

---

## 📚 **REFERENCES**

### **Security Standards**
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [NIST Cybersecurity Framework](https://www.nist.gov/cyberframework)
- [ISO 27001](https://www.iso.org/standard/54534.html)

### **Laravel Security**
- [Laravel Security Documentation](https://laravel.com/docs/security)
- [Laravel Security Best Practices](https://laravel.com/docs/security-best-practices)

### **PHP Security**
- [PHP Security Guide](https://phpsec.org/projects/guide/)
- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Security_Cheat_Sheet.html)

---

*This security guideline is a living document. Regular updates and reviews are essential to maintain the security posture of the system.*