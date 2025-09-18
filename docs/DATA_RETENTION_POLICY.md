# Data Retention Policies

## Overview
Sistem absensi SMKN 1 Punggelan mengimplementasikan kebijakan retensi data untuk mengelola penyimpanan data secara efisien dan mematuhi regulasi privasi data.

## Retention Policies

### 1. Attendance Records (Data Absensi)
- **Retention Period**: 2 tahun
- **Reason**: Data absensi diperlukan untuk pelaporan bulanan, penggajian, dan audit kepatuhan
- **Cleanup Schedule**: Mingguan (hari Minggu pukul 03:00)
- **Implementation**: Soft delete dengan cleanup otomatis

### 2. Leave Records (Data Cuti/Izin)
- **Retention Period**: 3 tahun
- **Reason**: Data cuti diperlukan untuk audit jangka panjang dan pelaporan SDM
- **Cleanup Schedule**: Mingguan (hari Minggu pukul 03:00)
- **Implementation**: Soft delete dengan cleanup otomatis

### 3. User Sessions (Sesi Pengguna)
- **Retention Period**: Sesuai konfigurasi session lifetime (default: 2 jam)
- **Reason**: Membersihkan data sesi yang tidak aktif untuk performa aplikasi
- **Cleanup Schedule**: Mingguan (hari Minggu pukul 03:00)
- **Implementation**: Hard delete otomatis

### 4. Application Logs (Log Aplikasi)
- **Retention Period**: 6 bulan
- **Reason**: Log diperlukan untuk debugging dan audit keamanan
- **Cleanup Schedule**: Mingguan (hari Minggu pukul 03:00)
- **Implementation**: File deletion otomatis

## Technical Implementation

### Command Usage
```bash
# Jalankan cleanup data
php artisan app:cleanup-old-data

# Dry run (lihat apa yang akan dihapus tanpa menghapus)
php artisan app:cleanup-old-data --dry-run
```

### Scheduled Tasks
Data cleanup dijadwalkan otomatis setiap hari Minggu pukul 03:00 melalui Laravel Scheduler.

### Monitoring
- Cleanup activities dicatat dalam application logs
- Email notifications dikirim jika cleanup gagal
- Dashboard admin menampilkan statistik cleanup terakhir

## Security Considerations

### Data Privacy
- Semua data dihapus secara permanen sesuai retention policy
- Tidak ada recovery untuk data yang sudah dihapus
- Backup dilakukan sebelum cleanup

### Audit Trail
- Semua aktivitas cleanup dicatat dalam audit logs
- Admin dapat melihat history cleanup melalui admin panel

## Backup Strategy

### Automated Backups
- Database backup harian pukul 02:00
- Backup disimpan selama 30 hari
- Backup mingguan disimpan selama 12 minggu
- Backup bulanan disimpan selama 12 bulan
- Backup tahunan disimpan selama 3 tahun

### Manual Backups
Admin dapat membuat backup manual kapan saja melalui admin panel.

## Compliance

### Regulasi Terkait
- Undang-Undang Ketenagakerjaan
- Peraturan Pemerintah tentang Pengelolaan Data Pribadi
- Standar ISO 27001 untuk Information Security Management

### Data Classification
- **Public**: Tidak ada data public yang disimpan
- **Internal**: Data absensi dan cuti (2-3 tahun retention)
- **Confidential**: Data pribadi karyawan (sesuai retention policy)

## Emergency Procedures

### Data Recovery
1. Restore dari backup terakhir
2. Verifikasi integritas data
3. Update audit logs
4. Notify stakeholders

### Incident Response
1. Hentikan cleanup process
2. Assess data loss impact
3. Execute recovery plan
4. Document incident dan lessons learned

## Maintenance

### Regular Reviews
- Review retention policies setiap 6 bulan
- Update berdasarkan perubahan regulasi
- Adjust retention periods jika diperlukan

### Performance Monitoring
- Monitor storage usage
- Track cleanup execution time
- Alert jika cleanup gagal

---

*Dokumen ini diperbarui pada: 18 September 2025*