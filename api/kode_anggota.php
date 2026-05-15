<?php
session_start();
if (!isset($_SESSION['login'])) { die(json_encode(['error' => 'unauthorized'])); }
include(__DIR__ . '/../config/koneksi.php');

header('Content-Type: application/json');

$kategori = $_GET['kategori'] ?? 'dewasa';
$prefix   = match($kategori) {
    'dewasa'    => 'D',
    'remaja'    => 'R',
    'anak-anak' => 'A',
    default     => 'D',
};
$tahun = date('Y');
$like  = "$prefix-$tahun%";

// Format kode: D-2026001 (prefix-tahunNNN)
// Angka mulai posisi 7: D(1) -(2) 2(3) 0(4) 2(5) 6(6) NNN(7+)
$result = mysqli_query($conn,
    "SELECT MAX(CAST(SUBSTRING(kode_anggota, 7) AS UNSIGNED)) AS n
     FROM m_anggota WHERE kode_anggota LIKE '$like'");
$maxn   = (int)(mysqli_fetch_assoc($result)['n'] ?? 0);
$kode   = "$prefix-$tahun" . sprintf('%03d', $maxn + 1);

echo json_encode(['kode' => $kode]);
