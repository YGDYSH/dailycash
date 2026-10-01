# DailyCash

Aplikasi pencatatan keuangan harian sederhana untuk mencatat pemasukan dan pengeluaran pengguna.

## Tujuan Project

Membantu pengguna mencatat dan mengelola keuangan harian dengan mudah, serta menyediakan visualisasi grafik untuk memantau pola pengeluaran dan pemasukan.

## Teknologi yang Digunakan

- **Backend**: PHP Native (tanpa framework)
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Chart**: Chart.js (via CDN)
- **Server**: Laragon
- **Database Name**: `dailycash`

## Struktur Folder

```
dailycash/
│
├── index.php                 # Entry point, redirect ke dashboard
│
├── config/
│   └── database.php          # Konfigurasi koneksi database (PDO)
│
├── auth/
│   ├── login.php             # Halaman login
│   ├── register.php          # Halaman registrasi
│   └── logout.php            # Proses logout
│
├── dashboard/
│   └── index.php             # Halaman dashboard utama
│
├── transactions/
│   ├── index.php             # Daftar transaksi
│   ├── create.php            # Tambah transaksi
│   ├── edit.php              # Edit transaksi
│   └── delete.php            # Hapus transaksi
│
├── assets/
│   ├── css/
│   │   └── style.css         # Stylesheet utama
│   │
│   ├── js/
│   │   └── dashboard.js      # JavaScript untuk dashboard & grafik
│   │
│   └── images/               # Folder gambar (kosong)
│
├── includes/
│   ├── header.php            # Header HTML (head, link CSS, Chart.js CDN)
│   ├── navbar.php            # Navbar atas
│   ├── sidebar.php           # Sidebar navigasi
│   └── footer.php            # Footer & script JS
│
└── README.md                 # Dokumentasi project
```

## Cara Menjalankan Project menggunakan Laragon

1. **Install Laragon** (jika belum): Download dari https://laragon.org
2. **Letakkan project** di folder: `D:\Laragon\www\dailycash`
3. **Start Laragon**: Jalankan Laragon, klik **Start All** (Apache & MySQL)
4. **Buat Database**: Buka HeidiSQL (termasuk di Laragon) atau phpMyAdmin, buat database baru bernama `dailycash`
5. **Konfigurasi Database**: Edit file `config/database.php` sesuai kredensial MySQL Anda:
   ```php
   $host = 'localhost';
   $dbname = 'dailycash';
   $username = 'root';
   $password = ''; // default Laragon MySQL password kosong
   ```
6. **Akses Aplikasi**: Buka browser dan kunjungi `http://dailycash.test` (Laragon auto virtual host) atau `http://localhost/dailycash`

## Catatan Pengembangan

- Project menggunakan **PHP Native** tanpa framework backend/frontend
- Koneksi database menggunakan **PDO**
- Komponen UI reusable diletakkan di folder `includes/`
- Chart.js dimuat via CDN di `includes/header.php`
- File PHP saat ini masih **skeleton** (belum ada logika bisnis lengkap)
- Database `dailycash` harus dibuat manual di MySQL sebelum menjalankan

## Kredensial Database Default (Laragon)

| Parameter | Value |
|-----------|-------|
| Host | localhost |
| Database | dailycash |
| Username | root |
| Password | (kosong) |

*Ubah di `config/database.php` jika konfigurasi MySQL Anda berbeda.*