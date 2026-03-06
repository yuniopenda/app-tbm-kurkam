<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'anggota') { header("Location: /app-tbm-kurkam/pages/user/katalog.php"); exit; }
include(__DIR__ . '/../../config/koneksi.php');

// Search
$keyword = isset($_POST['cari']) ? $_POST['keyword'] : ($_SESSION['keyword_pinjam'] ?? '');
$filter_status = $_GET['status'] ?? '';
if (isset($_POST['cari'])) $_SESSION['keyword_pinjam'] = $keyword;

$limit   = isset($_GET['limit']) ? (int)$_GET['limit'] : ($_SESSION['limit_pinjam'] ?? 10);
$_SESSION['limit_pinjam'] = $limit;
$halaman = (int)($_GET['halaman'] ?? 1);
$awal    = ($limit * $halaman) - $limit;

$where = "WHERE (m.nama_lengkap LIKE '%$keyword%' OR b.judul LIKE '%$keyword%' OR b.kode_buku LIKE '%$keyword%')";
if ($filter_status) $where .= " AND t.status = '$filter_status'";

$total = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as t FROM t_peminjaman t
     JOIN m_anggota m ON t.id_anggota = m.id
     JOIN m_buku b ON t.id_buku = b.id $where"))['t'];
$jumlahHalaman = max(1, ceil($total / $limit));

$data = mysqli_query($conn,
    "SELECT t.*, m.nama_lengkap, m.telepon, m.alamat, b.judul, b.kode_buku
     FROM t_peminjaman t
     JOIN m_anggota m ON t.id_anggota = m.id
     JOIN m_buku b ON t.id_buku = b.id
     $where ORDER BY t.id DESC LIMIT $awal, $limit");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Peminjaman - TBM Kurung Kambing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">
<?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

<main class="flex-grow lg:ml-64 p-4 lg:p-8 pt-16 lg:pt-8">
    <div class="flex flex-wrap justify-between items-start gap-3 mb-6">
        <div>
            <h1 class="text-2xl lg:text-3xl font-black text-slate-800">Daftar Peminjaman</h1>
            <p class="text-slate-500 mt-1 text-sm">Manajemen sirkulasi buku · Denda: Rp 1.000/hari</p>
        </div>
        <a href="tambah.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 lg:px-6 py-2.5 lg:py-3 rounded-2xl font-bold shadow-lg shadow-indigo-100 transition flex items-center gap-2 text-sm">
            <i class="fas fa-plus"></i> <span class="hidden sm:inline">Pinjam Baru</span>
        </a>
    </div>

    <!-- Filter Status -->
    <div class="flex gap-3 mb-5 flex-wrap">
        <a href="daftar.php" class="px-5 py-2 rounded-full text-sm font-bold transition <?= !$filter_status ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' ?>">Semua</a>
        <a href="?status=Dipinjam" class="px-5 py-2 rounded-full text-sm font-bold transition <?= $filter_status=='Dipinjam' ? 'bg-amber-500 text-white shadow-md' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' ?>">⏳ Dipinjam</a>
        <a href="?status=Kembali" class="px-5 py-2 rounded-full text-sm font-bold transition <?= $filter_status=='Kembali' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' ?>">✅ Sudah Kembali</a>
    </div>

    <!-- Search + Limit -->
    <div class="flex gap-4 mb-6">
        <form method="POST" class="flex gap-2 flex-grow max-w-lg">
            <div class="relative flex-grow">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari nama, judul, kode buku..."
                       class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm text-sm">
            </div>
            <button type="submit" name="cari" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700 transition">Cari</button>
        </form>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase tracking-widest">
                    <tr>
                        <th class="px-5 py-5 font-black text-center w-10 border-b border-slate-100">No</th>
                        <th class="px-5 py-5 font-black border-b border-slate-100">Peminjam & Alamat</th>
                        <th class="px-5 py-5 font-black border-b border-slate-100">Buku</th>
                        <th class="px-5 py-5 font-black border-b border-slate-100">Tgl Pinjam</th>
                        <th class="px-5 py-5 font-black border-b border-slate-100">Batas Kembali</th>
                        <th class="px-5 py-5 font-black text-center border-b border-slate-100">Status</th>
                        <th class="px-5 py-5 font-black text-center border-b border-slate-100">Denda</th>
                        <th class="px-5 py-5 font-black text-center border-b border-slate-100">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php $no = $awal + 1; $found = false; while($row = mysqli_fetch_assoc($data)): $found = true;
                    $today = date('Y-m-d');
                    $telat_hari = max(0, (int)floor((strtotime($today) - strtotime($row['tgl_kembali'])) / 86400));
                    $denda_estimasi = ($row['status'] === 'Dipinjam') ? $telat_hari * 1000 : ((int)($row['denda'] ?? 0));
                    $is_overdue = ($row['status'] === 'Dipinjam' && $today > $row['tgl_kembali']);
                    $is_due_soon = ($row['status'] === 'Dipinjam' && $today <= $row['tgl_kembali'] && $telat_hari >= 0 && (strtotime($row['tgl_kembali']) - strtotime($today)) / 86400 <= 3);
                ?>
                <tr class="hover:bg-slate-50/60 transition <?= $is_overdue ? 'bg-red-50/30' : ($is_due_soon ? 'bg-amber-50/30' : '') ?>">
                    <td class="px-5 py-4 text-center text-xs font-bold text-slate-300"><?= $no++ ?></td>
                    <td class="px-5 py-4">
                        <div class="font-bold text-slate-800"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                        <div class="text-xs text-slate-400 mt-0.5">📞 <?= $row['telepon'] ?: '-' ?></div>
                        <div class="text-xs text-slate-400 mt-0.5 max-w-[200px] truncate">📍 <?= htmlspecialchars($row['alamat'] ?: '-') ?></div>
                    </td>
                    <td class="px-5 py-4">
                        <div class="text-[10px] font-black text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded w-fit mb-1"><?= $row['kode_buku'] ?></div>
                        <div class="font-bold text-slate-700 text-sm"><?= htmlspecialchars($row['judul']) ?></div>
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-500 font-medium"><?= date('d/m/Y', strtotime($row['tgl_pinjam'])) ?></td>
                    <td class="px-5 py-4">
                        <div class="text-sm font-bold <?= $is_overdue ? 'text-red-600' : ($is_due_soon ? 'text-amber-600' : 'text-slate-700') ?>">
                            <?= date('d/m/Y', strtotime($row['tgl_kembali'])) ?>
                        </div>
                        <?php if($is_overdue): ?>
                            <div class="text-[10px] text-red-500 font-black">⚠ Terlambat <?= $telat_hari ?> hari</div>
                        <?php elseif($is_due_soon && $row['status'] === 'Dipinjam'): ?>
                            <div class="text-[10px] text-amber-500 font-black">⏰ Segera dikembalikan</div>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <?php if($row['status'] === 'Dipinjam'): ?>
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-black rounded-full">Dipinjam</span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-full">Kembali</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <?php if($denda_estimasi > 0): ?>
                            <span class="font-black text-red-600 text-sm">Rp <?= number_format($denda_estimasi, 0, ',', '.') ?></span>
                            <?php if($row['status'] === 'Dipinjam'): ?>
                            <div class="text-[9px] text-slate-400">(estimasi)</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-emerald-600 font-bold text-sm">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <?php if($row['status'] === 'Dipinjam'): ?>
                            <button onclick="konfirmasiKembali(<?= $row['id'] ?>, '<?= addslashes($row['nama_lengkap']) ?>', <?= $denda_estimasi ?>)"
                                    class="bg-orange-500 text-white text-xs px-4 py-2 rounded-xl font-bold hover:bg-orange-600 transition shadow-md">
                                Kembalikan
                            </button>
                        <?php else: ?>
                            <span class="text-slate-300 text-xs font-bold italic">Selesai</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if(!$found): ?>
                <tr><td colspan="8" class="px-6 py-16 text-center text-slate-400 font-bold italic">Tidak ada data peminjaman...</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex justify-between items-center">
            <p class="text-xs text-slate-400 font-black uppercase tracking-widest">Total: <?= $total ?> transaksi</p>
            <div class="flex gap-1">
                <?php for($i = 1; $i <= $jumlahHalaman; $i++): ?>
                    <a href="?halaman=<?= $i ?>&status=<?= $filter_status ?>"
                       class="w-8 h-8 flex items-center justify-center <?= $i==$halaman ? 'bg-indigo-600 text-white' : 'bg-white text-slate-500 border border-slate-200' ?> rounded-lg text-xs font-bold transition"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</main>

<script>
function konfirmasiKembali(id, nama, denda) {
    const dendaText = denda > 0
        ? `<br><span class="text-red-600 font-bold">Denda keterlambatan: Rp ${denda.toLocaleString('id-ID')}</span>`
        : '<br><span class="text-emerald-600 font-bold">Tidak ada denda ✓</span>';
    Swal.fire({
        title: 'Konfirmasi Pengembalian',
        html: `<b>${nama}</b> mengembalikan buku?${dendaText}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f97316', cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Kembalikan!', cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-[2rem]' }
    }).then(r => { if (r.isConfirmed) location.href = 'kembalikan.php?id=' + id; });
}
</script>

<?php if(isset($_SESSION['info_kembali'])):
    $ik = $_SESSION['info_kembali']; unset($_SESSION['info_kembali']); ?>
<script>
Swal.fire({
    title: 'Buku Dikembalikan! ✅',
    html: `<b><?= addslashes($ik['nama']) ?></b> mengembalikan "<i><?= addslashes($ik['judul']) ?></i>"<br><br>
           <?= $ik['denda'] > 0 ? '<span class="text-red-600 font-bold">Denda: Rp '.number_format($ik['denda'],0,',','.').' ('.$ik['telat'].' hari)</span>' : '<span class="text-emerald-600">Tidak ada denda ✓</span>' ?>`,
    icon: '<?= $ik['denda'] > 0 ? 'warning' : 'success' ?>',
    confirmButtonColor: '#4f46e5', customClass: { popup: 'rounded-[2rem]' }
});
</script>
<?php endif; ?>
</body>
</html>
