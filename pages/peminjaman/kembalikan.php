<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: /app-tbm-kurkam/login.php");
    exit;
}
include(__DIR__ . '/../../config/koneksi.php');

// Pastikan ada ID yang dikirim melalui URL
if (isset($_GET['id'])) {
    $id_pinjam = $_GET['id'];
    $denda     = isset($_GET['denda']) ? $_GET['denda'] : 0;
    $tgl_kembali_asli = date('Y-m-d');

    // 1. Ambil ID Buku dari transaksi ini untuk mengupdate stok nanti
    $cek_pinjam = mysqli_query($conn, "SELECT id_buku FROM t_peminjaman WHERE id = '$id_pinjam'");
    $data_pinjam = mysqli_fetch_assoc($cek_pinjam);
    $id_buku = $data_pinjam['id_buku'];

    // Mulai Transaksi Database
    mysqli_begin_transaction($conn);

    try {
        // 2. Update status peminjaman
        $update_status = mysqli_query($conn, "UPDATE t_peminjaman SET 
            status = 'Kembali', 
            updated_at = NOW() 
            WHERE id = '$id_pinjam'");

        // 3. Tambah stok buku (+1)
        $update_stok = mysqli_query($conn, "UPDATE m_buku SET 
            stok = stok + 1 
            WHERE id = '$id_buku'");

        if ($update_status && $update_stok) {
            mysqli_commit($conn);
            echo "<script>
                    alert('Buku berhasil dikembalikan!');
                    window.location='daftar.php';
                  </script>";
        } else {
            throw new Exception("Gagal mengupdate data.");
        }

    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>
                alert('Terjadi kesalahan sistem.');
                window.location='daftar.php';
              </script>";
    }

} else {
    header("Location: daftar.php");
    exit;
}
?>
