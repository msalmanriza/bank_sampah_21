# Bank Sampah Manyar21 - Admin Dashboard

Dashboard admin lengkap untuk mengelola sistem Bank Sampah Manyar21 menggunakan PHP dan MySQL.

## 🚀 Fitur Utama

### ✅ Autentikasi Admin
- Login admin dengan session management
- Proteksi halaman admin dengan session check
- Logout aman dengan penghancuran session

### ✅ Dashboard Utama
- Ringkasan statistik:
  - Total nasabah
  - Total transaksi
  - Total berat sampah masuk
  - Total poin yang diberikan
- Tampilan transaksi terbaru

### ✅ Manajemen Nasabah
- **Create**: Tambah nasabah baru
- **Read**: Tampilkan daftar nasabah
- **Update**: Edit data nasabah
- **Delete**: Hapus nasabah (dengan validasi transaksi)

### ✅ Manajemen Jenis Sampah
- **Create**: Tambah jenis sampah baru dengan harga per kg
- **Read**: Tampilkan daftar jenis sampah
- **Update**: Edit jenis sampah dan harga
- **Delete**: Hapus jenis sampah (dengan validasi transaksi)

### ✅ Riwayat Transaksi
- Tampilkan semua transaksi dengan detail lengkap
- Filter berdasarkan status, tanggal, dan pencarian
- Update status transaksi (pending → selesai/ditolak)
- Statistik transaksi

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Icons**: Font Awesome 6
- **Connection**: PDO (PHP Data Objects)

## 📋 Struktur Database

```sql
-- Tabel Users
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'nasabah') DEFAULT 'nasabah',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Sampah
CREATE TABLE sampah (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jenis VARCHAR(100) UNIQUE NOT NULL,
    harga_per_kg DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Transaksi
CREATE TABLE transaksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    jenis_sampah INT NOT NULL,
    berat DECIMAL(8,2) NOT NULL,
    poin INT NOT NULL,
    status ENUM('pending', 'selesai', 'ditolak') DEFAULT 'pending',
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (jenis_sampah) REFERENCES sampah(id)
);
```

## 🚀 Instalasi dan Setup

### 1. Persiapan Database
```bash
# Import file database_setup.sql ke MySQL
mysql -u root -p < database_setup.sql
```

### 2. Konfigurasi Database
Edit file `config.php` sesuai dengan setting database Anda:
```php
$host = 'localhost';
$dbname = 'bank_sampah';
$username = 'root';
$password = 'your_password';
```

### 3. Jalankan Server
```bash
# Menggunakan PHP built-in server
php -S localhost:8000

# Atau menggunakan Apache/Nginx
```

### 4. Akses Dashboard
- **Login Admin**: http://localhost:8000/login.php
- **Default Admin Account**:
  - Email: admin@banksampah.com
  - Password: admin123

## 📁 Struktur File

```
bank-sampah-admin/
├── config.php              # Konfigurasi database
├── login.php               # Halaman login admin
├── dashboard.php           # Dashboard utama
├── nasabah.php             # Manajemen nasabah
├── jenis-sampah.php        # Manajemen jenis sampah
├── transaksi.php           # Riwayat transaksi
├── logout.php              # Logout handler
├── database_setup.sql      # Script setup database
└── README.md              # Dokumentasi
```

## 🔒 Keamanan

- **Password Hashing**: Menggunakan `password_hash()` dan `password_verify()`
- **Prepared Statements**: Mencegah SQL injection
- **Session Management**: Proteksi akses halaman admin
- **Input Validation**: Validasi data input
- **CSRF Protection**: Token-based protection (recommended for production)

## 🎨 Fitur UI/UX

- **Responsive Design**: Menggunakan Bootstrap 5
- **Modern Interface**: Gradient backgrounds dan smooth animations
- **Interactive Modals**: Untuk form CRUD operations
- **Real-time Updates**: Statistik dashboard terupdate
- **Filter & Search**: Pencarian dan filter data

## 📊 Dashboard Features

### Statistik Real-time
- Total nasabah terdaftar
- Jumlah transaksi keseluruhan
- Berat sampah yang berhasil dikumpulkan
- Poin yang telah didistribusikan

### Manajemen Data
- **Nasabah**: Tambah, edit, hapus, dan lihat detail
- **Jenis Sampah**: Kelola kategori dan harga per kg
- **Transaksi**: Monitor dan update status transaksi

## 🔧 Development Notes

### Password Default Admin
- **Email**: admin@banksampah.com
- **Password**: admin123
- **Hashed Password**: `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`

### Sample Data
Database sudah dilengkapi dengan data sample:
- 1 Admin user
- 3 Sample nasabah
- 6 Jenis sampah dengan harga
- 5 Sample transaksi

## 🚀 Production Deployment

Untuk deployment production:
1. Gunakan HTTPS
2. Konfigurasi proper error handling
3. Implementasi logging
4. Backup database otomatis
5. Security hardening (disable error reporting, etc.)

## 📞 Support

Untuk pertanyaan atau masalah, silakan buat issue di repository ini.

---

**Bank Sampah Manyar21 - Admin Dashboard**
Dibuat dengan ❤️ untuk kemajuan lingkungan
