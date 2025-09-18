<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportService;
use App\Mail\ReportGenerated;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GenerateReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-reports
                            {type : Type of report (attendance, leave, monthly-summary)}
                            {--user_id= : User ID for individual reports}
                            {--month= : Month for reports (YYYY-MM format)}
                            {--start_date= : Start date for date range (YYYY-MM-DD)}
                            {--end_date= : End date for date range (YYYY-MM-DD)}
                            {--email= : Email address to send report to}
                            {--save : Save report to storage instead of downloading}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate various types of attendance and leave reports';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $reportService = new ReportService();

        $this->info("🧾 Generating {$type} report...");

        // Build filters
        $filters = [];
        if ($this->option('user_id')) {
            $filters['user_id'] = $this->option('user_id');
        }
        if ($this->option('month')) {
            $filters['month'] = $this->option('month');
        }
        if ($this->option('start_date') && $this->option('end_date')) {
            $filters['start_date'] = $this->option('start_date');
            $filters['end_date'] = $this->option('end_date');
        }

        try {
            $pdfContent = null;
            $filename = '';

            switch ($type) {
                case 'attendance':
                    $pdfContent = $reportService->generateAttendanceReport($filters);
                    $filename = 'laporan-absensi-' . now()->format('Y-m-d-H-i-s') . '.pdf';
                    break;

                case 'leave':
                    $pdfContent = $reportService->generateLeaveReport($filters);
                    $filename = 'laporan-cuti-' . now()->format('Y-m-d-H-i-s') . '.pdf';
                    break;

                case 'monthly-summary':
                    if (!$this->option('month')) {
                        $this->error('❌ Month parameter is required for monthly-summary report');
                        return 1;
                    }

                    $date = Carbon::parse($this->option('month'));
                    $pdfContent = $reportService->generateMonthlySummaryReport(
                        $date->year,
                        $date->month
                    );
                    $filename = "ringkasan-bulanan-{$date->year}-{$date->month}-" . now()->format('Y-m-d-H-i-s') . '.pdf';
                    break;

                default:
                    $this->error('❌ Invalid report type. Available types: attendance, leave, monthly-summary');
                    return 1;
            }

            if ($this->option('save')) {
                // Save to storage
                Storage::disk('local')->put('reports/' . $filename, $pdfContent);
                $this->info("✅ Report saved to: storage/app/reports/{$filename}");
            } elseif ($this->option('email')) {
                // Send via email
                $this->sendReportEmail($pdfContent, $filename, $this->option('email'), $type);
                $this->info("✅ Report sent to: {$this->option('email')}");
            } else {
                // Just generate (for testing)
                $this->info("✅ Report generated successfully: {$filename}");
                $this->info("💡 Use --save flag to save to storage or --email flag to send via email");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error generating report: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Send report via email
     */
    private function sendReportEmail(string $pdfContent, string $filename, string $email, string $type): void
    {
        $metadata = [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'period' => $this->getReportPeriodText($type),
        ];

        // Add additional metadata based on report type
        switch ($type) {
            case 'attendance':
                $metadata['total_records'] = $this->getAttendanceCount();
                break;
            case 'leave':
                $metadata['total_records'] = $this->getLeaveCount();
                break;
            case 'monthly-summary':
                $metadata['total_users'] = $this->getUserCount();
                break;
        }

        Mail::to($email)->send(new ReportGenerated($type, $pdfContent, $filename, $metadata));
    }

    /**
     * Get report period text for metadata
     */
    private function getReportPeriodText(string $type): string
    {
        if ($this->option('month')) {
            return Carbon::parse($this->option('month'))->locale('id')->format('F Y');
        } elseif ($this->option('start_date') && $this->option('end_date')) {
            return Carbon::parse($this->option('start_date'))->format('d/m/Y') . ' - ' .
                   Carbon::parse($this->option('end_date'))->format('d/m/Y');
        }

        return 'Periode Terbaru';
    }

    /**
     * Get attendance count for metadata
     */
    private function getAttendanceCount(): int
    {
        $query = \App\Models\Attendance::query();

        if ($this->option('user_id')) {
            $query->where('user_id', $this->option('user_id'));
        }

        if ($this->option('month')) {
            $month = Carbon::parse($this->option('month'));
            $query->whereYear('created_at', $month->year)
                  ->whereMonth('created_at', $month->month);
        }

        return $query->count();
    }

    /**
     * Get leave count for metadata
     */
    private function getLeaveCount(): int
    {
        $query = \App\Models\Leave::query();

        if ($this->option('user_id')) {
            $query->where('user_id', $this->option('user_id'));
        }

        if ($this->option('status')) {
            $query->where('status', $this->option('status'));
        }

        if ($this->option('month')) {
            $month = Carbon::parse($this->option('month'));
            $query->whereYear('start_date', $month->year)
                  ->whereMonth('start_date', $month->month);
        }

        return $query->count();
    }

    /**
     * Get user count for metadata
     */
    private function getUserCount(): int
    {
        return \App\Models\User::where('is_approved', true)->count();
    }
}
