<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

\DB::table('report_templates')->insert([
    [
        'name' => 'Laporan Absensi Harian',
        'type' => 'attendance',
        'description' => 'Laporan absensi harian untuk semua karyawan',
        'filters' => json_encode(['month' => date('Y-m')]),
        'recipients' => json_encode([['email' => 'admin@example.com', 'name' => 'Administrator']]),
        'schedule_frequency' => 'daily',
        'schedule_day' => null,
        'schedule_time' => '08:00',
        'is_active' => 1,
        'last_generated_at' => null,
        'created_at' => now(),
        'updated_at' => now()
    ],
    [
        'name' => 'Laporan Manual - Testing',
        'type' => 'attendance',
        'description' => 'Template untuk testing manual generation',
        'filters' => json_encode(['user_id' => 1]),
        'recipients' => json_encode([]),
        'schedule_frequency' => 'manual',
        'schedule_day' => null,
        'schedule_time' => null,
        'is_active' => 0,
        'last_generated_at' => null,
        'created_at' => now(),
        'updated_at' => now()
    ]
]);

echo 'Sample data inserted successfully';