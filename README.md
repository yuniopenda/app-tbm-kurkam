# 📚 App TBM Kurkam — Sistem Peminjaman Buku

Aplikasi manajemen perpustakaan berbasis PHP & MySQL untuk mengelola buku, anggota, dan transaksi peminjaman.

---

## 📁 Struktur Folder

```
app-tbm-kurkam/
│
├── config/                     # Konfigurasi aplikasi
│   └── koneksi.php             # Koneksi database MySQL
│
├── database/                   # File SQL & migrasi database
│   └── db_pinjam_buku.sql      # Dump database lengkap (struktur + data)
│
├── includes/                   # Komponen yang dapat digunakan ulang
│   └── sidebar.php             # Sidebar navigasi
│
├── pages/                      # Halaman-halaman utama aplikasi
│   ├── anggota/
│   │   ├── daftar.php          # Daftar seluruh anggota
│   │   ├── tambah.php          # Form tambah anggota baru
│   │   ├── edit.php            # Form edit data anggota
│   │   └── hapus.php           # Proses hapus anggota
│   │
│   ├── buku/
│   │   ├── daftar.php          # Katalog buku
│   │   ├── tambah.php          # Form tambah buku baru
│   │   ├── edit.php            # Form edit data buku
│   │   └── hapus.php           # Proses hapus buku
│   │
│   └── peminjaman/
│       ├── daftar.php          # Daftar transaksi peminjaman
│       ├── tambah.php          # Form tambah pinjaman baru
│       └── kembalikan.php      # Proses pengembalian buku
│
├── index.php                   # Dashboard utama (halaman pertama setelah login)
├── login.php                   # Halaman login
└── logout.php                  # Proses logout
```

---

## 🗄️ Struktur Database

Database: `db_pinjam_buku`

| Tabel           | Deskripsi                              |
|-----------------|----------------------------------------|
| `m_anggota`     | Data master anggota perpustakaan       |
| `m_buku`        | Data master koleksi buku               |
| `m_users`       | Data akun admin/petugas                |
| `t_peminjaman`  | Transaksi peminjaman dan pengembalian  |

---

## 🚀 Cara Instalasi

1. **Copy** folder `app-tbm-kurkam` ke `C:\xampp\htdocs\`
2. **Impor database**: Buka phpMyAdmin → Buat database `db_pinjam_buku` → Import file `database/db_pinjam_buku.sql`
3. **Sesuaikan koneksi** (jika perlu) di `config/koneksi.php`
4. **Buka browser** dan akses: `http://localhost/app-tbm-kurkam/`

---

## ️ Teknologi

- **Backend**: PHP 8+ (Native)
- **Database**: MySQL / MariaDB
- **CSS Framework**: Tailwind CSS (via CDN)
- **Icon**: Font Awesome 6
- **Alert**: SweetAlert2
- **Server**: XAMPP (Apache + MySQL)
