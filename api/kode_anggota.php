<?php
session_start();
if (!isset($_SESSION['login'])) { die(json_encode(['error' => 'unauthorized'])); }
include(__DIR__ . '/../config/koneksi.php');

header('Content-Type: application/json');

$kategori  = $_GET['kategori'] ?? 'dewasa'; // dewasa, remaja, anak-anak
$tahun     = date('Y');

// Prefix sesuai kategori
$prefix = match($kategori) {
    'dewasa'    => 'D',
    'remaja'    => 'R',
    'anak-anak' => 'A',
    default     => 'D',
};

// Cari nomor terakhir untuk prefix tahun ini
$result = mysqli_query($conn,
    "SELECT kode_anggota FROM m_anggota
     WHERE kode_anggota LIKE '{$prefix}-{$tahun}%'
     ORDER BY kode_anggota DESC LIMIT 1");

$urutan = 1;
if ($row = mysqli_fetch_assoc($result)) {
    // Ambil 3 digit terakhir dari kode, lalu increment
    $parts = explode('-', $row['kode_anggota']); // [D, 2026, 001]
    if (isset($parts[2])) {
        $urutan = (int)$parts[2] + 1;
    } else {
        // Format lama tanpa separator ganda: D-2026001
        $numPart = substr($row['kode_anggota'], strlen("{$prefix}-{$tahun}"));
        $urutan  = (int)$numPart + 1;
    }
}

$kode = $prefix . '-' . $tahun . '-' . sprintf('%03d', $urutan);

echo json_encode(['kode' => $kode]);
