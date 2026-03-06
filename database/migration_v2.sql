-- ============================================================
-- Migration V2 - TBM Kurung Kambing
-- Kompatibel dengan MariaDB 10.4 (XAMPP)
-- Jalankan di phpMyAdmin > database: db_pinjam_buku
-- Jalankan satu per satu jika ada error "duplicate column"
-- ============================================================

-- ==== 1. Tabel m_anggota ====
-- Tambah tanggal_lahir
ALTER TABLE `m_anggota` ADD COLUMN `tanggal_lahir` DATE NULL AFTER `jenis_kelamin`;

-- Tambah NIK
ALTER TABLE `m_anggota` ADD COLUMN `nik` VARCHAR(20) NULL AFTER `tanggal_lahir`;

-- Tambah kategori_usia
ALTER TABLE `m_anggota` ADD COLUMN `kategori_usia` ENUM('dewasa','remaja','anak-anak') NOT NULL DEFAULT 'dewasa' AFTER `nik`;

-- Tambah password anggota (null = kode_anggota sebagai password default)
ALTER TABLE `m_anggota` ADD COLUMN `password` VARCHAR(255) NULL AFTER `alamat`;

-- Perlebar kode_anggota untuk format baru (D-2026001)
ALTER TABLE `m_anggota` MODIFY COLUMN `kode_anggota` VARCHAR(20) NOT NULL;

-- ==== 2. Tabel m_buku ====
-- Tambah jenis_buku
ALTER TABLE `m_buku` ADD COLUMN `jenis_buku` ENUM('fisik','digital') NOT NULL DEFAULT 'fisik' AFTER `stok`;

-- Tambah link_ebook
ALTER TABLE `m_buku` ADD COLUMN `link_ebook` TEXT NULL AFTER `jenis_buku`;

-- Tambah kategori_usia untuk buku
ALTER TABLE `m_buku` ADD COLUMN `kategori_usia` ENUM('semua','dewasa','remaja','anak-anak') NOT NULL DEFAULT 'semua' AFTER `link_ebook`;

-- ==== 3. Tabel t_peminjaman ====
-- Tambah kolom denda
ALTER TABLE `t_peminjaman` ADD COLUMN `denda` INT NOT NULL DEFAULT 0 AFTER `tgl_dikembalikan`;

-- ==== 4. Update data lama: set kategori_usia default untuk anggota lama ====
UPDATE `m_anggota` SET `kategori_usia` = 'dewasa' WHERE `kategori_usia` IS NULL OR `kategori_usia` = '';

-- ==== 5. OPSIONAL: Format ulang kode_anggota lama ke format baru ====
-- UPDATE `m_anggota` SET `kode_anggota` = CONCAT('D-2026', LPAD(id, 3, '0')) WHERE `kode_anggota` NOT LIKE 'D-%' AND `kode_anggota` NOT LIKE 'R-%' AND `kode_anggota` NOT LIKE 'A-%';

-- ==== Verifikasi ====
-- DESCRIBE m_anggota;
-- DESCRIBE m_buku;
-- DESCRIBE t_peminjaman;
