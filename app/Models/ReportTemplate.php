<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReportTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'filters',
        'recipients',
        'schedule_frequency',
        'schedule_day',
        'schedule_time',
        'is_active',
        'last_generated_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'recipients' => 'array',
        'is_active' => 'boolean',
        'last_generated_at' => 'datetime',
    ];

    /**
     * Get the report type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'attendance' => 'Laporan Absensi',
            'leave' => 'Laporan Cuti/Izin',
            'monthly-summary' => 'Ringkasan Bulanan',
            default => ucfirst($this->type)
        };
    }

    /**
     * Get the schedule frequency label
     */
    public function getScheduleFrequencyLabelAttribute(): string
    {
        return match($this->schedule_frequency) {
            'manual' => 'Manual',
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            default => ucfirst($this->schedule_frequency)
        };
    }

    /**
     * Get the schedule day label
     */
    public function getScheduleDayLabelAttribute(): string
    {
        if (!$this->schedule_day) return '-';

        $days = [
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            'sunday' => 'Minggu',
        ];

        return $days[$this->schedule_day] ?? ucfirst($this->schedule_day);
    }

    /**
     * Check if the report should be generated now
     */
    public function shouldGenerateNow(): bool
    {
        if (!$this->is_active || $this->schedule_frequency === 'manual') {
            return false;
        }

        $now = now();

        switch ($this->schedule_frequency) {
            case 'daily':
                return $now->format('H:i') === $this->schedule_time;

            case 'weekly':
                return $now->format('l') === ucfirst($this->schedule_day) &&
                       $now->format('H:i') === $this->schedule_time;

            case 'monthly':
                return $now->day === 1 && // First day of month
                       $now->format('H:i') === $this->schedule_time;

            default:
                return false;
        }
    }

    /**
     * Generate the report using this template
     */
    public function generateReport()
    {
        $reportService = app(\App\Services\ReportService::class);

        // Generate the report based on type
        $pdfContent = match($this->type) {
            'attendance' => $reportService->generateAttendanceReport($this->filters ?? []),
            'leave' => $reportService->generateLeaveReport($this->filters ?? []),
            'monthly-summary' => $reportService->generateMonthlySummaryReport(
                now()->year,
                now()->month
            ),
        };

        // Update last generated timestamp
        $this->update(['last_generated_at' => now()]);

        return $pdfContent;
    }

    /**
     * Send the report to recipients
     */
    public function sendToRecipients(string $pdfContent, string $filename): void
    {
        if (empty($this->recipients)) {
            return;
        }

        $metadata = [
            'template_name' => $this->name,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'period' => $this->getReportPeriodText(),
        ];

        foreach ($this->recipients as $recipient) {
            if (isset($recipient['email']) && filter_var($recipient['email'], FILTER_VALIDATE_EMAIL)) {
                \Illuminate\Support\Facades\Mail::to($recipient['email'])
                    ->send(new \App\Mail\ReportGenerated($this->type, $pdfContent, $filename, $metadata));
            }
        }
    }

    /**
     * Get report period text for this template
     */
    private function getReportPeriodText(): string
    {
        if (isset($this->filters['month'])) {
            return \Carbon\Carbon::parse($this->filters['month'])->locale('id')->format('F Y');
        } elseif (isset($this->filters['start_date']) && isset($this->filters['end_date'])) {
            return \Carbon\Carbon::parse($this->filters['start_date'])->format('d/m/Y') . ' - ' .
                   \Carbon\Carbon::parse($this->filters['end_date'])->format('d/m/Y');
        }

        return 'Periode Terbaru';
    }
}
