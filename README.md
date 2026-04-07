# SIPRESDIN — Sistem Presensi Dinas

Aplikasi web berbasis **Laravel 8** untuk mengelola presensi/kehadiran siswa secara digital. Sistem ini mendukung dua peran pengguna: **Guru** (admin) dan **Siswa**.

---

## Fitur Utama

### Guru (Admin)
- Manajemen pengguna (tambah, edit, hapus, reset password)
- Melihat & mengelola data kehadiran semua siswa
- Mengubah status kehadiran siswa (Masuk, Alpha, Telat, Sakit, Cuti)
- Ekspor data kehadiran ke file Excel (per siswa maupun semua siswa)
- Pencarian data pengguna dan kehadiran

### Siswa
- Check-in (absen masuk)
- Check-out (absen pulang)
- Melihat daftar hadir pribadi
- Pencarian data kehadiran sendiri

### Umum
- Login / Logout
- Ubah profil
- Ganti password

---

## Teknologi

| Layer       | Teknologi                              |
|-------------|----------------------------------------|
| Backend     | PHP 8+, Laravel 8                      |
| Frontend    | Bootstrap 4, jQuery 3, Vue.js 2        |
| Bundler     | Laravel Mix (Webpack)                  |
| Database    | MySQL / MariaDB                        |
| Export      | Maatwebsite Excel 3.1                  |

---

## Persyaratan Sistem

- PHP >= 8.0
- Composer
- Node.js & NPM
- MySQL / MariaDB
- Web server (Apache/Nginx) atau `php artisan serve`

---

## Instalasi

### 1. Clone repository

```bash
git clone https://github.com/RoviFauzan/sipresdin.git
cd sipresdin
```

### 2. Install dependensi PHP

```bash
composer install
```

### 3. Install dependensi JavaScript

```bash
npm install
npm run dev
```

### 4. Konfigurasi environment

```bash
cp .env.example .env
php artisan key:generate
```

Buka file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensilara
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Import database

Import file SQL yang tersedia ke database MySQL:

```bash
mysql -u root -p absensilara < absensilara.sql
```

Atau gunakan phpMyAdmin untuk mengimpor file `absensilara.sql`.

### 6. Jalankan aplikasi

```bash
php artisan serve
```

Akses aplikasi di: `http://localhost:8000`

---

## Akun Default

| Role  | NRP         | Password    |
|-------|-------------|-------------|
| Guru  | `123456789` | `123456789` |
| Siswa | `987654321` | `987654321` |

---

## Struktur Database

Tabel utama dalam database `absensilara`:

| Tabel      | Keterangan                                         |
|------------|----------------------------------------------------|
| `roles`    | Peran pengguna: `Guru` dan `Siswa`                 |
| `users`    | Data pengguna (nama, NRP, foto, password, role)    |
| `presents` | Data kehadiran (tanggal, jam masuk, jam keluar, keterangan) |

Status kehadiran yang tersedia: **Masuk**, **Alpha**, **Telat**, **Sakit**, **Cuti**.

---

## Struktur Direktori

```
sipresdin/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php       # Login & logout
│   │   │   ├── HomeController.php       # Dashboard
│   │   │   ├── PresentsController.php   # Manajemen kehadiran
│   │   │   └── UsersController.php      # Manajemen pengguna
│   │   └── Middleware/
│   ├── Exports/                         # Kelas ekspor Excel
│   ├── Present.php                      # Model kehadiran
│   ├── Role.php                         # Model role
│   └── User.php                         # Model pengguna
├── database/
│   └── migrations/
├── resources/
│   └── views/
├── routes/
│   └── web.php
└── absensilara.sql                      # Dump database
```

---

## Lisensi

Project ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).
