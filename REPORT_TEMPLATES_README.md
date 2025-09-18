# Sistem Template Laporan Otomatis

## Overview

Sistem template laporan otomatis memungkinkan administrator untuk membuat, mengelola, dan menjadwalkan pembuatan laporan PDF secara otomatis. Sistem ini terintegrasi dengan Filament admin panel dan mendukung berbagai jenis laporan dengan pengiriman email otomatis.

## Fitur Utama

### 1. Template Laporan
- **Jenis Laporan**: Absensi, Cuti/Izin, Ringkasan Bulanan
- **Filter Dinamis**: Konfigurasi filter berdasarkan user, tanggal, bulan, dll
- **Pengaturan Email**: Multiple recipients dengan nama dan email
- **Jadwal Otomatis**: Manual, Harian, Mingguan, Bulanan

### 2. Generate Laporan
- **Manual Generation**: Generate laporan kapan saja dari admin panel
- **Bulk Generation**: Generate multiple laporan sekaligus
- **Scheduled Generation**: Otomatis berdasarkan jadwal yang ditentukan
- **Email Delivery**: Kirim laporan ke multiple recipients

### 3. Manajemen Template
- **CRUD Operations**: Create, Read, Update, Delete template
- **Export/Import**: Backup dan migrasi template antar sistem
- **Validation**: Validasi input dan konfigurasi jadwal

## Cara Penggunaan

### Membuat Template Laporan

1. **Akses Menu**: Navigasi ke "Laporan & Analitik" > "Template Laporan"
2. **Buat Template Baru**: Klik tombol "Create"
3. **Konfigurasi**:
   - **Nama Template**: Nama yang deskriptif
   - **Jenis Laporan**: Pilih tipe laporan
   - **Deskripsi**: Penjelasan template (opsional)
   - **Filter Laporan**: Tambahkan parameter filter (JSON)
   - **Penerima Email**: Daftar email penerima
   - **Jadwal Otomatis**: Konfigurasi frekuensi dan waktu

### Generate Laporan Manual

1. **Dari List Template**: Klik ikon "Generate" pada baris template
2. **Bulk Generate**: Pilih multiple template dan klik "Generate Laporan Terpilih"
3. **Via Command Line**:
   ```bash
   php artisan app:generate-reports attendance --save
   php artisan app:generate-reports leave --email=user@example.com
   ```

### Scheduled Reports

Template yang memiliki jadwal otomatis akan dijalankan secara otomatis oleh Laravel Scheduler:

```bash
# Jalankan scheduler setiap menit
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Export/Import Template

1. **Export**: Klik "Export Template" untuk download semua template dalam format JSON
2. **Import**: Klik "Import Template" dan upload file JSON yang telah diekspor

## Konfigurasi Jadwal

### Frekuensi
- **Manual**: Generate manual saja
- **Harian**: Setiap hari pada waktu tertentu
- **Mingguan**: Setiap minggu pada hari dan waktu tertentu
- **Bulanan**: Setiap bulan pada tanggal 1 dan waktu tertentu

### Contoh Konfigurasi
```json
{
  "name": "Laporan Absensi Mingguan",
  "type": "attendance",
  "schedule_frequency": "weekly",
  "schedule_day": "monday",
  "schedule_time": "08:00",
  "recipients": [
    {"email": "manager@example.com", "name": "Manager"},
    {"email": "hr@example.com", "name": "HR"}
  ]
}
```

## File yang Dihasilkan

Laporan PDF disimpan di `storage/app/reports/` dengan format:
- Manual: `template-{id}-{timestamp}.pdf`
- Scheduled: `scheduled-{id}-{timestamp}.pdf`

## Troubleshooting

### Laporan Tidak Tergenerate
1. Periksa konfigurasi filter dan parameter
2. Pastikan data tersedia untuk periode yang diminta
3. Cek log Laravel untuk error details

### Email Tidak Terkirim
1. Konfirmasi konfigurasi SMTP di `.env`
2. Periksa validitas alamat email recipients
3. Cek queue worker jika menggunakan queue

### Scheduled Task Tidak Berjalan
1. Pastikan cron job scheduler sudah dikonfigurasi
2. Cek status queue worker
3. Verifikasi timezone server

## API Endpoints

### Generate Laporan via Web
```
GET /reports/attendance/generate?user_id=1&month=2024-01
GET /reports/leave/generate?status=approved&start_date=2024-01-01
GET /reports/monthly-summary/generate/2024/1
```

### Download Laporan
```
GET /reports/download/filename.pdf
```

## Commands

### Generate Reports
```bash
php artisan app:generate-reports {type} [options]
```

**Parameters:**
- `type`: attendance, leave, monthly-summary
- `--user_id`: Filter by user ID
- `--month`: Month filter (YYYY-MM)
- `--email`: Send to specific email
- `--save`: Save to storage

### Generate Scheduled Reports
```bash
php artisan app:generate-scheduled-reports [--dry-run]
```

**Options:**
- `--dry-run`: Show templates that would be generated without actually generating

## Database Schema

### report_templates table
```sql
- id: Primary key
- name: Template name
- type: Report type (attendance, leave, monthly-summary)
- description: Template description
- filters: JSON filters configuration
- recipients: JSON email recipients
- schedule_frequency: manual, daily, weekly, monthly
- schedule_day: Day for weekly schedule
- schedule_time: Time for schedule (HH:MM)
- is_active: Boolean status
- last_generated_at: Timestamp
- created_at, updated_at: Timestamps
```

## Keamanan

- Semua operasi memerlukan autentikasi
- Validasi input pada semua form
- Sanitasi data JSON untuk filters dan recipients
- Log aktivitas untuk audit trail

## Performa

- PDF generation menggunakan DOMPDF untuk kualitas tinggi
- Email dikirim menggunakan queue untuk performa
- Scheduled tasks dioptimalkan untuk menghindari overload
- File storage dengan cleanup otomatis

## Support

Untuk bantuan teknis atau pertanyaan, silakan hubungi tim development.