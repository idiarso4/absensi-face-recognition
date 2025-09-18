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
            border-bottom: 2px solid #dc2626;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #dc2626;
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
            color: #dc2626;
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

        .status-approved {
            background: #dcfce7;
            color: #166534;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-rejected {
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

        .user-info {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
        }

        .user-info strong {
            color: #dc2626;
        }

        .reason-cell {
            max-width: 200px;
            word-wrap: break-word;
        }

        .note-cell {
            max-width: 150px;
            word-wrap: break-word;
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
            <div class="stat-value">{{ number_format($stats['total_leaves']) }}</div>
            <div class="stat-label">Total Pengajuan</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['approved_count']) }}</div>
            <div class="stat-label">Disetujui</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ number_format($stats['pending_count']) }}</div>
            <div class="stat-label">Menunggu</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $stats['approved_percentage'] }}%</div>
            <div class="stat-label">Tingkat Persetujuan</div>
        </div>
    </div>

    <!-- Leave Data Table -->
    @if($leaves->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 18%;">Nama</th>
                    <th style="width: 12%;">Tanggal Mulai</th>
                    <th style="width: 12%;">Tanggal Selesai</th>
                    <th style="width: 8%;">Durasi</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 20%;">Alasan</th>
                    <th style="width: 15%;">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaves as $index => $leave)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $leave->user->name ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d/m/Y') }}</td>
                    <td>
                        @php
                            $days = \Carbon\Carbon::parse($leave->start_date)->diffInDays(\Carbon\Carbon::parse($leave->end_date)) + 1;
                        @endphp
                        {{ $days }} hari
                    </td>
                    <td>
                        <span class="status-badge status-{{ $leave->status }}">
                            @switch($leave->status)
                                @case('approved')
                                    Disetujui
                                    @break
                                @case('pending')
                                    Menunggu
                                    @break
                                @case('rejected')
                                    Ditolak
                                    @break
                                @default
                                    {{ ucfirst($leave->status) }}
                            @endswitch
                        </span>
                    </td>
                    <td class="reason-cell">{{ $leave->reason ?? '-' }}</td>
                    <td class="note-cell">{{ $leave->note ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        <div style="margin-top: 20px; padding: 15px; background: #f8fafc; border-radius: 8px;">
            <h3 style="margin: 0 0 10px 0; color: #374151; font-size: 14px;">Ringkasan Total Hari Cuti Disetujui</h3>
            <p style="margin: 0; font-size: 16px; font-weight: bold; color: #dc2626;">
                {{ number_format($stats['total_leave_days']) }} hari
            </p>
        </div>
    @else
        <div class="no-data">
            <p>Tidak ada data cuti/izin untuk periode yang dipilih.</p>
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dihasilkan pada {{ $generated_at->format('d F Y H:i:s') }}</p>
        <p>Sistem Absensi SMKN 1 Punggelan - Generated by Laravel</p>
    </div>
</body>
</html>