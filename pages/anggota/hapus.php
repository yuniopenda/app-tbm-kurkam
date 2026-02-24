<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: /app-tbm-kurkam/login.php");
    exit;
}
include(__DIR__ . '/../../config/koneksi.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $query_hapus = "DELETE FROM m_anggota WHERE id = '$id'";

    if (mysqli_query($conn, $query_hapus)) {
        header("Location: daftar.php");
        exit;
    } else {
        echo "<script>
                alert('Anggota tidak bisa dihapus karena masih terkait dengan data peminjaman!');
                window.location='daftar.php';
              </script>";
    }
} else {
    header("Location: daftar.php");
    exit;
}
?>
