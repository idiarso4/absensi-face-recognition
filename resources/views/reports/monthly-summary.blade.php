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
            border-bottom: 2px solid #059669;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #059669;
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
            font-size: 16px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: #f0fdf4;
            border: 2px solid #bbf7d0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }

        .summary-value {
            font-size: 28px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 5px;
        }

        .summary-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
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
            vertical-align: middle;
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

        .performance-good {
            background: #dcfce7;
            color: #166534;
        }

        .performance-average {
            background: #fef3c7;
            color: #92400e;
        }

        .performance-poor {
            background: #fee2e2;
            color: #991b1b;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #6b7280;
            font-style: italic;
        }

        .performance-bar {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 5px;
        }

        .performance-fill {
            height: 100%;
            background: linear-gradient(90deg, #dc2626 0%, #f59e0b 50%, #059669 100%);
            width: 0%;
            transition: width 0.3s ease;
        }

        .legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 20px;
            font-size: 10px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $report_title }}</h1>
        <div class="subtitle">Sistem Absensi SMKN 1 Punggelan</div>
        <div class="period">{{ $month_name }} {{ $year }}</div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-value">{{ number_format($total_stats['total_users']) }}</div>
            <div class="summary-label">Total Karyawan</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">{{ number_format($total_stats['total_attendances']) }}</div>
            <div class="summary-label">Total Absensi</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">{{ number_format($total_stats['total_leaves']) }}</div>
            <div class="summary-label">Total Cuti</div>
        </div>
        <div class="summary-card">
            <div class="summary-value">{{ $total_stats['avg_attendance_rate'] }}%</div>
            <div class="summary-label">Rata-rata Kehadiran</div>
        </div>
    </div>

    <!-- Legend -->
    <div class="legend">
        <div class="legend-item">
            <div class="legend-color" style="background: linear-gradient(90deg, #dc2626 0%, #f59e0b 50%, #059669 100%);"></div>
            <span>Tingkat Kehadiran</span>
        </div>
    </div>

    <!-- Monthly Summary Table -->
    @if(count($summary_data) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">Nama Karyawan</th>
                    <th style="width: 12%;">Email</th>
                    <th style="width: 10%;">Hari Kerja</th>
                    <th style="width: 10%;">Hadir</th>
                    <th style="width: 10%;">Cuti</th>
                    <th style="width: 15%;">Tingkat Kehadiran</th>
                    <th style="width: 13%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summary_data as $index => $data)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $data['user']->name }}</td>
                    <td>{{ $data['user']->email }}</td>
                    <td style="text-align: center;">{{ $data['working_days'] }}</td>
                    <td style="text-align: center;">{{ $data['attendances'] }}</td>
                    <td style="text-align: center;">{{ $data['leaves'] }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-weight: bold;">{{ $data['attendance_rate'] }}%</span>
                            <div class="performance-bar">
                                <div class="performance-fill" style="width: {{ min($data['attendance_rate'], 100) }}%;"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($data['attendance_rate'] >= 90)
                            <span style="background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: bold;">EXCELLENT</span>
                        @elseif($data['attendance_rate'] >= 75)
                            <span style="background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: bold;">GOOD</span>
                        @elseif($data['attendance_rate'] >= 50)
                            <span style="background: #fed7aa; color: #9a3412; padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: bold;">AVERAGE</span>
                        @else
                            <span style="background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: bold;">NEEDS IMPROVEMENT</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Performance Summary -->
        <div style="margin-top: 20px; padding: 20px; background: #f8fafc; border-radius: 8px; border-left: 4px solid #059669;">
            <h3 style="margin: 0 0 15px 0; color: #374151; font-size: 14px;">Ringkasan Performa</h3>
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px;">
                @php
                    $excellent = count(array_filter($summary_data, fn($d) => $d['attendance_rate'] >= 90));
                    $good = count(array_filter($summary_data, fn($d) => $d['attendance_rate'] >= 75 && $d['attendance_rate'] < 90));
                    $average = count(array_filter($summary_data, fn($d) => $d['attendance_rate'] >= 50 && $d['attendance_rate'] < 75));
                    $poor = count(array_filter($summary_data, fn($d) => $d['attendance_rate'] < 50));
                @endphp
                <div style="text-align: center;">
                    <div style="font-size: 18px; font-weight: bold; color: #166534;">{{ $excellent }}</div>
                    <div style="font-size: 10px; color: #6b7280;">EXCELLENT (≥90%)</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 18px; font-weight: bold; color: #92400e;">{{ $good }}</div>
                    <div style="font-size: 10px; color: #6b7280;">GOOD (75-89%)</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 18px; font-weight: bold; color: #9a3412;">{{ $average }}</div>
                    <div style="font-size: 10px; color: #6b7280;">AVERAGE (50-74%)</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 18px; font-weight: bold; color: #991b1b;">{{ $poor }}</div>
                    <div style="font-size: 10px; color: #6b7280;">NEEDS IMPROVEMENT (<50%)</div>
                </div>
            </div>
        </div>
    @else
        <div class="no-data">
            <p>Tidak ada data karyawan untuk periode yang dipilih.</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dihasilkan pada {{ $generated_at->format('d F Y H:i:s') }}</p>
        <p>Sistem Absensi SMKN 1 Punggelan - Generated by Laravel</p>
    </div>
</body>
</html>