<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $report_title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .header .subtitle {
            color: #6b7280;
            margin: 5px 0;
            font-size: 14px;
        }

        .header .period {
            color: #374151;
            font-weight: bold;
            margin-top: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 10px;
        }

        th, td {
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f1f5f9;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }

        tr:nth-child(even) {
            background: #f8fafc;
        }

        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-on-time {
            background: #dcfce7;
            color: #166534;
        }

        .status-late {
            background: #fef3c7;
            color: #92400e;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }

        .page-break {
            page-break-before: always;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-style: italic;
        }

        .user-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2563eb;
        }

        .user-info strong {
            color: #2563eb;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $report_title }}</h1>
        <div class="subtitle">Sistem Absensi SMKN 1 Punggelan</div>
        <div class="period">Periode: {{ $period }}</div>
    </div>

    <!-- User Filter Info -->
    @if(isset($filters['user_id']))
        @php
            $user = \App\Models\User::find($filters['user_id']);
        @endphp
        @if($user)
        <div class="user-info">
            <strong>Laporan untuk:</strong> {{ $user->name }} ({{ $user->email }})
        </div>
        @endif
    @endif

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['total_attendances']) }}</div>
            <div class="stat-label">Total Absensi</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['on_time_count']) }}</div>
            <div class="stat-label">Tepat Waktu</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['late_count']) }}</div>
            <div class="stat-label">Terlambat</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['on_time_percentage'] }}%</div>
            <div class="stat-label">Persentase Tepat Waktu</div>
        </div>
    </div>

    <!-- Attendance Data Table -->
    @if($attendances->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 20%;">Nama</th>
                    <th style="width: 15%;">Tanggal</th>
                    <th style="width: 12%;">Check In</th>
                    <th style="width: 12%;">Check Out</th>
                    <th style="width: 15%;">Lokasi</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 11%;">Durasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $index => $attendance)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $attendance->user->name ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d/m/Y') }}</td>
                    <td>{{ $attendance->check_in_time ? \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i:s') : '-' }}</td>
                    <td>{{ $attendance->check_out_time ? \Carbon\Carbon::parse($attendance->check_out_time)->format('H:i:s') : '-' }}</td>
                    <td>
                        @if($attendance->start_latitude && $attendance->start_longitude)
                            {{ round($attendance->start_latitude, 6) }}, {{ round($attendance->start_longitude, 6) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @php
                            $isOnTime = $attendance->check_in_time && \Carbon\Carbon::parse($attendance->check_in_time)->format('H:i:s') <= '08:00:00';
                        @endphp
                        <span class="status-badge {{ $isOnTime ? 'status-on-time' : 'status-late' }}">
                            {{ $isOnTime ? 'Tepat Waktu' : 'Terlambat' }}
                        </span>
                    </td>
                    <td>
                        @if($attendance->check_in_time && $attendance->check_out_time)
                            @php
                                $start = \Carbon\Carbon::parse($attendance->check_in_time);
                                $end = \Carbon\Carbon::parse($attendance->check_out_time);
                                $duration = $start->diff($end);
                            @endphp
                            {{ $duration->format('%Hh %Im') }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>Tidak ada data absensi untuk periode yang dipilih.</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dihasilkan pada {{ $generated_at->format('d F Y H:i:s') }}</p>
        <p>Sistem Absensi SMKN 1 Punggelan - Generated by Laravel</p>
    </div>
</body>
</html>