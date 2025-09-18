<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReportTemplate;

class ReportTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ReportTemplate::create([
            'name' => 'Laporan Absensi Harian',
            'type' => 'attendance',
            'description' => 'Laporan absensi harian untuk semua karyawan',
            'filters' => ['month' => now()->format('Y-m')],
            'recipients' => [
                ['email' => 'admin@example.com', 'name' => 'Administrator']
            ],
            'schedule_frequency' => 'daily',
            'schedule_time' => '08:00',
            'is_active' => true
        ]);

        ReportTemplate::create([
            'name' => 'Laporan Cuti Mingguan',
            'type' => 'leave',
            'description' => 'Laporan cuti dan izin karyawan mingguan',
            'filters' => [
                'start_date' => now()->startOfWeek()->format('Y-m-d'),
                'end_date' => now()->endOfWeek()->format('Y-m-d')
            ],
            'recipients' => [
                ['email' => 'hr@example.com', 'name' => 'HR Manager']
            ],
            'schedule_frequency' => 'weekly',
            'schedule_day' => 'monday',
            'schedule_time' => '09:00',
            'is_active' => true
        ]);

        ReportTemplate::create([
            'name' => 'Ringkasan Bulanan',
            'type' => 'monthly-summary',
            'description' => 'Ringkasan absensi dan cuti bulanan',
            'filters' => [],
            'recipients' => [
                ['email' => 'manager@example.com', 'name' => 'Manager'],
                ['email' => 'admin@example.com', 'name' => 'Administrator']
            ],
            'schedule_frequency' => 'monthly',
            'schedule_time' => '07:00',
            'is_active' => true
        ]);

        ReportTemplate::create([
            'name' => 'Laporan Manual - Testing',
            'type' => 'attendance',
            'description' => 'Template untuk testing manual generation',
            'filters' => ['user_id' => 1],
            'recipients' => [],
            'schedule_frequency' => 'manual',
            'is_active' => false
        ]);
    }
}
