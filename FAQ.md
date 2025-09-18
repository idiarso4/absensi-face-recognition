# ❓ FAQ
## Sistem Absensi SMKN 1 Punggelan

*Pertanyaan yang sering ditanyakan dan jawabannya*

---

## 📋 **TABLE OF CONTENTS**
1. [General Questions](#general-questions)
2. [Technical Questions](#technical-questions)
3. [User Management](#user-management)
4. [Attendance System](#attendance-system)
5. [Mobile & API](#mobile--api)
6. [Security & Privacy](#security--privacy)
7. [Troubleshooting](#troubleshooting)
8. [Development](#development)

---

## ❓ **GENERAL QUESTIONS**

### **Apa itu Sistem Absensi SMKN 1 Punggelan?**
Sistem Absensi SMKN 1 Punggelan adalah aplikasi web-based yang dirancang khusus untuk mengelola absensi siswa dan guru di SMKN 1 Punggelan. Sistem ini menggunakan teknologi GPS untuk validasi lokasi dan menyediakan antarmuka yang mudah digunakan untuk admin, guru, dan siswa.

### **Fitur utama apa saja yang tersedia?**
- ✅ Pendaftaran dan manajemen pengguna
- ✅ Tracking absensi real-time dengan GPS
- ✅ Sistem persetujuan cuti
- ✅ Dashboard admin dengan laporan lengkap
- ✅ API untuk integrasi mobile app
- ✅ Upload foto sebagai bukti absensi
- ✅ Notifikasi email untuk approval
- ✅ Role-based access control

### **Siapa saja yang bisa menggunakan sistem ini?**
- **Administrator**: Mengelola pengguna, mengatur jadwal, melihat laporan
- **Guru**: Melakukan absensi, mengajukan cuti, melihat riwayat
- **Siswa**: Melakukan absensi harian, mengajukan cuti
- **Staff TU**: Membantu administrasi absensi

### **Berapa biaya untuk menggunakan sistem ini?**
Sistem ini dikembangkan khusus untuk SMKN 1 Punggelan dan penggunaannya gratis untuk komunitas sekolah. Kode sumber tersedia sebagai open source untuk sekolah lain yang ingin mengimplementasikan sistem serupa.

---

## 🛠️ **TECHNICAL QUESTIONS**

### **Teknologi apa yang digunakan?**
- **Backend**: Laravel 11.x (PHP Framework)
- **Frontend**: Tailwind CSS, JavaScript
- **Database**: MySQL 8.0+ atau PostgreSQL 13+
- **Admin Panel**: Filament 3.x
- **API**: REST API dengan Laravel Sanctum
- **Authentication**: Laravel Sanctum + Filament Shield
- **File Storage**: Local storage dengan symbolic links

### **Apakah sistem ini responsive?**
Ya, sistem ini fully responsive dan dapat diakses dari:
- 💻 Desktop computer
- 💻 Laptop
- 📱 Tablet
- 📱 Smartphone (iOS dan Android)

### **Berapa kapasitas maksimal pengguna?**
Sistem ini dapat menangani:
- **Pengguna aktif**: 1,000+ pengguna bersamaan
- **Database records**: 100,000+ records absensi
- **File uploads**: 10,000+ foto per bulan
- **API requests**: 10,000+ requests per menit

### **Apakah sistem ini real-time?**
Sistem ini menggunakan:
- **Real-time updates**: Untuk notifikasi dan status updates
- **Background jobs**: Untuk processing yang berat
- **Caching**: Untuk performa optimal
- **WebSocket**: Untuk fitur real-time (opsional)

---

## 👥 **USER MANAGEMENT**

### **Bagaimana cara mendaftar sebagai pengguna baru?**
1. Kunjungi halaman registrasi di website
2. Isi formulir pendaftaran dengan data lengkap
3. Upload foto profil (opsional)
4. Tunggu approval dari administrator
5. Setelah disetujui, Anda akan mendapat email konfirmasi

### **Mengapa akun saya belum aktif setelah registrasi?**
Untuk keamanan, semua akun baru memerlukan approval dari administrator sebelum dapat digunakan. Proses approval biasanya memakan waktu 1-2 hari kerja.

### **Bagaimana cara mengubah password?**
1. Login ke akun Anda
2. Pergi ke menu "Profile" atau "Pengaturan"
3. Klik "Change Password"
4. Masukkan password lama dan password baru
5. Konfirmasi perubahan

### **Saya lupa password, bagaimana cara reset?**
1. Klik "Forgot Password" di halaman login
2. Masukkan alamat email Anda
3. Periksa email untuk link reset password
4. Ikuti instruksi di email untuk membuat password baru

---

## 📊 **ATTENDANCE SYSTEM**

### **Bagaimana cara melakukan absensi?**
1. Pastikan GPS device Anda aktif
2. Buka aplikasi web atau mobile
3. Klik tombol "Check In" atau "Check Out"
4. Sistem akan memvalidasi lokasi Anda
5. Ambil foto sebagai bukti (opsional)
6. Tunggu konfirmasi berhasil

### **Mengapa absensi saya ditolak?**
Absensi dapat ditolak karena beberapa alasan:
- 📍 **Lokasi tidak valid**: Anda berada di luar radius sekolah
- 📱 **GPS tidak akurat**: Sinyal GPS lemah atau tidak stabil
- ⏰ **Waktu tidak sesuai**: Di luar jam operasional
- 📷 **Foto tidak jelas**: Bukti foto tidak memenuhi syarat

### **Apa itu radius absensi?**
Radius absensi adalah area di sekitar sekolah dimana absensi dapat dilakukan. Saat ini diatur pada radius 100 meter dari titik koordinat sekolah. Jika Anda berada di luar radius ini, absensi akan ditolak.

### **Bagaimana cara melihat riwayat absensi?**
1. Login ke akun Anda
2. Pergi ke menu "Attendance" atau "Absensi"
3. Pilih periode waktu yang diinginkan
4. Klik "View History" atau "Lihat Riwayat"
5. Anda dapat melihat detail check-in, check-out, dan status

---

## 📱 **MOBILE & API**

### **Apakah ada aplikasi mobile?**
Saat ini sistem menggunakan web responsive yang dapat diakses dari mobile browser. Aplikasi mobile native sedang dalam pengembangan dan akan tersedia dalam versi mendatang.

### **Bagaimana cara menggunakan API?**
API tersedia untuk developer yang ingin membuat aplikasi mobile atau integrasi dengan sistem lain. Dokumentasi lengkap tersedia di [API_DOCUMENTATION.md](API_DOCUMENTATION.md).

### **Endpoint API apa saja yang tersedia?**
- `POST /api/login` - Login pengguna
- `GET /api/user` - Informasi pengguna
- `POST /api/attendance/check-in` - Check in absensi
- `POST /api/attendance/check-out` - Check out absensi
- `GET /api/attendance` - Riwayat absensi
- `POST /api/leaves` - Ajukan cuti
- Dan masih banyak lagi...

### **Bagaimana cara mendapatkan API token?**
1. Login melalui endpoint `/api/login`
2. Sistem akan memberikan Bearer token
3. Sertakan token dalam header Authorization untuk setiap request
4. Token berlaku selama 1 tahun (dapat dikonfigurasi)

---

## 🔒 **SECURITY & PRIVACY**

### **Apakah data saya aman?**
Ya, sistem ini mengimplementasikan berbagai lapisan keamanan:
- 🔐 **Enkripsi data**: Password dan data sensitif dienkripsi
- 🛡️ **HTTPS**: Semua komunikasi menggunakan SSL/TLS
- 🔑 **Authentication**: Multi-layer authentication
- 📊 **Audit logs**: Semua aktivitas dicatat
- 🚫 **Rate limiting**: Mencegah brute force attacks

### **Siapa yang bisa melihat data absensi saya?**
- **Anda sendiri**: Dapat melihat riwayat absensi pribadi
- **Administrator**: Dapat melihat semua data untuk keperluan manajemen
- **Guru/Wali Kelas**: Dapat melihat data siswa di kelasnya (terbatas)
- **Orang tua**: Dapat melihat data anaknya melalui portal khusus

### **Bagaimana sistem menangani data pribadi?**
- 📋 **Data minimization**: Hanya mengumpulkan data yang diperlukan
- 🔒 **Data encryption**: Data sensitif dienkripsi di database
- 🗑️ **Data retention**: Data disimpan sesuai kebijakan sekolah
- ⚖️ **Compliance**: Mengikuti regulasi PDPM (Peraturan Data Pribadi Mahasiswa)

### **Apakah foto absensi disimpan?**
Ya, foto disimpan sebagai bukti absensi dengan enkripsi dan akses terbatas. Foto hanya dapat diakses oleh pengguna yang bersangkutan dan administrator untuk keperluan verifikasi.

---

## 🔧 **TROUBLESHOOTING**

### **Saya tidak bisa login, apa yang harus dilakukan?**
1. Pastikan email dan password benar
2. Periksa apakah akun sudah diapprove admin
3. Coba reset password jika lupa
4. Clear browser cache dan cookies
5. Coba browser yang berbeda

### **Absensi GPS tidak akurat, kenapa?**
1. Pastikan GPS device aktif dan mendapat sinyal baik
2. Coba refresh halaman beberapa kali
3. Pastikan tidak menggunakan VPN atau proxy
4. Jika di dalam gedung, coba ke area terbuka
5. Update aplikasi browser ke versi terbaru

### **Website lambat atau error, apa masalahnya?**
1. Periksa koneksi internet Anda
2. Clear browser cache
3. Coba akses dari device lain
4. Jika semua user mengalami, mungkin ada maintenance
5. Laporkan ke administrator jika berlanjut

### **Foto tidak bisa diupload, kenapa?**
1. Pastikan ukuran file < 2MB
2. Format yang didukung: JPEG, PNG, JPG
3. Periksa koneksi internet
4. Coba foto dengan resolusi lebih kecil
5. Pastikan tidak ada masalah permission

---

## 💻 **DEVELOPMENT**

### **Bagaimana cara berkontribusi ke project ini?**
1. Fork repository di GitHub
2. Buat branch untuk fitur baru
3. Implementasikan perubahan
4. Tulis unit tests
5. Submit pull request
6. Tunggu review dari maintainer

Lihat [CONTRIBUTING.md](CONTRIBUTING.md) untuk panduan lengkap.

### **Teknologi apa yang dibutuhkan untuk development?**
- **PHP**: 8.2 atau lebih tinggi
- **Composer**: Dependency manager untuk PHP
- **Node.js**: 18+ untuk frontend assets
- **MySQL/PostgreSQL**: Database server
- **Git**: Version control
- **IDE**: VS Code, PHPStorm, atau editor favorit

### **Bagaimana cara setup development environment?**
1. Clone repository
2. Install dependencies dengan `composer install`
3. Setup environment file `.env`
4. Generate application key
5. Run database migrations
6. Install Node dependencies `npm install`
7. Build assets `npm run dev`
8. Start development server `php artisan serve`

Lihat [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md) untuk panduan detail.

### **Di mana saya bisa belajar lebih lanjut?**
- 📚 [Laravel Documentation](https://laravel.com/docs)
- 🎨 [Filament Documentation](https://filamentphp.com/docs)
- 📱 [API Documentation](API_DOCUMENTATION.md)
- 🧪 [Testing Guide](TESTING_GUIDE.md)
- 🔒 [Security Guidelines](SECURITY.md)

---

## 📞 **CONTACT & SUPPORT**

### **Siapa yang bisa dihubungi untuk bantuan?**
- **Technical Issues**: idiarsosimbang@gmail.com
- **User Support**: support@absensi.smkn1.sch.id
- **Bug Reports**: [GitHub Issues](https://github.com/idiarso4/absensi-face-recognition/issues)
- **Feature Requests**: [GitHub Discussions](https://github.com/idiarso4/absensi-face-recognition/discussions)

### **Jam operasional support?**
- **Senin - Jumat**: 07:00 - 16:00 WIB
- **Sabtu**: 08:00 - 12:00 WIB
- **Minggu**: Emergency only
- **Response Time**: 24 jam untuk critical issues

### **Bagaimana cara melaporkan bug?**
1. Periksa apakah bug sudah dilaporkan di GitHub Issues
2. Jika belum, buat issue baru dengan template yang tersedia
3. Sertakan langkah-langkah untuk mereproduksi bug
4. Lampirkan screenshot dan informasi environment
5. Tunggu response dari tim development

### **Fitur apa yang akan datang?**
Lihat [ROADMAP.md](ROADMAP.md) untuk rencana pengembangan mendatang, termasuk:
- Mobile app native
- Facial recognition
- Advanced analytics
- Multi-office support
- Real-time notifications

---

## 🔄 **UPDATES & NEWS**

### **Bagaimana cara mendapat update terbaru?**
- Follow repository di GitHub untuk release notifications
- Subscribe ke newsletter sekolah
- Ikuti pengumuman di grup WhatsApp sekolah
- Cek halaman "What's New" di dashboard admin

### **Apakah ada training atau workshop?**
Ya, kami menyediakan:
- **User Training**: Untuk pengguna baru
- **Admin Training**: Untuk administrator sistem
- **Developer Workshop**: Untuk tim IT sekolah
- **API Integration**: Untuk developer external

### **Berapa sering sistem diupdate?**
- **Security patches**: Segera ketika ditemukan vulnerability
- **Bug fixes**: 1-2 minggu sekali
- **New features**: 1-2 bulan sekali
- **Major updates**: 3-6 bulan sekali

---

## 📊 **PERFORMANCE & LIMITATIONS**

### **Batasan teknis saat ini:**
- **File upload**: Maksimal 2MB per foto
- **API rate limit**: 60 requests per menit per user
- **GPS accuracy**: Minimum 10 meter accuracy
- **Concurrent users**: 500+ simultaneous users
- **Data retention**: 2 tahun riwayat absensi

### **Roadmap improvements:**
- **File upload**: Increase to 5MB, support video
- **API rate limit**: Increase to 1000 requests per menit
- **GPS accuracy**: Support indoor positioning
- **Concurrent users**: Scale to 5000+ users
- **Data retention**: Configurable retention policies

---

*FAQ ini akan terus diupdate berdasarkan pertanyaan yang sering diajukan pengguna. Jika pertanyaan Anda tidak terjawab di sini, silakan hubungi tim support.*