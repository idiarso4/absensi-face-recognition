# 📡 API DOCUMENTATION
## Sistem Absensi SMKN 1 Punggelan

*Dokumentasi lengkap API untuk integrasi mobile dan third-party*

---

## 📋 **TABLE OF CONTENTS**
1. [Authentication](#authentication)
2. [Users API](#users-api)
3. [Attendance API](#attendance-api)
4. [Leave Management API](#leave-management-api)
5. [Office Management API](#office-management-api)
6. [Schedule Management API](#schedule-management-api)
7. [Shift Management API](#shift-management-api)
8. [Error Handling](#error-handling)
9. [Rate Limiting](#rate-limiting)
10. [API Testing](#api-testing)

---

## 🔐 **AUTHENTICATION**

### **Overview**
API menggunakan Laravel Sanctum untuk authentication. Semua request ke protected endpoints harus menyertakan Bearer token.

### **Login**
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123",
  "device_name": "Mobile App v1.0"
}
```

**Response (200 OK):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "is_approved": true,
    "roles": ["employee"]
  },
  "token": "1|abcdefghijklmnop...",
  "token_type": "Bearer"
}
```

**Error Response (401 Unauthorized):**
```json
{
  "message": "Invalid credentials",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

### **Logout**
```http
POST /api/logout
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "message": "Logged out successfully"
}
```

### **Register**
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "+6281234567890"
}
```

**Response (201 Created):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "is_approved": false
  },
  "message": "Registration successful. Please wait for admin approval."
}
```

### **Get Current User**
```http
GET /api/user
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+6281234567890",
  "is_approved": true,
  "created_at": "2023-12-01T08:00:00.000000Z",
  "updated_at": "2023-12-01T08:00:00.000000Z",
  "roles": [
    {
      "id": 1,
      "name": "employee",
      "guard_name": "web"
    }
  ]
}
```

---

## 👥 **USERS API**

### **List Users**
```http
GET /api/users
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (integer): Page number for pagination
- `per_page` (integer): Items per page (default: 15)
- `search` (string): Search by name or email
- `is_approved` (boolean): Filter by approval status

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+6281234567890",
      "is_approved": true,
      "created_at": "2023-12-01T08:00:00.000000Z",
      "roles": ["employee"]
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/users?page=1",
    "last": "http://localhost:8000/api/users?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

### **Get User Details**
```http
GET /api/users/{id}
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+6281234567890",
  "is_approved": true,
  "created_at": "2023-12-01T08:00:00.000000Z",
  "updated_at": "2023-12-01T08:00:00.000000Z",
  "attendance_today": {
    "check_in": "08:30:00",
    "check_out": null,
    "status": "present"
  },
  "roles": ["employee"],
  "permissions": ["view attendance", "create leave"]
}
```

### **Update User**
```http
PUT /api/users/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Doe Updated",
  "phone": "+6281234567891",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response (200 OK):**
```json
{
  "id": 1,
  "name": "John Doe Updated",
  "email": "john@example.com",
  "phone": "+6281234567891",
  "is_approved": true,
  "updated_at": "2023-12-01T09:00:00.000000Z"
}
```

### **Approve User**
```http
POST /api/users/{id}/approve
Authorization: Bearer {token} (Admin only)
```

**Response (200 OK):**
```json
{
  "message": "User approved successfully",
  "user": {
    "id": 1,
    "name": "John Doe",
    "is_approved": true
  }
}
```

---

## 📊 **ATTENDANCE API**

### **Clock In**
```http
POST /api/attendance/check-in
Authorization: Bearer {token}
Content-Type: application/json

{
  "latitude": -6.2088,
  "longitude": 106.8456,
  "photo": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
  "notes": "Working from office"
}
```

**Response (201 Created):**
```json
{
  "id": 1,
  "user_id": 1,
  "check_in_time": "2023-12-01T08:30:00.000000Z",
  "latitude": -6.2088,
  "longitude": 106.8456,
  "photo_url": "https://absensi.smkn1punggelan.sch.id/storage/attendance/1/checkin.jpg",
  "notes": "Working from office",
  "status": "present"
}
```

### **Clock Out**
```http
POST /api/attendance/check-out
Authorization: Bearer {token}
Content-Type: application/json

{
  "latitude": -6.2088,
  "longitude": 106.8456,
  "photo": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ...",
  "notes": "End of workday"
}
```

**Response (200 OK):**
```json
{
  "id": 1,
  "user_id": 1,
  "check_in_time": "2023-12-01T08:30:00.000000Z",
  "check_out_time": "2023-12-01T17:30:00.000000Z",
  "latitude": -6.2088,
  "longitude": 106.8456,
  "photo_url": "https://absensi.smkn1punggelan.sch.id/storage/attendance/1/checkout.jpg",
  "notes": "End of workday",
  "status": "present",
  "work_hours": 9
}
```

### **Get Attendance History**
```http
GET /api/attendance
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (integer): Page number
- `per_page` (integer): Items per page
- `start_date` (date): Filter from date (YYYY-MM-DD)
- `end_date` (date): Filter to date (YYYY-MM-DD)
- `status` (string): Filter by status (present, absent, late)

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "date": "2023-12-01",
      "check_in_time": "08:30:00",
      "check_out_time": "17:30:00",
      "work_hours": 9,
      "status": "present",
      "latitude": -6.2088,
      "longitude": 106.8456,
      "photo_checkin_url": "https://...",
      "photo_checkout_url": "https://...",
      "notes": "Working from office"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### **Get Today's Attendance**
```http
GET /api/attendance/today
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "date": "2023-12-01",
  "check_in_time": "08:30:00",
  "check_out_time": null,
  "status": "present",
  "can_check_in": false,
  "can_check_out": true,
  "work_hours": null,
  "schedule": {
    "start_time": "08:00:00",
    "end_time": "17:00:00",
    "break_start": "12:00:00",
    "break_end": "13:00:00"
  }
}
```

### **Update Attendance**
```http
PUT /api/attendance/{id}
Authorization: Bearer {token} (Admin only)
Content-Type: application/json

{
  "check_in_time": "08:30:00",
  "check_out_time": "17:30:00",
  "status": "present",
  "notes": "Updated by admin"
}
```

**Response (200 OK):**
```json
{
  "id": 1,
  "user_id": 1,
  "date": "2023-12-01",
  "check_in_time": "08:30:00",
  "check_out_time": "17:30:00",
  "work_hours": 9,
  "status": "present",
  "notes": "Updated by admin"
}
```

---

## 📝 **LEAVE MANAGEMENT API**

### **Create Leave Request**
```http
POST /api/leaves
Authorization: Bearer {token}
Content-Type: application/json

{
  "type": "annual",
  "start_date": "2023-12-15",
  "end_date": "2023-12-20",
  "reason": "Family vacation",
  "contact_number": "+6281234567890",
  "address_during_leave": "Jakarta, Indonesia"
}
```

**Leave Types:**
- `annual`: Annual leave
- `sick`: Sick leave
- `emergency`: Emergency leave
- `maternity`: Maternity leave
- `other`: Other types

**Response (201 Created):**
```json
{
  "id": 1,
  "user_id": 1,
  "type": "annual",
  "start_date": "2023-12-15",
  "end_date": "2023-12-20",
  "days_requested": 6,
  "reason": "Family vacation",
  "status": "pending",
  "contact_number": "+6281234567890",
  "address_during_leave": "Jakarta, Indonesia",
  "created_at": "2023-12-01T10:00:00.000000Z"
}
```

### **List Leave Requests**
```http
GET /api/leaves
Authorization: Bearer {token}
```

**Query Parameters:**
- `page` (integer): Page number
- `per_page` (integer): Items per page
- `status` (string): Filter by status (pending, approved, rejected)
- `type` (string): Filter by leave type

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "user": {
        "name": "John Doe",
        "email": "john@example.com"
      },
      "type": "annual",
      "start_date": "2023-12-15",
      "end_date": "2023-12-20",
      "days_requested": 6,
      "reason": "Family vacation",
      "status": "pending",
      "approved_by": null,
      "approved_at": null,
      "created_at": "2023-12-01T10:00:00.000000Z"
    }
  ],
  "links": {...},
  "meta": {...}
}
```

### **Approve/Reject Leave**
```http
POST /api/leaves/{id}/approve
Authorization: Bearer {token} (Admin only)
Content-Type: application/json

{
  "action": "approve", // or "reject"
  "notes": "Approved for family vacation"
}
```

**Response (200 OK):**
```json
{
  "id": 1,
  "status": "approved",
  "approved_by": 2,
  "approved_at": "2023-12-01T11:00:00.000000Z",
  "notes": "Approved for family vacation"
}
```

### **Get Leave Balance**
```http
GET /api/leaves/balance
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "annual_leave": {
    "total": 12,
    "used": 5,
    "remaining": 7,
    "pending": 2
  },
  "sick_leave": {
    "total": 14,
    "used": 2,
    "remaining": 12,
    "pending": 0
  },
  "emergency_leave": {
    "total": 7,
    "used": 0,
    "remaining": 7,
    "pending": 1
  }
}
```

---

## 🏢 **OFFICE MANAGEMENT API**

### **List Offices**
```http
GET /api/offices
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Kantor Pusat SMKN 1 Punggelan",
      "address": "Jl. Raya Punggelan No. 1",
      "latitude": -6.2088,
      "longitude": 106.8456,
      "radius": 100,
      "is_active": true,
      "created_at": "2023-12-01T08:00:00.000000Z"
    }
  ]
}
```

### **Create Office**
```http
POST /api/offices
Authorization: Bearer {token} (Admin only)
Content-Type: application/json

{
  "name": "Cabang Jakarta",
  "address": "Jl. Sudirman No. 123, Jakarta",
  "latitude": -6.2088,
  "longitude": 106.8456,
  "radius": 150,
  "is_active": true
}
```

**Response (201 Created):**
```json
{
  "id": 2,
  "name": "Cabang Jakarta",
  "address": "Jl. Sudirman No. 123, Jakarta",
  "latitude": -6.2088,
  "longitude": 106.8456,
  "radius": 150,
  "is_active": true,
  "created_at": "2023-12-01T12:00:00.000000Z"
}
```

---

## 📅 **SCHEDULE MANAGEMENT API**

### **List Schedules**
```http
GET /api/schedules
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Jadwal Kerja Normal",
      "description": "Senin - Jumat, 08:00 - 17:00",
      "is_default": true,
      "is_active": true,
      "shifts": [
        {
          "id": 1,
          "name": "Shift Pagi",
          "start_time": "08:00:00",
          "end_time": "17:00:00",
          "break_start": "12:00:00",
          "break_end": "13:00:00",
          "days": ["monday", "tuesday", "wednesday", "thursday", "friday"]
        }
      ],
      "created_at": "2023-12-01T08:00:00.000000Z"
    }
  ]
}
```

### **Assign Schedule to User**
```http
POST /api/users/{userId}/schedule
Authorization: Bearer {token} (Admin only)
Content-Type: application/json

{
  "schedule_id": 1,
  "start_date": "2023-12-01",
  "end_date": null
}
```

**Response (201 Created):**
```json
{
  "user_id": 1,
  "schedule_id": 1,
  "start_date": "2023-12-01",
  "end_date": null,
  "assigned_at": "2023-12-01T13:00:00.000000Z"
}
```

---

## ⏰ **SHIFT MANAGEMENT API**

### **List Shifts**
```http
GET /api/shifts
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Shift Pagi",
      "start_time": "08:00:00",
      "end_time": "17:00:00",
      "break_start": "12:00:00",
      "break_end": "13:00:00",
      "days": ["monday", "tuesday", "wednesday", "thursday", "friday"],
      "is_active": true,
      "created_at": "2023-12-01T08:00:00.000000Z"
    }
  ]
}
```

### **Create Shift**
```http
POST /api/shifts
Authorization: Bearer {token} (Admin only)
Content-Type: application/json

{
  "name": "Shift Malam",
  "start_time": "16:00:00",
  "end_time": "00:00:00",
  "break_start": "20:00:00",
  "break_end": "21:00:00",
  "days": ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday"],
  "is_active": true
}
```

**Response (201 Created):**
```json
{
  "id": 2,
  "name": "Shift Malam",
  "start_time": "16:00:00",
  "end_time": "00:00:00",
  "break_start": "20:00:00",
  "break_end": "21:00:00",
  "days": ["monday", "tuesday", "wednesday", "thursday", "friday", "saturday"],
  "is_active": true,
  "created_at": "2023-12-01T14:00:00.000000Z"
}
```

---

## ⚠️ **ERROR HANDLING**

### **HTTP Status Codes**
- `200 OK`: Request successful
- `201 Created`: Resource created successfully
- `400 Bad Request`: Invalid request data
- `401 Unauthorized`: Authentication required
- `403 Forbidden`: Insufficient permissions
- `404 Not Found`: Resource not found
- `422 Unprocessable Entity`: Validation errors
- `429 Too Many Requests`: Rate limit exceeded
- `500 Internal Server Error`: Server error

### **Error Response Format**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The email field is required."
    ],
    "password": [
      "The password must be at least 8 characters."
    ]
  }
}
```

### **Common Error Codes**
- `VALIDATION_ERROR`: Input validation failed
- `AUTHENTICATION_FAILED`: Invalid credentials
- `AUTHORIZATION_FAILED`: Insufficient permissions
- `RESOURCE_NOT_FOUND`: Requested resource doesn't exist
- `RATE_LIMIT_EXCEEDED`: Too many requests
- `LOCATION_OUT_OF_RANGE`: GPS location outside office radius
- `ALREADY_CHECKED_IN`: User already checked in for today
- `LEAVE_BALANCE_INSUFFICIENT`: Not enough leave balance

---

## 🚦 **RATE LIMITING**

### **Rate Limits**
- **Authentication endpoints**: 5 requests per minute
- **API endpoints**: 60 requests per minute
- **File uploads**: 10 requests per minute

### **Rate Limit Headers**
```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1638360000
Retry-After: 60
```

### **Rate Limit Exceeded Response**
```json
{
  "message": "API rate limit exceeded.",
  "retry_after": 60
}
```

---

## 🧪 **API TESTING**

### **Using cURL**

#### **Login Example**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "device_name": "Test Device"
  }'
```

#### **Authenticated Request**
```bash
curl -X GET http://localhost:8000/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### **Using Postman**

#### **Environment Variables**
```
base_url: http://localhost:8000
token: YOUR_BEARER_TOKEN
```

#### **Authentication Setup**
1. Create new request
2. Set method to POST
3. URL: `{{base_url}}/api/login`
4. Body: raw JSON with login credentials
5. Send request and copy token from response
6. Set token in environment variable

#### **Test Collection**
```json
{
  "info": {
    "name": "Absensi API Tests",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Authentication",
      "item": [
        {
          "name": "Login",
          "request": {
            "method": "POST",
            "header": [
              {
                "key": "Content-Type",
                "value": "application/json"
              }
            ],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"admin@example.com\",\n  \"password\": \"password123\",\n  \"device_name\": \"Postman Test\"\n}"
            },
            "url": {
              "raw": "{{base_url}}/api/login",
              "host": ["{{base_url}}"],
              "path": ["api", "login"]
            }
          }
        }
      ]
    }
  ]
}
```

### **Automated Testing**

#### **Using PHPUnit**
```php
class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'is_approved' => true
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
            'device_name' => 'Test Device'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'user',
                    'token',
                    'token_type'
                ]);
    }
}
```

#### **API Documentation Generation**
```bash
# Install API documentation generator
composer require --dev mpociot/laravel-apidoc-generator

# Generate documentation
php artisan api:generate --routePrefix="api/*" --actAsUserId=1
```

---

## 📱 **MOBILE INTEGRATION**

### **SDK Integration**

#### **Android (Kotlin)**
```kotlin
class ApiService {
    private val retrofit = Retrofit.Builder()
        .baseUrl("https://absensi.smkn1punggelan.sch.id/api/")
        .addConverterFactory(GsonConverterFactory.create())
        .build()

    private val api = retrofit.create(AbsensiApi::class.java)

    suspend fun login(email: String, password: String): LoginResponse {
        return api.login(LoginRequest(email, password, "Android App"))
    }

    suspend fun checkIn(token: String, request: CheckInRequest): AttendanceResponse {
        return api.checkIn("Bearer $token", request)
    }
}
```

#### **iOS (Swift)**
```swift
class ApiService {
    private let baseURL = "https://absensi.smkn1punggelan.sch.id/api/"
    
    func login(email: String, password: String) async throws -> LoginResponse {
        let url = URL(string: baseURL + "login")!
        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        request.setValue("application/json", forHTTPHeaderField: "Content-Type")
        
        let body = LoginRequest(email: email, password: password, deviceName: "iOS App")
        request.httpBody = try JSONEncoder().encode(body)
        
        let (data, _) = try await URLSession.shared.data(for: request)
        return try JSONDecoder().decode(LoginResponse.self, from: data)
    }
}
```

### **Real-time Updates**
```javascript
// WebSocket connection for real-time attendance updates
const socket = io('https://absensi.smkn1punggelan.sch.id', {
    auth: {
        token: localStorage.getItem('token')
    }
});

socket.on('attendance.updated', (data) => {
    console.log('Attendance updated:', data);
    // Update UI accordingly
});
```

---

## 🔧 **API VERSIONS**

### **Versioning Strategy**
- API versioning menggunakan URL prefix: `/api/v1/`
- Breaking changes akan menggunakan versi baru
- Deprecation notice 6 bulan sebelum penghapusan versi lama

### **Current Version: v1**
- Base URL: `https://absensi.smkn1punggelan.sch.id/api/v1/`
- Status: Active
- End of Life: TBD

### **Future Versions**
- **v2** (Planned for Q2 2024):
  - GraphQL support
  - Enhanced real-time features
  - Improved error handling

---

*This API documentation is automatically generated and should be kept in sync with code changes. Last updated: December 2023*