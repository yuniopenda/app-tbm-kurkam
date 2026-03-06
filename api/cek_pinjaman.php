<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
include(__DIR__ . '/../../config/koneksi.php');

// API: cek jumlah pinjaman aktif seorang anggota
$id = (int)($_GET['id_anggota'] ?? 0);
$r  = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM t_peminjaman WHERE id_anggota='$id' AND status='Dipinjam'"));
header('Content-Type: application/json');
echo json_encode(['total' => (int)$r['total']]);
