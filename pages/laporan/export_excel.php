<?php
session_start();
if (!isset($_SESSION['login'])) { exit; }
include(__DIR__ . '/../../config/koneksi.php');

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

$filename = "Laporan_TBM_Kurkam_" . $bulan . "_" . $tahun . ".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

$stats = getMonthlyStats($conn, $bulan, $tahun);
?>

<table border="1">
    <tr>
        <th colspan="6" style="font-size: 16px; font-weight: bold;">LAPORAN BULANAN TBM KURUNG KAMBING</th>
    </tr>
    <tr>
        <th colspan="6">Bulan: <?= $nama_bulan[$bulan] ?> <?= $tahun ?></th>
    </tr>
    <tr><td colspan="6"></td></tr>
    <tr>
        <th colspan="3">RINGKASAN STATISTIK</th>
        <th colspan="3"></th>
    </tr>
    <tr>
        <td>Total Peminjaman</td>
        <td><?= $stats['total_peminjaman'] ?></td>
        <td></td>
        <td>Buku Masuk</td>
        <td><?= $stats['buku_masuk'] ?></td>
        <td></td>
    </tr>
    <tr>
        <td>Telat Kembali</td>
        <td><?= $stats['total_telat'] ?></td>
        <td></td>
        <td>Anggota Baru</td>
        <td><?= $stats['anggota_baru'] ?></td>
        <td></td>
    </tr>
    <tr><td colspan="6"></td></tr>
    <tr>
        <th bgcolor="#eeeeee">No</th>
        <th bgcolor="#eeeeee">ID Transaksi</th>
        <th bgcolor="#eeeeee">Nama Anggota</th>
        <th bgcolor="#eeeeee">Judul Buku</th>
        <th bgcolor="#eeeeee">Tgl Pinjam</th>
        <th bgcolor="#eeeeee">Status</th>
    </tr>
    <?php
    $q = mysqli_query($conn, "SELECT t.*, a.nama_lengkap, b.judul 
                             FROM t_peminjaman t
                             JOIN m_anggota a ON t.id_anggota = a.id
                             JOIN m_buku b ON t.id_buku = b.id
                             WHERE MONTH(t.tgl_pinjam) = '$bulan' AND YEAR(t.tgl_pinjam) = '$tahun'
                             ORDER BY t.tgl_pinjam DESC");
    $no = 1;
    while($row = mysqli_fetch_assoc($q)):
    ?>
    <tr>
        <td><?= $no++ ?></td>
        <td>#<?= $row['id'] ?></td>
        <td><?= $row['nama_lengkap'] ?></td>
        <td><?= $row['judul'] ?></td>
        <td><?= $row['tgl_pinjam'] ?></td>
        <td><?= $row['status'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
