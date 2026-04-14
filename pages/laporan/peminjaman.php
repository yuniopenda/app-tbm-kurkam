<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
include(__DIR__ . '/../../config/koneksi.php');

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$stats = getMonthlyStats($conn, $bulan, $tahun);

$nama_bulan = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peminjaman - TBM Kurung Kambing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50 font-sans flex min-h-screen">
<?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

<main class="flex-grow lg:ml-64 p-4 lg:p-8 pt-16 lg:pt-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">LAPORAN BULANAN</h1>
                <p class="text-slate-500 font-medium">Statistik peminjaman, anggota, dan koleksi buku.</p>
            </div>
            
            <form action="" method="GET" class="flex items-center gap-2 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
                <select name="bulan" class="bg-transparent border-none outline-none font-bold text-slate-700 px-3">
                    <?php foreach($nama_bulan as $m => $n): ?>
                        <option value="<?= $m ?>" <?= $bulan == $m ? 'selected' : '' ?>><?= $n ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="tahun" class="bg-transparent border-none outline-none font-bold text-slate-700 px-3">
                    <?php for($i=date('Y'); $i>=2024; $i--): ?>
                        <option value="<?= $i ?>" <?= $tahun == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="bg-indigo-600 text-white p-2 px-4 rounded-xl font-bold hover:bg-indigo-700 transition">
                    Filter
                </button>
            </form>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-5">
                <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner"><i class="fas fa-exchange-alt"></i></div>
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Total Peminjaman</p>
                    <h3 class="text-2xl font-black text-slate-800"><?= $stats['total_peminjaman'] ?></h3>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-5">
                <div class="w-14 h-14 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner"><i class="fas fa-clock"></i></div>
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Telat Kembali</p>
                    <h3 class="text-2xl font-black text-slate-800"><?= $stats['total_telat'] ?></h3>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-5">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner"><i class="fas fa-book-medical"></i></div>
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Buku Masuk</p>
                    <h3 class="text-2xl font-black text-slate-800"><?= $stats['buku_masuk'] ?></h3>
                </div>
            </div>
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-5">
                <div class="w-14 h-14 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-2xl shadow-inner"><i class="fas fa-user-plus"></i></div>
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Anggota Baru</p>
                    <h3 class="text-2xl font-black text-slate-800"><?= $stats['anggota_baru'] ?></h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Chart Container -->
            <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-xl font-black text-slate-800">RINGKASAN GRAFIK</h2>
                    <div class="flex gap-2">
                        <span class="flex items-center gap-2 text-xs font-bold text-slate-500"><span class="w-3 h-3 bg-indigo-500 rounded-full"></span> Data Bulan Ini</span>
                    </div>
                </div>
                <div class="h-[350px]">
                    <canvas id="loanChart"></canvas>
                </div>
            </div>

            <!-- Export Section -->
            <div class="bg-indigo-600 p-8 rounded-[2.5rem] shadow-xl shadow-indigo-100 text-white flex flex-col justify-between">
                <div>
                    <h2 class="text-2xl font-black mb-2 uppercase tracking-tighter">Download Laporan</h2>
                    <p class="text-indigo-100 text-sm mb-8 leading-relaxed">Dapatkan data lengkap dalam format Excel untuk keperluan dokumentasi dan analisis offline.</p>
                </div>
                <div class="space-y-3">
                    <a href="export_excel.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="flex items-center justify-center gap-3 w-full bg-white text-indigo-600 py-4 rounded-2xl font-black hover:bg-slate-50 transition active:scale-95 shadow-lg">
                        <i class="fas fa-file-excel text-xl"></i> Export ke Excel
                    </a>
                    <button onclick="window.print()" class="flex items-center justify-center gap-3 w-full bg-indigo-500 text-white py-4 rounded-2xl font-black hover:bg-indigo-400 transition active:scale-95 border border-indigo-400">
                        <i class="fas fa-print"></i> Cetak PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- Table View -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-8 border-b border-slate-50 flex items-center justify-between">
                <h2 class="text-xl font-black text-slate-800">DETAIL PEMINJAMAN</h2>
                <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Bulan <?= $nama_bulan[$bulan] ?> <?= $tahun ?></div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">ID</th>
                            <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Anggota</th>
                            <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Buku</th>
                            <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Tgl Pinjam</th>
                            <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Status</th>
                            <th class="px-8 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Pembayaran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php
                        $q = mysqli_query($conn, "SELECT t.*, a.nama_lengkap, b.judul 
                                                 FROM t_peminjaman t
                                                 JOIN m_anggota a ON t.id_anggota = a.id
                                                 JOIN m_buku b ON t.id_buku = b.id
                                                 WHERE MONTH(t.tgl_pinjam) = '$bulan' AND YEAR(t.tgl_pinjam) = '$tahun'
                                                 ORDER BY t.tgl_pinjam DESC");
                        if(mysqli_num_rows($q) > 0):
                            while($row = mysqli_fetch_assoc($q)):
                                $status_color = $row['status'] == 'Kembali' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                                $pembayaran = $row['metode_pembayaran'] ? $row['metode_pembayaran'] : '-';
                        ?>
                        <tr class="hover:bg-slate-50/50 transition duration-200">
                            <td class="px-8 py-5 font-bold text-slate-400">#<?= $row['id'] ?></td>
                            <td class="px-8 py-5">
                                <div class="font-black text-slate-700"><?= $row['nama_lengkap'] ?></div>
                            </td>
                            <td class="px-8 py-5 font-bold text-slate-600"><?= $row['judul'] ?></td>
                            <td class="px-8 py-5 font-bold text-slate-500"><?= date('d M Y', strtotime($row['tgl_pinjam'])) ?></td>
                            <td class="px-8 py-5">
                                <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest <?= $status_color ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td class="px-8 py-5">
                                <span class="text-xs font-bold <?= $row['metode_pembayaran'] == 'Non-tunai' ? 'text-indigo-600' : 'text-slate-600' ?>">
                                    <?= $pembayaran ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="6" class="px-8 py-10 text-center text-slate-400 font-bold italic">Tidak ada data peminjaman di bulan ini.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
    const ctx = document.getElementById('loanChart').getContext('2d');
    
    // Gradient effect
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.4)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Peminjaman', 'Lama/Telat', 'Buku Masuk', 'Anggota Baru'],
            datasets: [{
                label: 'Jumlah',
                data: [
                    <?= $stats['total_peminjaman'] ?>, 
                    <?= $stats['total_telat'] ?>, 
                    <?= $stats['buku_masuk'] ?>, 
                    <?= $stats['anggota_baru'] ?>
                ],
                backgroundColor: [
                    'rgba(79, 70, 229, 0.8)',
                    'rgba(244, 63, 94, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)'
                ],
                borderColor: 'transparent',
                borderWidth: 0,
                borderRadius: 12,
                barThickness: 50
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9', drawBorder: false },
                    ticks: { color: '#94a3b8', font: { weight: 'bold' } }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: '#64748b', font: { weight: 'black' } }
                }
            }
        }
    });
</script>
</body>
</html>
