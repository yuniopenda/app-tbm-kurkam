<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: /app-tbm-kurkam/login.php");
    exit;
}
include(__DIR__ . '/../../config/koneksi.php');

// 1. Ambil ID dari URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // 2. Jalankan query hapus
    $query_hapus = "DELETE FROM m_buku WHERE id = '$id'";

    if (mysqli_query($conn, $query_hapus)) {
        // 3. Set session sukses untuk memicu SweetAlert di daftar.php
        $_SESSION['sukses_hapus'] = true;
        header("Location: daftar.php");
        exit;
    } else {
        // Jika gagal karena ada relasi data (misal buku sedang dipinjam)
        echo "<script>
                alert('Buku tidak bisa dihapus karena masih terkait dengan data peminjaman!');
                window.location='daftar.php';
              </script>";
    }
} else {
    // Jika tidak ada ID, kembalikan ke daftar
    header("Location: daftar.php");
    exit;
}
?>
