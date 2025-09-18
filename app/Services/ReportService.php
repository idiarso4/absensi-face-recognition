<?php

namespace App\Services;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Storage;

class ReportService
{
    /**
     * Generate attendance report PDF
     */
    public function generateAttendanceReport(array $filters = []): string
    {
        $query = Attendance::with(['user'])
            ->select([
                'attendances.*',
                DB::raw('DATE(created_at) as attendance_date'),
                DB::raw('TIME(start_time) as check_in_time'),
                DB::raw('TIME(end_time) as check_out_time')
            ]);

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($filters['start_date'])->startOfDay(),
                Carbon::parse($filters['end_date'])->endOfDay()
            ]);
        } elseif (isset($filters['month'])) {
            $month = Carbon::parse($filters['month']);
            $query->whereYear('created_at', $month->year)
                  ->whereMonth('created_at', $month->month);
        }

        $attendances = $query->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = $this->calculateAttendanceStats($attendances, $filters);

        $data = [
            'attendances' => $attendances,
            'stats' => $stats,
            'filters' => $filters,
            'generated_at' => now(),
            'report_title' => 'Laporan Absensi',
            'period' => $this->getReportPeriod($filters)
        ];

        $pdf = Pdf::loadView('reports.attendance', $data);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('laporan-absensi-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Generate leave report PDF
     */
    public function generateLeaveReport(array $filters = []): string
    {
        $query = Leave::with(['user'])
            ->select(['leaves.*']);

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->where(function($q) use ($filters) {
                $q->whereBetween('start_date', [$filters['start_date'], $filters['end_date']])
                  ->orWhereBetween('end_date', [$filters['start_date'], $filters['end_date']]);
            });
        } elseif (isset($filters['month'])) {
            $month = Carbon::parse($filters['month']);
            $query->whereYear('start_date', $month->year)
                  ->whereMonth('start_date', $month->month);
        }

        $leaves = $query->orderBy('created_at', 'desc')->get();

        // Calculate statistics
        $stats = $this->calculateLeaveStats($leaves, $filters);

        $data = [
            'leaves' => $leaves,
            'stats' => $stats,
            'filters' => $filters,
            'generated_at' => now(),
            'report_title' => 'Laporan Cuti/Izin',
            'period' => $this->getReportPeriod($filters)
        ];

        $pdf = Pdf::loadView('reports.leave', $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('laporan-cuti-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Generate monthly summary report PDF
     */
    public function generateMonthlySummaryReport(int $year, int $month): string
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get all users
        $users = User::where('is_approved', true)->get();

        $summaryData = [];
        $totalStats = [
            'total_users' => $users->count(),
            'total_attendances' => 0,
            'total_leaves' => 0,
            'avg_attendance_rate' => 0
        ];

        foreach ($users as $user) {
            $userAttendances = Attendance::where('user_id', $user->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $userLeaves = Leave::where('user_id', $user->id)
                ->where('status', 'approved')
                ->where(function($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate]);
                })
                ->count();

            $workingDays = $this->getWorkingDaysInMonth($year, $month);
            $attendanceRate = $workingDays > 0 ? round(($userAttendances / $workingDays) * 100, 2) : 0;

            $summaryData[] = [
                'user' => $user,
                'attendances' => $userAttendances,
                'leaves' => $userLeaves,
                'working_days' => $workingDays,
                'attendance_rate' => $attendanceRate
            ];

            $totalStats['total_attendances'] += $userAttendances;
            $totalStats['total_leaves'] += $userLeaves;
        }

        $totalStats['avg_attendance_rate'] = $users->count() > 0
            ? round(array_sum(array_column($summaryData, 'attendance_rate')) / $users->count(), 2)
            : 0;

        $data = [
            'summary_data' => $summaryData,
            'total_stats' => $totalStats,
            'month' => $month,
            'year' => $year,
            'month_name' => Carbon::create($year, $month, 1)->locale('id')->monthName,
            'generated_at' => now(),
            'report_title' => 'Ringkasan Bulanan Absensi'
        ];

        $pdf = Pdf::loadView('reports.monthly-summary', $data);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download("ringkasan-bulanan-{$year}-{$month}.pdf");
    }

    /**
     * Calculate attendance statistics
     */
    private function calculateAttendanceStats($attendances, array $filters = []): array
    {
        $total = $attendances->count();
        $onTime = $attendances->filter(function($attendance) {
            return $attendance->start_time && Carbon::parse($attendance->start_time)->format('H:i:s') <= '08:00:00';
        })->count();

        $late = $total - $onTime;

        return [
            'total_attendances' => $total,
            'on_time_count' => $onTime,
            'late_count' => $late,
            'on_time_percentage' => $total > 0 ? round(($onTime / $total) * 100, 2) : 0,
            'late_percentage' => $total > 0 ? round(($late / $total) * 100, 2) : 0
        ];
    }

    /**
     * Calculate leave statistics
     */
    private function calculateLeaveStats($leaves, array $filters = []): array
    {
        $total = $leaves->count();
        $approved = $leaves->where('status', 'approved')->count();
        $pending = $leaves->where('status', 'pending')->count();
        $rejected = $leaves->where('status', 'rejected')->count();

        $totalDays = $leaves->where('status', 'approved')->sum(function($leave) {
            return Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date)) + 1;
        });

        return [
            'total_leaves' => $total,
            'approved_count' => $approved,
            'pending_count' => $pending,
            'rejected_count' => $rejected,
            'total_leave_days' => $totalDays,
            'approved_percentage' => $total > 0 ? round(($approved / $total) * 100, 2) : 0
        ];
    }

    /**
     * Get working days in a month (excluding weekends)
     */
    private function getWorkingDaysInMonth(int $year, int $month): int
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $workingDays = 0;
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            if (!$currentDate->isWeekend()) {
                $workingDays++;
            }
            $currentDate->addDay();
        }

        return $workingDays;
    }

    /**
     * Get report period description
     */
    private function getReportPeriod(array $filters = []): string
    {
        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            return Carbon::parse($filters['start_date'])->format('d/m/Y') . ' - ' .
                   Carbon::parse($filters['end_date'])->format('d/m/Y');
        } elseif (isset($filters['month'])) {
            return Carbon::parse($filters['month'])->locale('id')->format('F Y');
        }

        return 'Semua Periode';
    }
}
