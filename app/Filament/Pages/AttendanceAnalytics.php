<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\User;
use App\Models\Leave;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class AttendanceAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.attendance-analytics';

    protected static ?string $navigationLabel = 'Analytics Absensi';

    protected static ?string $navigationGroup = 'Laporan & Analytics';

    protected static ?int $navigationSort = 10;

    public function getTitle(): string
    {
        return 'Analytics Absensi';
    }

    public function getAttendanceStats()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return [
            'total_users' => User::where('is_approved', true)->count(),
            'total_attendances_this_month' => Attendance::whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->count(),
            'on_time_percentage' => $this->calculateOnTimePercentage(),
            'late_count' => $this->getLateAttendancesCount(),
            'absent_today' => $this->getAbsentTodayCount(),
            'pending_leaves' => Leave::where('status', 'pending')->count(),
        ];
    }

    public function getMonthlyAttendanceData()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Attendance::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $data[] = [
                'month' => $date->format('M Y'),
                'count' => $count,
            ];
        }
        return $data;
    }

    public function getDailyAttendanceData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Attendance::whereDate('created_at', $date)->count();
            $data[] = [
                'day' => $date->format('D'),
                'count' => $count,
            ];
        }
        return $data;
    }

    public function getTopPerformers()
    {
        return User::withCount(['attendances' => function ($query) {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        }])
        ->where('is_approved', true)
        ->orderBy('attendances_count', 'desc')
        ->limit(5)
        ->get();
    }

    public function getDepartmentStats()
    {
        // Since we don't have departments, we'll show role-based stats
        return User::select('roles.name as role_name', DB::raw('COUNT(*) as user_count'))
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_type', User::class)
            ->where('users.is_approved', true)
            ->groupBy('roles.name')
            ->get();
    }

    private function calculateOnTimePercentage()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $totalAttendances = Attendance::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        if ($totalAttendances === 0) return 0;

        $onTimeCount = Attendance::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->whereRaw('TIME(start_time) <= TIME(schedule_start_time)')
            ->count();

        return round(($onTimeCount / $totalAttendances) * 100, 1);
    }

    private function getLateAttendancesCount()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return Attendance::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->whereRaw('TIME(start_time) > TIME(schedule_start_time)')
            ->count();
    }

    private function getAbsentTodayCount()
    {
        $approvedUsers = User::where('is_approved', true)->count();
        $presentToday = Attendance::whereDate('created_at', today())->distinct('user_id')->count();

        return max(0, $approvedUsers - $presentToday);
    }

    protected function getViewData(): array
    {
        return [
            'stats' => $this->getAttendanceStats(),
            'monthlyData' => $this->getMonthlyAttendanceData(),
            'dailyData' => $this->getDailyAttendanceData(),
            'topPerformers' => $this->getTopPerformers(),
            'departmentStats' => $this->getDepartmentStats(),
        ];
    }
}
