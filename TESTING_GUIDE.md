# 🧪 TESTING GUIDE
## Sistem Absensi SMKN 1 Punggelan

*Panduan lengkap untuk testing dan quality assurance*

---

## 📋 **TABLE OF CONTENTS**
1. [Testing Overview](#testing-overview)
2. [Unit Testing](#unit-testing)
3. [Feature Testing](#feature-testing)
4. [API Testing](#api-testing)
5. [Browser Testing](#browser-testing)
6. [Performance Testing](#performance-testing)
7. [Security Testing](#security-testing)
8. [Automated Testing](#automated-testing)
9. [Test Data Management](#test-data-management)
10. [CI/CD Integration](#cicd-integration)
11. [Bug Reporting](#bug-reporting)
12. [Test Checklist](#test-checklist)

---

## 🎯 **TESTING OVERVIEW**

### **Testing Pyramid**
```
End-to-End Tests (20%)
    ↕️
Integration Tests (30%)
    ↕️
Unit Tests (50%)
```

### **Testing Types**
- **Unit Tests**: Test individual functions/methods
- **Feature Tests**: Test user interactions and workflows
- **Integration Tests**: Test component interactions
- **API Tests**: Test REST API endpoints
- **E2E Tests**: Test complete user journeys
- **Performance Tests**: Test system performance under load
- **Security Tests**: Test security vulnerabilities

### **Testing Goals**
- Ensure code quality and reliability
- Prevent regressions
- Validate requirements
- Improve maintainability
- Build confidence in deployments

---

## 🧩 **UNIT TESTING**

### **Setting Up PHPUnit**
```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">./app</directory>
        </include>
    </coverage>
</phpunit>
```

### **Unit Test Example**
```php
<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_work_hours()
    {
        // Arrange
        $service = new AttendanceService();
        $checkIn = '08:30:00';
        $checkOut = '17:45:00';
        $breakMinutes = 60; // 1 hour break

        // Act
        $workHours = $service->calculateWorkHours($checkIn, $checkOut, $breakMinutes);

        // Assert
        $this->assertEquals(8.25, $workHours); // 9.25 - 1 = 8.25 hours
    }

    public function test_validate_gps_location_within_office_radius()
    {
        // Arrange
        $service = new AttendanceService();
        $officeLat = -6.2088;
        $officeLng = 106.8456;
        $officeRadius = 100; // meters

        $userLat = -6.2089; // Within 100m
        $userLng = 106.8457;

        // Act
        $isValid = $service->isWithinRadius($userLat, $userLng, $officeLat, $officeLng, $officeRadius);

        // Assert
        $this->assertTrue($isValid);
    }

    public function test_validate_gps_location_outside_office_radius()
    {
        // Arrange
        $service = new AttendanceService();
        $officeLat = -6.2088;
        $officeLng = 106.8456;
        $officeRadius = 100;

        $userLat = -6.2200; // Outside 100m
        $userLng = 106.8500;

        // Act
        $isValid = $service->isWithinRadius($userLat, $userLng, $officeLat, $officeLng, $officeRadius);

        // Assert
        $this->assertFalse($isValid);
    }
}
```

### **Model Testing**
```php
<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_attendances()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $user->attendances);
        $this->assertCount(1, $user->attendances);
    }

    public function test_user_can_access_panel_when_approved()
    {
        $user = User::factory()->create(['is_approved' => true]);

        $this->assertTrue($user->canAccessPanel());
    }

    public function test_user_cannot_access_panel_when_not_approved()
    {
        $user = User::factory()->create(['is_approved' => false]);

        $this->assertFalse($user->canAccessPanel());
    }
}
```

### **Service Testing**
```php
<?php

namespace Tests\Unit\Services;

use App\Services\LeaveService;
use App\Models\User;
use App\Models\Leave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_leave_balance()
    {
        $user = User::factory()->create();
        $service = new LeaveService();

        // Create some leave records
        Leave::factory()->create([
            'user_id' => $user->id,
            'type' => 'annual',
            'status' => 'approved',
            'days_requested' => 5
        ]);

        $balance = $service->getLeaveBalance($user->id);

        $this->assertEquals(7, $balance['annual']['remaining']); // 12 - 5 = 7
    }

    public function test_prevent_overlapping_leave_requests()
    {
        $user = User::factory()->create();
        $service = new LeaveService();

        // Create existing approved leave
        Leave::factory()->create([
            'user_id' => $user->id,
            'start_date' => '2023-12-15',
            'end_date' => '2023-12-20',
            'status' => 'approved'
        ]);

        // Try to create overlapping leave
        $result = $service->validateLeaveRequest(
            $user->id,
            '2023-12-18',
            '2023-12-22'
        );

        $this->assertFalse($result['valid']);
        $this->assertContains('overlapping', $result['errors']);
    }
}
```

---

## 🌟 **FEATURE TESTING**

### **Authentication Testing**
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'is_approved' => false,
        ]);
        $response->assertSessionHas('status');
    }

    public function test_approved_user_can_login_to_admin_panel()
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'is_approved' => true,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user, 'web');
    }

    public function test_unapproved_user_cannot_login_to_admin_panel()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
            'is_approved' => false,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('error');
        $this->assertGuest();
    }
}
```

### **Attendance Testing**
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_check_in_with_valid_location()
    {
        $user = User::factory()->create(['is_approved' => true]);
        $office = Office::factory()->create([
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 100,
        ]);

        $this->actingAs($user);

        $response = $this->post('/admin/attendance/check-in', [
            'latitude' => -6.2089,
            'longitude' => 106.8457,
            'photo' => null,
            'notes' => 'Check in from office',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('attendance', [
            'user_id' => $user->id,
            'status' => 'present',
        ]);
    }

    public function test_user_cannot_check_in_with_invalid_location()
    {
        $user = User::factory()->create(['is_approved' => true]);
        $office = Office::factory()->create([
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 100,
        ]);

        $this->actingAs($user);

        $response = $this->post('/admin/attendance/check-in', [
            'latitude' => -6.2500, // Far from office
            'longitude' => 106.9000,
            'photo' => null,
            'notes' => 'Check in from home',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('attendance', [
            'user_id' => $user->id,
            'date' => today()->toDateString(),
        ]);
    }

    public function test_user_cannot_check_in_twice_on_same_day()
    {
        $user = User::factory()->create(['is_approved' => true]);
        $office = Office::factory()->create();

        // First check-in
        $this->actingAs($user);
        $this->post('/admin/attendance/check-in', [
            'latitude' => $office->latitude,
            'longitude' => $office->longitude,
        ]);

        // Second check-in attempt
        $response = $this->post('/admin/attendance/check-in', [
            'latitude' => $office->latitude,
            'longitude' => $office->longitude,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Should still have only one attendance record
        $this->assertEquals(1, $user->attendances()->count());
    }
}
```

### **Leave Management Testing**
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Leave;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_annual_leave()
    {
        $user = User::factory()->create(['is_approved' => true]);

        $this->actingAs($user);

        $leaveData = [
            'type' => 'annual',
            'start_date' => now()->addDays(7)->toDateString(),
            'end_date' => now()->addDays(12)->toDateString(),
            'reason' => 'Family vacation',
            'contact_number' => '+6281234567890',
            'address_during_leave' => 'Jakarta, Indonesia',
        ];

        $response = $this->post('/admin/leaves', $leaveData);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('leaves', [
            'user_id' => $user->id,
            'type' => 'annual',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_approve_leave_request()
    {
        $admin = User::factory()->create(['is_approved' => true]);
        $admin->assignRole('admin');

        $user = User::factory()->create(['is_approved' => true]);
        $leave = Leave::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->post("/admin/leaves/{$leave->id}/approve", [
            'action' => 'approve',
            'notes' => 'Approved for vacation',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $leave->refresh();
        $this->assertEquals('approved', $leave->status);
        $this->assertEquals($admin->id, $leave->approved_by);
    }
}
```

---

## 🔌 **API TESTING**

### **API Authentication Testing**
```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_via_api()
    {
        $user = User::factory()->create([
            'email' => 'api@example.com',
            'password' => bcrypt('password123'),
            'is_approved' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'api@example.com',
            'password' => 'password123',
            'device_name' => 'Test Device',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user' => ['id', 'name', 'email'],
                    'token',
                    'token_type',
                ]);

        $this->assertNotEmpty($response->json('token'));
    }

    public function test_authenticated_user_can_access_protected_route()
    {
        $user = User::factory()->create(['is_approved' => true]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200)
                ->assertJson([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
    }

    public function test_unauthenticated_user_cannot_access_protected_route()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.',
                ]);
    }
}
```

### **API Attendance Testing**
```php
<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Office;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_check_in_via_api()
    {
        $user = User::factory()->create(['is_approved' => true]);
        $office = Office::factory()->create([
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 100,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/attendance/check-in', [
            'latitude' => -6.2089,
            'longitude' => 106.8457,
            'photo' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAAIAAoDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAhEAACAQMDBQAAAAAAAAAAAAABAgMABAUGIWGRkqGx0f/EABUBAQEAAAAAAAAAAAAAAAAAAAMF/8QAGhEAAgIDAAAAAAAAAAAAAAAAAAECEgMRkf/aAAwDAQACEQMRAD8AltJagyeH0AthI5xdrLcNM91BF5pX2HaH9bcfaSXWGaRmknyJckliyjqTzSlT54b6bk+h0R+IRjWjBqO6O2mhP//Z',
            'notes' => 'Check in via mobile app',
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'id',
                    'user_id',
                    'check_in_time',
                    'latitude',
                    'longitude',
                    'photo_url',
                    'status',
                ]);

        $this->assertDatabaseHas('attendance', [
            'user_id' => $user->id,
            'status' => 'present',
        ]);
    }

    public function test_user_cannot_check_in_outside_office_radius()
    {
        $user = User::factory()->create(['is_approved' => true]);
        $office = Office::factory()->create([
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 100,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/attendance/check-in', [
            'latitude' => -6.2500, // Outside radius
            'longitude' => 106.9000,
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['location']);
    }

    public function test_user_can_get_attendance_history()
    {
        $user = User::factory()->create(['is_approved' => true]);
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in_time' => now()->subDays(1),
            'check_out_time' => now()->subDays(1)->addHours(9),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/attendance');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'date',
                            'check_in_time',
                            'check_out_time',
                            'work_hours',
                            'status',
                        ]
                    ],
                    'links',
                    'meta',
                ]);
    }
}
```

---

## 🌐 **BROWSER TESTING**

### **Laravel Dusk Setup**
```bash
composer require laravel/dusk --dev
php artisan dusk:install
```

### **Browser Test Example**
```php
<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_user_can_login_to_admin_panel()
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'is_approved' => true,
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/login')
                    ->type('email', 'admin@example.com')
                    ->type('password', 'password123')
                    ->press('Log in')
                    ->assertPathIs('/admin')
                    ->assertSee('Dashboard');
        });
    }

    public function test_user_can_check_in_via_web_interface()
    {
        $user = User::factory()->create(['is_approved' => true]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/admin/attendance')
                    ->assertSee('Check In')
                    ->press('Check In')
                    ->waitForText('Check in successful')
                    ->assertSee('Check in successful');
        });
    }
}
```

### **Visual Regression Testing**
```bash
# Install BackstopJS
npm install -g backstopjs

# Initialize BackstopJS
backstop init

# Configure backstop.json
{
  "id": "absensi_visual_tests",
  "viewports": [
    {
      "label": "desktop",
      "width": 1920,
      "height": 1080
    },
    {
      "label": "mobile",
      "width": 375,
      "height": 667
    }
  ],
  "scenarios": [
    {
      "label": "Login Page",
      "url": "http://localhost:8000/admin/login",
      "referenceUrl": "",
      "readyEvent": "",
      "readySelector": "",
      "delay": 100,
      "hideSelectors": [],
      "removeSelectors": [],
      "hoverSelector": "",
      "clickSelector": "",
      "postInteractionWait": 0,
      "selectors": [],
      "selectorExpansion": true,
      "expect": 0,
      "misMatchThreshold" : 0.1,
      "requireSameDimensions": true
    }
  ],
  "paths": {
    "bitmaps_reference": "backstop_data/bitmaps_reference",
    "bitmaps_test": "backstop_data/bitmaps_test",
    "engine_scripts": "backstop_data/engine_scripts",
    "html_report": "backstop_data/html_report",
    "ci_report": "backstop_data/ci_report"
  },
  "report": ["browser"],
  "engine": "puppeteer",
  "engineOptions": {
    "args": ["--no-sandbox"]
  },
  "asyncCaptureLimit": 5,
  "asyncCompareLimit": 50,
  "debug": false,
  "debugWindow": false
}
```

---

## ⚡ **PERFORMANCE TESTING**

### **Load Testing with Artillery**
```yaml
# artillery.yml
config:
  target: 'http://localhost:8000'
  phases:
    - duration: 60
      arrivalRate: 5
      name: "Warm up"
    - duration: 120
      arrivalRate: 20
      name: "Load testing"
    - duration: 60
      arrivalRate: 50
      name: "Spike testing"
  defaults:
    headers:
      Content-Type: 'application/json'

scenarios:
  - name: 'User login'
    weight: 30
    flow:
      - post:
          url: '/api/login'
          json:
            email: 'user{{ $randomInt }}@example.com'
            password: 'password123'
            device_name: 'Load Test'

  - name: 'Check attendance'
    weight: 40
    flow:
      - post:
          url: '/api/login'
          json:
            email: 'user1@example.com'
            password: 'password123'
            device_name: 'Load Test'
          capture:
            json: '$.token'
            as: 'token'
      - get:
          url: '/api/attendance'
          headers:
            Authorization: 'Bearer {{ token }}'

  - name: 'Submit leave request'
    weight: 30
    flow:
      - post:
          url: '/api/login'
          json:
            email: 'user1@example.com'
            password: 'password123'
            device_name: 'Load Test'
          capture:
            json: '$.token'
            as: 'token'
      - post:
          url: '/api/leaves'
          headers:
            Authorization: 'Bearer {{ token }}'
          json:
            type: 'annual'
            start_date: '2023-12-20'
            end_date: '2023-12-25'
            reason: 'Load testing'
```

### **Database Performance Testing**
```php
<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_query_performance()
    {
        // Create test data
        $users = User::factory()->count(100)->create(['is_approved' => true]);

        foreach ($users as $user) {
            Attendance::factory()->count(30)->create(['user_id' => $user->id]);
        }

        // Test query performance
        $startTime = microtime(true);

        $attendances = Attendance::with('user')
            ->whereBetween('date', ['2023-11-01', '2023-11-30'])
            ->orderBy('date', 'desc')
            ->get();

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Assert performance requirements
        $this->assertLessThan(500, $executionTime, 'Query should execute in less than 500ms');
        $this->assertCount(3000, $attendances); // 100 users * 30 days
    }

    public function test_database_indexes_are_used()
    {
        // Enable query logging
        DB::enableQueryLog();

        // Perform query that should use index
        $attendances = Attendance::where('user_id', 1)
            ->where('date', '>=', '2023-01-01')
            ->get();

        $queries = DB::getQueryLog();

        // Check if query used index (this is a simplified check)
        $this->assertStringContains('user_id', $queries[0]['query']);
    }
}
```

---

## 🔒 **SECURITY TESTING**

### **Authentication Security Testing**
```php
<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_brute_force_protection()
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct_password'),
            'is_approved' => true,
        ]);

        // Attempt multiple failed logins
        for ($i = 0; $i < 10; $i++) {
            $response = $this->post('/admin/login', [
                'email' => $user->email,
                'password' => 'wrong_password',
            ]);
        }

        // Next attempt should be rate limited
        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(429); // Too Many Requests
    }

    public function test_sql_injection_protection()
    {
        $maliciousInput = "' OR '1'='1'; --";

        $response = $this->post('/admin/login', [
            'email' => $maliciousInput,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_xss_protection()
    {
        $xssPayload = '<script>alert("XSS")</script>';

        $user = User::factory()->create([
            'name' => $xssPayload,
            'is_approved' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/admin');

        $response->assertDontSee('<script>', false);
        $response->assertSee('&lt;script&gt;', false); // Should be escaped
    }
}
```

### **API Security Testing**
```php
<?php

namespace Tests\Feature\Api\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_rate_limiting()
    {
        $user = User::factory()->create(['is_approved' => true]);
        Sanctum::actingAs($user);

        // Make many requests quickly
        for ($i = 0; $i < 65; $i++) { // Exceed rate limit
            $response = $this->getJson('/api/user');
        }

        $response->assertStatus(429)
                ->assertJson([
                    'message' => 'API rate limit exceeded.',
                ]);
    }

    public function test_mass_assignment_protection()
    {
        $user = User::factory()->create(['is_approved' => true]);
        Sanctum::actingAs($user);

        // Try to update protected fields
        $response = $this->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name',
            'is_approved' => true, // Should be protected
            'email_verified_at' => now(), // Should be protected
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertFalse($user->is_approved); // Should not be changed
        $this->assertNull($user->email_verified_at); // Should not be changed
    }

    public function test_authorization_enforcement()
    {
        $user1 = User::factory()->create(['is_approved' => true]);
        $user2 = User::factory()->create(['is_approved' => true]);

        Sanctum::actingAs($user1);

        // Try to update another user's profile
        $response = $this->putJson("/api/users/{$user2->id}", [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403); // Forbidden

        $user2->refresh();
        $this->assertNotEquals('Hacked Name', $user2->name);
    }
}
```

---

## 🤖 **AUTOMATED TESTING**

### **GitHub Actions CI/CD**
```yaml
# .github/workflows/tests.yml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: absensi_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
    - uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.2'
        extensions: pdo, pdo_mysql
        coverage: xdebug

    - name: Install dependencies
      run: composer install --no-progress --prefer-dist --optimize-autoloader

    - name: Copy environment file
      run: cp .env.ci .env

    - name: Generate application key
      run: php artisan key:generate

    - name: Run migrations
      run: php artisan migrate --force

    - name: Run tests
      run: php artisan test --coverage --min=80

    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage.xml
```

### **Pre-commit Hooks**
```bash
# Install pre-commit
pip install pre-commit

# Create .pre-commit-config.yaml
repos:
  - repo: local
    hooks:
      - id: phpstan
        name: PHPStan
        entry: vendor/bin/phpstan analyse
        language: system
        files: \.php$
        pass_filenames: false

      - id: phpunit
        name: PHPUnit
        entry: vendor/bin/phpunit
        language: system
        files: \.php$
        pass_filenames: false

      - id: eslint
        name: ESLint
        entry: npx eslint
        language: system
        files: \.(js|vue)$
        pass_filenames: true
```

---

## 📊 **TEST DATA MANAGEMENT**

### **Factories**
```php
<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'password' => Hash::make('password123'),
            'is_approved' => $this->faker->boolean(70), // 70% approved
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function approved()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_approved' => true,
            ];
        });
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_approved' => true,
            ];
        })->afterCreating(function (User $user) {
            $user->assignRole('admin');
        });
    }
}
```

### **Seeders for Testing**
```php
<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Create test office
        $office = Office::factory()->create([
            'name' => 'Test Office',
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'radius' => 500,
        ]);

        // Create test schedule
        $schedule = Schedule::factory()->create([
            'name' => 'Test Schedule',
            'is_default' => true,
        ]);

        // Create test shift
        Shift::factory()->create([
            'schedule_id' => $schedule->id,
            'name' => 'Regular Shift',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        // Create test users
        User::factory()->count(10)->create()->each(function ($user) use ($schedule) {
            $user->schedules()->attach($schedule->id, [
                'start_date' => now()->startOfMonth(),
                'end_date' => null,
            ]);
        });

        // Create admin user
        User::factory()->admin()->create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
        ]);
    }
}
```

---

## 🐛 **BUG REPORTING**

### **Bug Report Template**
```markdown
## Bug Report

### Description
A clear and concise description of the bug.

### Steps to Reproduce
1. Go to '...'
2. Click on '...'
3. Scroll down to '...'
4. See error

### Expected Behavior
What should happen.

### Actual Behavior
What actually happens.

### Screenshots
If applicable, add screenshots to help explain the problem.

### Environment
- OS: [e.g., Ubuntu 22.04]
- Browser: [e.g., Chrome 91]
- PHP Version: [e.g., 8.2]
- Laravel Version: [e.g., 11.x]
- Database: [e.g., MySQL 8.0]

### Additional Context
- Error logs
- Browser console errors
- Network requests
- Test case that fails

### Severity
- [ ] Critical (System down, data loss)
- [ ] High (Major feature broken)
- [ ] Medium (Feature partially broken)
- [ ] Low (Minor issue, workaround available)

### Priority
- [ ] Urgent (Fix immediately)
- [ ] High (Fix in next release)
- [ ] Medium (Fix when possible)
- [ ] Low (Fix if time permits)
```

### **Test Case for Bug Fixes**
```php
<?php

namespace Tests\Feature\BugFixes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BugFixTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group bug-fix
     * Bug: User approval status not properly checked in canAccessPanel method
     * Fix: Added proper role and approval checking
     */
    public function test_user_approval_status_is_checked_for_panel_access()
    {
        // Test case for the bug fix
        $approvedUser = User::factory()->create(['is_approved' => true]);
        $unapprovedUser = User::factory()->create(['is_approved' => false]);

        $this->assertTrue($approvedUser->canAccessPanel());
        $this->assertFalse($unapprovedUser->canAccessPanel());
    }
}
```

---

## ✅ **TEST CHECKLIST**

### **Pre-Development**
- [ ] Requirements reviewed and understood
- [ ] Test cases designed for new features
- [ ] Test data prepared
- [ ] Test environment ready

### **During Development**
- [ ] Unit tests written for new functions
- [ ] Feature tests written for user stories
- [ ] Code reviewed for testability
- [ ] Edge cases considered
- [ ] Error handling tested

### **Pre-Commit**
- [ ] All tests pass locally
- [ ] Code coverage maintained (>80%)
- [ ] No linting errors
- [ ] Documentation updated
- [ ] Migration tested

### **Pre-Release**
- [ ] Full test suite passes
- [ ] Performance tests pass
- [ ] Security tests pass
- [ ] Cross-browser testing completed
- [ ] Load testing completed
- [ ] Accessibility testing completed

### **Post-Release**
- [ ] Monitoring alerts configured
- [ ] Rollback plan ready
- [ ] User acceptance testing completed
- [ ] Documentation published
- [ ] Support team trained

---

*This testing guide ensures quality and reliability throughout the development lifecycle of the Sistem Absensi SMKN 1 Punggelan.*