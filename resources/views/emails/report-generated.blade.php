<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Sistem Absensi</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            background-color: #f8fafc;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .header .subtitle {
            color: #6b7280;
            margin: 8px 0 0 0;
            font-size: 14px;
        }

        .content {
            margin-bottom: 30px;
        }

        .report-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .report-info h3 {
            margin: 0 0 15px 0;
            color: #0369a1;
            font-size: 16px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            padding: 4px 0;
            font-weight: 600;
            color: #374151;
            width: 120px;
        }

        .info-value {
            display: table-cell;
            padding: 4px 0;
            color: #6b7280;
        }

        .attachment-notice {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }

        .attachment-notice h4 {
            margin: 0 0 10px 0;
            color: #166534;
            font-size: 14px;
        }

        .attachment-notice p {
            margin: 0;
            color: #166534;
            font-size: 13px;
        }

        .footer {
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }

        .footer p {
            margin: 5px 0;
        }

        .highlight {
            background: #fef3c7;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            color: #92400e;
        }

        .report-type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-attendance {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-leave {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-monthly-summary {
            background: #d1fae5;
            color: #059669;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>📊 Laporan Sistem Absensi</h1>
            <div class="subtitle">SMKN 1 Punggelan</div>
        </div>

        <!-- Content -->
        <div class="content">
            <p>Halo,</p>

            <p>Laporan sistem absensi telah berhasil dihasilkan dan dilampirkan dalam email ini.</p>

            <!-- Report Information -->
            <div class="report-info">
                <h3>📋 Informasi Laporan</h3>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Jenis Laporan:</div>
                        <div class="info-value">
                            <span class="report-type-badge badge-{{ $reportType }}">
                                @switch($reportType)
                                    @case('attendance')
                                        Laporan Absensi
                                        @break
                                    @case('leave')
                                        Laporan Cuti/Izin
                                        @break
                                    @case('monthly-summary')
                                        Ringkasan Bulanan
                                        @break
                                    @default
                                        {{ ucfirst($reportType) }}
                                @endswitch
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Nama File:</div>
                        <div class="info-value"><span class="highlight">{{ $filename }}</span></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Dihasilkan:</div>
                        <div class="info-value">{{ $generatedAt->format('d F Y H:i:s') }}</div>
                    </div>
                    @if(isset($metadata['period']))
                    <div class="info-row">
                        <div class="info-label">Periode:</div>
                        <div class="info-value">{{ $metadata['period'] }}</div>
                    </div>
                    @endif
                    @if(isset($metadata['total_records']))
                    <div class="info-row">
                        <div class="info-label">Total Data:</div>
                        <div class="info-value">{{ number_format($metadata['total_records']) }} record</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Attachment Notice -->
            <div class="attachment-notice">
                <h4>📎 Lampiran</h4>
                <p>Laporan dalam format PDF telah dilampirkan dalam email ini. Silakan unduh dan buka menggunakan aplikasi PDF viewer.</p>
            </div>

            <p>Jika Anda memiliki pertanyaan atau memerlukan laporan tambahan, silakan hubungi administrator sistem.</p>

            <p>Terima kasih,<br>
            <strong>Sistem Absensi SMKN 1 Punggelan</strong></p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Sistem Absensi SMKN 1 Punggelan</strong></p>
            <p>Email ini dikirim secara otomatis oleh sistem</p>
            <p>Jangan membalas email ini</p>
        </div>
    </div>
</body>
</html>