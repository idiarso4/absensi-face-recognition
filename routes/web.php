<?php
use Illuminate\Support\Facades\Route;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;
use App\Http\Controllers\Auth\RegisterController;
use App\Livewire\Presensi;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;

// Root route - redirect to admin login
Route::get('/', function () {
    return redirect('/admin');
})->name('home');

Route::group(['middleware' => 'auth'], function() {
    Route::get('presensi', Presensi::class)->name('presensi');
    Route::get('attendance/export', function () {
        return Excel::download(new AttendanceExport, 'attendances.xlsx');
    })->name('attendance-export');
});

Route::get('/admin/extend-session', function (Request $request) {
    // Extend the session by updating the last activity
    session()->put('last_activity', time());
    return response()->json(['status' => 'success']);
})->middleware('auth');

// Download user import template
Route::get('/user-template/download', function () {
    $filename = 'user_import_template.csv';
    $headers = [
        'name,email,password,is_approved',
        'John Doe,john@example.com,password123,0',
        'Jane Smith,jane@example.com,password123,1',
        'Bob Johnson,bob@example.com,,0',
    ];

    $content = implode("\n", $headers);

    return response($content)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
})->name('user-template.download');

// Report generation routes
Route::middleware('auth')->group(function() {
    Route::get('/reports/attendance/generate', function (Request $request) {
        $reportService = new ReportService();
        $filters = $request->only(['user_id', 'start_date', 'end_date', 'month']);

        return $reportService->generateAttendanceReport($filters);
    })->name('reports.attendance.generate');

    Route::get('/reports/leave/generate', function (Request $request) {
        $reportService = new ReportService();
        $filters = $request->only(['user_id', 'status', 'start_date', 'end_date', 'month']);

        return $reportService->generateLeaveReport($filters);
    })->name('reports.leave.generate');

    Route::get('/reports/monthly-summary/generate/{year}/{month}', function (Request $request, $year, $month) {
        $reportService = new ReportService();

        return $reportService->generateMonthlySummaryReport((int)$year, (int)$month);
    })->name('reports.monthly-summary.generate');

    // Download saved reports
    Route::get('/reports/download/{filename}', function ($filename) {
        $path = storage_path('app/reports/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'Report file not found');
        }

        return response()->download($path);
    })->name('reports.download');
});

// Public registration routes (only if enabled)
if (env('ALLOW_PUBLIC_REGISTRATION', false)) {
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
}

// Email verification routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/admin')->with('status', 'Email berhasil diverifikasi!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', 'Link verifikasi telah dikirim ke email Anda.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Password reset routes
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
        ? back()->with(['status' => __($status)])
        : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('status', __($status))
        : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');

// Test route to check error logging (only in local environment)
if (env('APP_ENV') === 'local') {
    Route::get('/test-error', function () {
        throw new \Exception('Test web error for logging verification');
    })->name('test.error');
}

