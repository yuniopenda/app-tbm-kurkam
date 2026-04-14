-- ============================================================
-- Migration V3 - TBM Kurung Kambing
-- New Requirements from Meeting
-- ============================================================

-- 1. Tambah kolom yang mungkin terlewat dari V2 ke m_anggota
ALTER TABLE `m_anggota` ADD COLUMN `tanggal_lahir` DATE NULL AFTER `jenis_kelamin`;
ALTER TABLE `m_anggota` ADD COLUMN `nik` VARCHAR(20) NULL AFTER `tanggal_lahir`;
ALTER TABLE `m_anggota` ADD COLUMN `kategori_usia` ENUM('dewasa','remaja','anak-anak') NOT NULL DEFAULT 'dewasa' AFTER `nik`;
ALTER TABLE `m_anggota` ADD COLUMN `password` VARCHAR(255) NULL AFTER `alamat`;
ALTER TABLE `m_anggota` MODIFY COLUMN `kode_anggota` VARCHAR(20) NOT NULL;

-- Tambah kolom umur ke m_anggota (V3)
ALTER TABLE `m_anggota` ADD COLUMN `umur` INT NULL AFTER `tanggal_lahir`;

-- 2. Tambah kolom denda, metode_pembayaran dan status_pembayaran ke t_peminjaman
-- diasumsikan pembayaran terkait denda atau biaya administratif lainnya
ALTER TABLE `t_peminjaman` ADD COLUMN `denda` INT NOT NULL DEFAULT 0 AFTER `tgl_dikembalikan`;
ALTER TABLE `t_peminjaman` ADD COLUMN `metode_pembayaran` ENUM('Tunai', 'Non-tunai') NULL AFTER `denda`;
ALTER TABLE `t_peminjaman` ADD COLUMN `bukti_pembayaran` TEXT NULL AFTER `metode_pembayaran`;

-- 3. Tambah kolom yang mungkin terlewat dari V2 ke m_buku
ALTER TABLE `m_buku` ADD COLUMN `jenis_buku` ENUM('fisik','digital') NOT NULL DEFAULT 'fisik' AFTER `stok`;
ALTER TABLE `m_buku` ADD COLUMN `link_ebook` TEXT NULL AFTER `jenis_buku`;
ALTER TABLE `m_buku` ADD COLUMN `kategori_usia` ENUM('semua','dewasa','remaja','anak-anak') NOT NULL DEFAULT 'semua' AFTER `link_ebook`;

-- 4. Tabel log_hapus_data
CREATE TABLE IF NOT EXISTS `log_hapus_data` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_tabel` varchar(100) NOT NULL,
  `data_id` bigint(20) NOT NULL,
  `data_json` longtext NOT NULL,
  `dihapus_oleh` varchar(100) NOT NULL,
  `dihapus_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Tambah role 'anggota' ke m_users
ALTER TABLE `m_users` MODIFY COLUMN `role` ENUM('admin', 'petugas', 'anggota') DEFAULT 'admin';
