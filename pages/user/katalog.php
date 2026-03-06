<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
include(__DIR__ . '/../../config/koneksi.php');

// Hanya anggota kalau login sebagai anggota; admin juga bisa akses
$is_anggota   = (isset($_SESSION['role']) && $_SESSION['role'] === 'anggota');
$kategori_usia = $is_anggota ? ($_SESSION['usia_anggota'] ?? 'semua') : '';

// Search & filter
$keyword      = $_GET['q'] ?? '';
$filter_jenis = $_GET['jenis'] ?? '';
$filter_kat   = $_GET['usia'] ?? $kategori_usia;

$where = "WHERE (judul LIKE '%$keyword%' OR penulis LIKE '%$keyword%' OR kategori LIKE '%$keyword%')";
if ($filter_jenis) $where .= " AND jenis_buku = '$filter_jenis'";
if ($filter_kat && $filter_kat !== 'semua') {
    $where .= " AND (kategori_usia = '$filter_kat' OR kategori_usia = 'semua')";
}

$data = mysqli_query($conn, "SELECT * FROM m_buku $where ORDER BY judul ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Buku - TBM Kurung Kambing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .book-card:hover { transform: translateY(-4px); }
        .book-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    </style>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">
<?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

<main class="flex-grow ml-64">
    <!-- Header -->
    <div class="bg-indigo-700 text-white px-10 py-10 bg-gradient-to-r from-indigo-700 to-indigo-900">
        <h1 class="text-4xl font-black mb-1">📚 Katalog Buku</h1>
        <p class="text-indigo-200">TBM Desa Kurung Kambing · Temukan buku yang kamu suka</p>
        <?php if($is_anggota && $kategori_usia && $kategori_usia !== 'semua'): ?>
        <div class="mt-3 inline-flex items-center gap-2 bg-white/10 px-4 py-2 rounded-full text-sm font-bold">
            <i class="fas fa-user-tag"></i>
            Kategori kamu: <?= ucfirst($kategori_usia) ?>
            — menampilkan buku sesuai usiamu
        </div>
        <?php endif; ?>
        <!-- Search -->
        <form method="GET" class="mt-6 flex gap-3 max-w-xl">
            <div class="relative flex-grow">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-sm"></i>
                <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>"
                       placeholder="Cari judul, penulis, kategori..."
                       class="w-full pl-11 pr-4 py-3 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-indigo-200 outline-none focus:bg-white/20 focus:border-white/40 backdrop-blur-xl">
            </div>
            <input type="hidden" name="jenis" value="<?= $filter_jenis ?>">
            <input type="hidden" name="usia" value="<?= $filter_kat ?>">
            <button type="submit" class="px-6 py-3 bg-white text-indigo-700 font-bold rounded-2xl hover:bg-indigo-50 transition">Cari</button>
        </form>
    </div>

    <div class="px-10 py-6">
        <!-- Filter Pills -->
        <div class="flex gap-2 mb-6 flex-wrap">
            <?php if(!$is_anggota): // admin bisa filter semua usia ?>
            <span class="text-xs text-slate-400 font-black uppercase tracking-widest self-center">Usia:</span>
            <?php foreach([''=>'Semua','dewasa'=>'🧑 Dewasa','remaja'=>'👦 Remaja','anak-anak'=>'👶 Anak-anak'] as $v=>$l): ?>
            <a href="?q=<?= $keyword ?>&usia=<?= $v ?>&jenis=<?= $filter_jenis ?>"
               class="px-4 py-1.5 rounded-full text-xs font-bold transition <?= ($filter_kat===$v)?'bg-indigo-600 text-white shadow':'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' ?>">
                <?= $l ?>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
            <span class="text-xs text-slate-400 font-black uppercase tracking-widest self-center ml-3">Jenis:</span>
            <a href="?q=<?= $keyword ?>&usia=<?= $filter_kat ?>&jenis=" class="px-4 py-1.5 rounded-full text-xs font-bold transition <?= !$filter_jenis?'bg-indigo-600 text-white':'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' ?>">Semua</a>
            <a href="?q=<?= $keyword ?>&usia=<?= $filter_kat ?>&jenis=fisik" class="px-4 py-1.5 rounded-full text-xs font-bold transition <?= $filter_jenis=='fisik'?'bg-indigo-600 text-white':'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' ?>">📖 Fisik</a>
            <a href="?q=<?= $keyword ?>&usia=<?= $filter_kat ?>&jenis=digital" class="px-4 py-1.5 rounded-full text-xs font-bold transition <?= $filter_jenis=='digital'?'bg-blue-600 text-white':'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' ?>">💻 Digital</a>
        </div>

        <!-- Grid Buku -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <?php $found = false; while($b = mysqli_fetch_assoc($data)): $found = true;
            $jenis = $b['jenis_buku'] ?? 'fisik';
            $kat_usia = $b['kategori_usia'] ?? 'semua';
            $usia_colors = ['dewasa'=>'blue','remaja'=>'purple','anak-anak'=>'pink','semua'=>'slate'];
            $color = $usia_colors[$kat_usia] ?? 'slate';
        ?>
        <div class="book-card bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl group">
            <!-- Cover dummy -->
            <div class="h-40 bg-gradient-to-br from-<?= $color ?>-100 to-<?= $color ?>-200 flex items-center justify-center relative">
                <i class="fas fa-book text-5xl text-<?= $color ?>-300"></i>
                <div class="absolute top-3 right-3 flex gap-1.5">
                    <?php if($jenis === 'digital'): ?>
                    <span class="px-2 py-0.5 bg-blue-600 text-white text-[9px] font-black rounded-full">💻 Digital</span>
                    <?php else: ?>
                    <span class="px-2 py-0.5 bg-white text-slate-600 text-[9px] font-black rounded-full shadow">📖 Fisik</span>
                    <?php endif; ?>
                </div>
                <?php if($b['stok'] == 0 && $jenis === 'fisik'): ?>
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                    <span class="bg-red-600 text-white text-xs font-black px-3 py-1 rounded-full">Habis</span>
                </div>
                <?php endif; ?>
            </div>
            <div class="p-5">
                <div class="text-[10px] font-black text-<?= $color ?>-500 mb-1"><?= htmlspecialchars($b['kategori'] ?? 'Umum') ?></div>
                <h3 class="font-black text-slate-800 text-base leading-tight mb-1 line-clamp-2"><?= htmlspecialchars($b['judul']) ?></h3>
                <p class="text-xs text-slate-400 mb-3"><?= htmlspecialchars($b['penulis'] ?? '-') ?></p>
                <div class="flex items-center justify-between">
                    <?php if($jenis === 'digital' && !empty($b['link_ebook'])): ?>
                    <a href="<?= htmlspecialchars($b['link_ebook']) ?>" target="_blank"
                       class="w-full text-center bg-blue-600 text-white text-xs font-bold px-4 py-2.5 rounded-2xl hover:bg-blue-700 transition flex items-center justify-center gap-2">
                        <i class="fas fa-external-link-alt text-[10px]"></i> Buka eBook
                    </a>
                    <?php elseif($jenis === 'fisik'): ?>
                        <span class="text-xs text-slag <?= $b['stok']>0?'text-emerald-600 font-bold':'text-red-500 font-bold' ?>">
                            <?= $b['stok']>0 ? "✓ {$b['stok']} Tersedia" : "✗ Habis" ?>
                        </span>
                        <span class="text-[10px] text-slate-300 font-black"><?= $b['kode_buku'] ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php if(!$found): ?>
            <div class="col-span-full py-20 text-center text-slate-400">
                <i class="fas fa-book-open text-5xl mb-4 text-slate-200"></i>
                <p class="font-bold text-lg">Tidak ada buku ditemukan.</p>
                <p class="text-sm">Coba kata kunci lain atau ubah filter.</p>
            </div>
        <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>
