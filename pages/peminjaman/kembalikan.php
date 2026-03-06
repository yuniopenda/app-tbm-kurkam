<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
include(__DIR__ . '/../../config/koneksi.php');

if (!isset($_GET['id'])) { header("Location: daftar.php"); exit; }
$id_pinjam = (int)$_GET['id'];

// Ambil data peminjaman
$pinjam = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT t.*, m.nama_lengkap, b.judul, b.kode_buku FROM t_peminjaman t
     JOIN m_anggota m ON t.id_anggota = m.id
     JOIN m_buku b ON t.id_buku = b.id
     WHERE t.id = '$id_pinjam' AND t.status = 'Dipinjam'"));

if (!$pinjam) { echo "<script>alert('Data tidak ditemukan atau sudah dikembalikan!'); window.location='daftar.php';</script>"; exit; }

// Hitung denda
$today         = date('Y-m-d');
$tgl_jatuh     = $pinjam['tgl_kembali'];
$selisih_hari  = (int)floor((strtotime($today) - strtotime($tgl_jatuh)) / 86400);
$hari_telat    = max(0, $selisih_hari);
$denda         = $hari_telat * 1000; // Rp 1.000/hari

// Proses pengembalian
$id_buku = $pinjam['id_buku'];
mysqli_begin_transaction($conn);
try {
    mysqli_query($conn, "UPDATE t_peminjaman SET status='Kembali', tgl_dikembalikan='$today', denda='$denda', updated_at=NOW() WHERE id='$id_pinjam'");
    mysqli_query($conn, "UPDATE m_buku SET stok = stok + 1 WHERE id='$id_buku'");
    mysqli_commit($conn);

    $_SESSION['info_kembali'] = [
        'nama'   => $pinjam['nama_lengkap'],
        'judul'  => $pinjam['judul'],
        'denda'  => $denda,
        'telat'  => $hari_telat,
    ];
    header("Location: daftar.php"); exit;

} catch(Exception $e) {
    mysqli_rollback($conn);
    echo "<script>alert('Gagal memproses pengembalian.'); window.location='daftar.php';</script>";
    exit;
}
