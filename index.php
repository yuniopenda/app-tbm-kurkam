<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php"); exit;
}
// Redirect anggota ke halaman katalog
if (isset($_SESSION['role']) && $_SESSION['role'] === 'anggota') {
    header("Location: pages/user/katalog.php"); exit;
}
include(__DIR__ . '/config/koneksi.php');

$today = date('Y-m-d');

// Statistik utama
$total_buku     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM m_buku"))['total'];
$total_anggota  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM m_anggota"))['total'];
$total_dipinjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_peminjaman WHERE status = 'Dipinjam'"))['total'];
$total_overdue  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM t_peminjaman WHERE status = 'Dipinjam' AND tgl_kembali < '$today'"))['total'];

// 5 peminjaman terbaru
$peminjaman_terbaru = mysqli_query($conn,
    "SELECT t.*, a.nama_lengkap, b.judul, b.kode_buku
     FROM t_peminjaman t
     JOIN m_anggota a ON t.id_anggota = a.id
     JOIN m_buku b ON t.id_buku = b.id
     ORDER BY t.id DESC LIMIT 5");

// ── DATA CHART ──────────────────────────────────────────────────────────────

// 1. Tren peminjaman 6 bulan terakhir
$chart_labels = [];
$chart_data   = [];
for ($i = 5; $i >= 0; $i--) {
    $bulan      = date('Y-m', strtotime("-$i months"));
    $nama_bulan = date('M Y', strtotime("-$i months"));
    $chart_labels[] = $nama_bulan;
    $res = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) as total FROM t_peminjaman
         WHERE DATE_FORMAT(tgl_pinjam, '%Y-%m') = '$bulan'"));
    $chart_data[] = (int)$res['total'];
}

// 2. Status buku: Dipinjam vs Tersedia
$buku_tersedia = (int)$total_buku - (int)$total_dipinjam;

// 3. Top 5 buku paling banyak dipinjam
$top_buku_res = mysqli_query($conn,
    "SELECT b.judul, COUNT(t.id) as total
     FROM t_peminjaman t
     JOIN m_buku b ON t.id_buku = b.id
     GROUP BY t.id_buku
     ORDER BY total DESC
     LIMIT 5");
$top_buku_labels = [];
$top_buku_data   = [];
while ($row = mysqli_fetch_assoc($top_buku_res)) {
    // Potong judul panjang agar chart rapi
    $top_buku_labels[] = mb_strlen($row['judul']) > 25
        ? mb_substr($row['judul'], 0, 25) . '…'
        : $row['judul'];
    $top_buku_data[] = (int)$row['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TBM Kurung Kambing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">

    <?php include(__DIR__ . '/includes/sidebar.php'); ?>

    <main class="flex-grow lg:ml-64 p-4 lg:p-8 pt-16 lg:pt-8">
        <!-- Header -->
        <div class="mb-6 lg:mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-2xl lg:text-3xl font-black text-slate-800">Ringkasan Sistem 👋</h2>
                <p class="text-slate-500 mt-1 text-sm lg:text-base">Pantau aktivitas perpustakaan dalam satu layar.</p>
            </div>
            <div class="text-right hidden sm:block">
                <p class="text-sm font-bold text-slate-400"><?= date('l, d F Y'); ?></p>
                <p class="text-indigo-600 font-black text-lg" id="clock"></p>
            </div>
        </div>

        <!-- Kartu Statistik -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-5 mb-6 lg:mb-8">
            <!-- Koleksi Buku -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-lg transition">
                <div class="w-14 h-14 bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Koleksi Buku</p>
                    <h3 class="text-3xl font-black text-slate-800"><?= $total_buku ?></h3>
                </div>
            </div>

            <!-- Total Anggota -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-lg transition">
                <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Anggota</p>
                    <h3 class="text-3xl font-black text-slate-800"><?= $total_anggota ?></h3>
                </div>
            </div>

            <!-- Buku Dipinjam -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex items-center gap-4 hover:shadow-lg transition">
                <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dipinjam</p>
                    <h3 class="text-3xl font-black text-slate-800"><?= $total_dipinjam ?></h3>
                </div>
            </div>

            <!-- Keterlambatan -->
            <a href="pages/peminjaman/daftar.php?status=Dipinjam" class="block">
                <div class="bg-white p-6 rounded-3xl border-2 flex items-center gap-4 hover:shadow-lg transition <?= $total_overdue > 0 ? 'border-red-200 bg-red-50/30' : 'border-slate-100' ?>">
                    <div class="w-14 h-14 <?= $total_overdue > 0 ? 'bg-red-100 text-red-600' : 'bg-slate-100 text-slate-400' ?> rounded-2xl flex items-center justify-center text-2xl flex-shrink-0">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black <?= $total_overdue > 0 ? 'text-red-400' : 'text-slate-400' ?> uppercase tracking-widest">Terlambat</p>
                        <h3 class="text-3xl font-black <?= $total_overdue > 0 ? 'text-red-600' : 'text-slate-800' ?>"><?= $total_overdue ?></h3>
                    </div>
                </div>
            </a>
        </div>

        <!-- Tabel 5 Peminjaman Terakhir -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-7 py-5 border-b border-slate-50 flex justify-between items-center">
                <div>
                    <h4 class="font-black text-slate-800 text-lg">5 Peminjaman Terakhir</h4>
                    <p class="text-xs text-slate-400">Transaksi peminjaman terbaru</p>
                </div>
                <a href="pages/peminjaman/daftar.php" class="text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
                    Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <tr>
                            <th class="px-7 py-4 border-b border-slate-100">Peminjam</th>
                            <th class="px-7 py-4 border-b border-slate-100">Judul Buku</th>
                            <th class="px-7 py-4 border-b border-slate-100">Tgl Pinjam</th>
                            <th class="px-7 py-4 border-b border-slate-100">Batas Kembali</th>
                            <th class="px-7 py-4 border-b border-slate-100 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-50">
                        <?php while($row = mysqli_fetch_assoc($peminjaman_terbaru)):
                            $is_overdue = ($row['status'] === 'Dipinjam' && $today > $row['tgl_kembali']);
                        ?>
                        <tr class="hover:bg-slate-50/40 transition <?= $is_overdue ? 'bg-red-50/20' : '' ?>">
                            <td class="px-7 py-4 font-bold text-slate-700"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                            <td class="px-7 py-4 text-slate-500 italic">"<?= htmlspecialchars($row['judul']) ?>"</td>
                            <td class="px-7 py-4 text-xs text-slate-500 font-medium"><?= date('d/m/Y', strtotime($row['tgl_pinjam'])) ?></td>
                            <td class="px-7 py-4 text-xs font-bold <?= $is_overdue ? 'text-red-600' : 'text-slate-500' ?>">
                                <?= date('d/m/Y', strtotime($row['tgl_kembali'])) ?>
                                <?php if($is_overdue): ?>
                                <span class="ml-1 text-red-400 font-black text-[9px]">⚠ TERLAMBAT</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-7 py-4 text-center">
                                <?php if($row['status'] === 'Dipinjam'): ?>
                                    <span class="text-[9px] bg-amber-50 text-amber-600 px-3 py-1 rounded-full font-black uppercase">Dipinjam</span>
                                <?php else: ?>
                                    <span class="text-[9px] bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full font-black uppercase">Kembali</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── CHARTS ──────────────────────────────────────────────────── -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-6">

            <!-- Chart 1: Tren Peminjaman -->
            <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="font-black text-slate-800 text-base">Tren Peminjaman</h4>
                        <p class="text-xs text-slate-400">6 bulan terakhir</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1.5 rounded-full">
                        <i class="fas fa-chart-line text-[9px]"></i> Line Chart
                    </span>
                </div>
                <canvas id="chartTren" height="110"></canvas>
            </div>

            <!-- Chart 2: Status Buku -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 flex flex-col">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h4 class="font-black text-slate-800 text-base">Status Koleksi</h4>
                        <p class="text-xs text-slate-400">Dipinjam vs Tersedia</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-full">
                        <i class="fas fa-circle-dot text-[9px]"></i> Doughnut
                    </span>
                </div>
                <div class="flex-grow flex items-center justify-center">
                    <canvas id="chartStatus" style="max-height:180px;"></canvas>
                </div>
                <div class="flex justify-center gap-5 mt-4">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-400 inline-block"></span>
                        <span class="text-xs text-slate-500 font-semibold">Dipinjam</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-indigo-400 inline-block"></span>
                        <span class="text-xs text-slate-500 font-semibold">Tersedia</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Chart 3: Top 5 Buku (full-width) -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 mt-5">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h4 class="font-black text-slate-800 text-base">Top 5 Buku Terpopuler</h4>
                    <p class="text-xs text-slate-400">Berdasarkan total peminjaman</p>
                </div>
                <span class="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-violet-600 bg-violet-50 px-3 py-1.5 rounded-full">
                    <i class="fas fa-chart-bar text-[9px]"></i> Bar Chart
                </span>
            </div>
            <canvas id="chartTopBuku" height="80"></canvas>
        </div>

        <!-- Shortcut -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 lg:gap-5 mt-6">
            <a href="pages/buku/tambah.php" class="bg-indigo-600 text-white p-5 rounded-2xl flex items-center gap-4 hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                <i class="fas fa-book-medical text-2xl bg-white/10 p-3 rounded-xl"></i>
                <div>
                    <p class="text-[10px] text-indigo-200 font-black uppercase tracking-widest">Tambah</p>
                    <p class="font-black text-lg">Buku Baru</p>
                </div>
            </a>
            <a href="pages/anggota/tambah.php" class="bg-white text-slate-800 p-5 rounded-2xl flex items-center gap-4 hover:shadow-lg transition border border-slate-100">
                <i class="fas fa-user-plus text-2xl text-indigo-400 bg-indigo-50 p-3 rounded-xl"></i>
                <div>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Tambah</p>
                    <p class="font-black text-lg">Anggota Baru</p>
                </div>
            </a>
            <a href="pages/peminjaman/tambah.php" class="bg-white text-slate-800 p-5 rounded-2xl flex items-center gap-4 hover:shadow-lg transition border border-slate-100">
                <i class="fas fa-calendar-plus text-2xl text-amber-500 bg-amber-50 p-3 rounded-xl"></i>
                <div>
                    <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">Catat</p>
                    <p class="font-black text-lg">Pinjam Baru</p>
                </div>
            </a>
        </div>
    </main>

    <script>
        // ── Jam Digital ─────────────────────────────────────────────────────────
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent =
                now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
        }
        setInterval(updateClock, 1000);
        updateClock();

        // ── Data dari PHP ────────────────────────────────────────────────────────
        const trenLabels  = <?= json_encode($chart_labels) ?>;
        const trenData    = <?= json_encode($chart_data) ?>;
        const statusData  = [<?= $total_dipinjam ?>, <?= $buku_tersedia ?>];
        const topLabels   = <?= json_encode($top_buku_labels) ?>;
        const topData     = <?= json_encode($top_buku_data) ?>;

        // ── Default Chart.js Options ─────────────────────────────────────────────
        Chart.defaults.font.family = "'Inter', 'ui-sans-serif', system-ui, sans-serif";
        Chart.defaults.color = '#94a3b8';

        // ── Chart 1: Tren Peminjaman ─────────────────────────────────────────────
        new Chart(document.getElementById('chartTren'), {
            type: 'line',
            data: {
                labels: trenLabels,
                datasets: [{
                    label: 'Peminjaman',
                    data: trenData,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.08)',
                    borderWidth: 3,
                    pointBackgroundColor: '#6366f1',
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(0,0,0,0.04)' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // ── Chart 2: Status Buku ─────────────────────────────────────────────────
        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: ['Dipinjam', 'Tersedia'],
                datasets: [{
                    data: statusData,
                    backgroundColor: ['#fbbf24', '#818cf8'],
                    hoverBackgroundColor: ['#f59e0b', '#6366f1'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed} buku`
                        }
                    }
                }
            }
        });

        // ── Chart 3: Top 5 Buku ──────────────────────────────────────────────────
        new Chart(document.getElementById('chartTopBuku'), {
            type: 'bar',
            data: {
                labels: topLabels,
                datasets: [{
                    label: 'Jumlah Dipinjam',
                    data: topData,
                    backgroundColor: [
                        'rgba(99,102,241,0.85)',
                        'rgba(129,140,248,0.75)',
                        'rgba(167,139,250,0.75)',
                        'rgba(196,181,253,0.75)',
                        'rgba(221,214,254,0.75)'
                    ],
                    borderRadius: 10,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(0,0,0,0.04)' }
                    },
                    y: { grid: { display: false } }
                }
            }
        });
    </script>
</body>
</html>