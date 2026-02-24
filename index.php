<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include(__DIR__ . '/config/koneksi.php');

// 1. STATISTIK UTAMA
$total_buku = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM m_buku"))['total'];
$total_anggota = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM m_anggota"))['total'];
$total_dipinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_peminjaman WHERE status = 'Dipinjam'"))['total'];

// 2. AMBIL 5 TRANSAKSI TERBARU (JOIN untuk ambil nama & judul)
$query_terbaru = "SELECT t_peminjaman.*, m_anggota.nama_lengkap, m_buku.judul 
                  FROM t_peminjaman 
                  JOIN m_anggota ON t_peminjaman.id_anggota = m_anggota.id 
                  JOIN m_buku ON t_peminjaman.id_buku = m_buku.id 
                  ORDER BY t_peminjaman.id DESC LIMIT 5";
$peminjaman_terbaru = mysqli_query($conn, $query_terbaru);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - LibraTech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans flex min-h-screen">

    <?php include(__DIR__ . '/includes/sidebar.php'); ?>

    <main class="flex-grow ml-64 p-8">
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Ringkasan Sistem 👋</h2>
                <p class="text-gray-500">Pantau aktivitas perpustakaan dalam satu layar.</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-gray-400"><?= date('l, d F Y'); ?></p>
                <p class="text-indigo-600 font-bold" id="clock"></p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 transition hover:shadow-md">
                <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Koleksi Buku</p>
                    <h3 class="text-3xl font-black text-gray-800"><?= $total_buku; ?></h3>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 transition hover:shadow-md">
                <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Anggota</p>
                    <h3 class="text-3xl font-black text-gray-800"><?= $total_anggota; ?></h3>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex items-center gap-5 transition hover:shadow-md">
                <div class="w-14 h-14 bg-orange-100 text-orange-600 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Buku Keluar</p>
                    <h3 class="text-3xl font-black text-gray-800"><?= $total_dipinjam; ?></h3>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                <h4 class="font-bold text-gray-800">5 Peminjaman Terakhir</h4>
                <a href="pages/peminjaman/daftar.php" class="text-xs font-bold text-indigo-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase">
                        <tr>
                            <th class="px-6 py-3">Peminjam</th>
                            <th class="px-6 py-3">Judul</th>
                            <th class="px-6 py-3">Tgl Pinjam</th>
                            <th class="px-6 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-50">
                        <?php while($row = mysqli_fetch_assoc($peminjaman_terbaru)) : ?>
                        <tr class="hover:bg-gray-50/30 transition">
                            <td class="px-6 py-4 font-bold text-gray-700"><?= $row['nama_lengkap']; ?></td>
                            <td class="px-6 py-4 italic text-gray-500">"<?= $row['judul']; ?>"</td>
                            <td class="px-6 py-4 text-xs font-mono"><?= date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                            <td class="px-6 py-4 text-center">
                                <?php if($row['status'] == 'Dipinjam'): ?>
                                    <span class="text-[9px] bg-orange-50 text-orange-500 px-2 py-1 rounded-md font-bold uppercase">Dipinjam</span>
                                <?php else: ?>
                                    <span class="text-[9px] bg-green-50 text-green-500 px-2 py-1 rounded-md font-bold uppercase">Kembali</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Script untuk Jam Real-time
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            document.getElementById('clock').textContent = timeString + " WIB";
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>