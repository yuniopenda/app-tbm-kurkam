<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'anggota') { header("Location: /app-tbm-kurkam/pages/user/katalog.php"); exit; }
include(__DIR__ . '/../../config/koneksi.php');

// Search & filter
$keyword      = isset($_POST['cari']) ? $_POST['keyword'] : ($_SESSION['keyword_buku'] ?? '');
$filter_usia  = $_GET['usia'] ?? '';
$filter_jenis = $_GET['jenis'] ?? '';
if (isset($_POST['cari'])) $_SESSION['keyword_buku'] = $keyword;

$limit   = isset($_GET['limit']) ? (int)$_GET['limit'] : ($_SESSION['limit_buku'] ?? 10);
$_SESSION['limit_buku'] = $limit;
$halaman = (int)($_GET['halaman'] ?? 1);
$awal    = ($limit * $halaman) - $limit;

$where = "WHERE (judul LIKE '%$keyword%' OR kode_buku LIKE '%$keyword%' OR penulis LIKE '%$keyword%')";
if ($filter_usia)  $where .= " AND (kategori_usia = '$filter_usia' OR kategori_usia = 'semua')";
if ($filter_jenis) $where .= " AND jenis_buku = '$filter_jenis'";

$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM m_buku $where"))['t'];
$jumlahHalaman = max(1, ceil($total / $limit));
$data = mysqli_query($conn, "SELECT * FROM m_buku $where ORDER BY id DESC LIMIT $awal, $limit");

// Siapkan badge maps
$usia_badge = ['dewasa'=>'bg-blue-100 text-blue-700','remaja'=>'bg-purple-100 text-purple-700','anak-anak'=>'bg-pink-100 text-pink-700','semua'=>'bg-gray-100 text-gray-600'];
$usia_label = ['dewasa'=>'🧑 Dewasa','remaja'=>'👦 Remaja','anak-anak'=>'👶 Anak-anak','semua'=>'👥 Semua'];

// Tampung ke array (dipakai 2x: card mobile + tabel desktop)
$rows = [];
while($row = mysqli_fetch_assoc($data)) $rows[] = $row;
$found = count($rows) > 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku - TBM Kurung Kambing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Pills scroll — sembunyikan scrollbar tapi tetap bisa digeser */
        .pills-scroll { -webkit-overflow-scrolling: touch; scrollbar-width: none; }
        .pills-scroll::-webkit-scrollbar { display: none; }
        /* Tabel desktop — scrollbar tipis indigo */
        .tbl-scroll { -webkit-overflow-scrolling: touch; scrollbar-width: thin; scrollbar-color: #818cf8 #f1f5f9; }
        .tbl-scroll::-webkit-scrollbar { height: 5px; }
        .tbl-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
        .tbl-scroll::-webkit-scrollbar-thumb { background: #818cf8; border-radius: 99px; }
    </style>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">
<?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

<main class="flex-grow lg:ml-64 p-4 lg:p-8 pt-16 lg:pt-8">

    <!-- ── Header ── -->
    <div class="flex flex-wrap justify-between items-start gap-3 mb-5">
        <div>
            <h1 class="text-2xl lg:text-3xl font-black text-slate-800">Katalog Buku</h1>
            <p class="text-slate-500 mt-1 text-sm">Kelola koleksi buku TBM Desa Kurung Kambing.</p>
        </div>
        <div class="flex gap-2">
            <a href="template_excel.php" target="_blank"
               class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 lg:px-5 py-2.5 rounded-2xl font-bold shadow-lg transition flex items-center gap-2 text-sm">
                <i class="fas fa-file-excel"></i>
                <span class="hidden sm:inline">Template Excel</span>
            </a>
            <a href="tambah.php"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 lg:px-6 py-2.5 rounded-2xl font-bold shadow-lg shadow-indigo-100 transition flex items-center gap-2 text-sm">
                <i class="fas fa-plus"></i>
                <span class="hidden sm:inline">Tambah Buku</span>
            </a>
        </div>
    </div>

    <!-- ── Filter Pills (scroll horizontal, tidak wrap) ── -->
    <div class="overflow-x-auto pills-scroll mb-4">
        <div class="flex items-center gap-2 pb-1" style="width: max-content;">
            <span class="text-xs text-slate-400 font-black uppercase tracking-widest whitespace-nowrap">Usia:</span>
            <?php
            $usia_filter = ['' => 'Semua', 'dewasa' => '🧑 Dewasa', 'remaja' => '👦 Remaja', 'anak-anak' => '👶 Anak-anak'];
            foreach($usia_filter as $val => $label):
                $active = $filter_usia === $val;
            ?>
            <a href="?usia=<?= $val ?>&jenis=<?= $filter_jenis ?>"
               class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-bold transition
                      <?= $active ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' ?>">
                <?= $label ?>
            </a>
            <?php endforeach; ?>
            <span class="text-xs text-slate-400 font-black uppercase tracking-widest whitespace-nowrap ml-2">Jenis:</span>
            <a href="?usia=<?= $filter_usia ?>&jenis="
               class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-bold transition <?= !$filter_jenis ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' ?>">Semua</a>
            <a href="?usia=<?= $filter_usia ?>&jenis=fisik"
               class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-bold transition <?= $filter_jenis=='fisik' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' ?>">📖 Fisik</a>
            <a href="?usia=<?= $filter_usia ?>&jenis=digital"
               class="whitespace-nowrap px-4 py-1.5 rounded-full text-xs font-bold transition <?= $filter_jenis=='digital' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' ?>">💻 Digital</a>
        </div>
    </div>

    <!-- ── Search + Limit ── -->
    <div class="flex gap-2 lg:gap-4 mb-5">
        <form method="POST" class="flex gap-2 flex-grow">
            <div class="relative flex-grow">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>"
                       placeholder="Cari judul, kode, penulis..."
                       class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm text-sm">
            </div>
            <button type="submit" name="cari"
                    class="bg-indigo-600 text-white px-4 lg:px-6 py-3 rounded-xl font-bold hover:bg-indigo-700 transition text-sm">Cari</button>
        </form>
        <div class="flex items-center gap-2 bg-white px-3 py-2 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest hidden sm:inline">Baris:</span>
            <select onchange="location=this.value" class="bg-transparent border-none text-sm font-bold text-indigo-600 outline-none cursor-pointer">
                <option value="?limit=10&usia=<?= $filter_usia ?>&jenis=<?= $filter_jenis ?>" <?= $limit==10?'selected':'' ?>>10</option>
                <option value="?limit=25&usia=<?= $filter_usia ?>&jenis=<?= $filter_jenis ?>" <?= $limit==25?'selected':'' ?>>25</option>
                <option value="?limit=50&usia=<?= $filter_usia ?>&jenis=<?= $filter_jenis ?>" <?= $limit==50?'selected':'' ?>>50</option>
            </select>
        </div>
    </div>

    <!-- ══════════════════════════════════════
         CARD VIEW — Mobile only (< lg)
    ══════════════════════════════════════ -->
    <div class="lg:hidden space-y-3">
        <?php if(!$found): ?>
        <div class="bg-white rounded-3xl p-10 text-center text-slate-400 font-bold italic shadow-sm border border-slate-100">
            Tidak ada buku ditemukan...
        </div>
        <?php endif; ?>

        <?php foreach($rows as $row):
            $kat    = $row['kategori_usia'] ?? 'semua';
            $covOk  = !empty($row['gambar']) && file_exists(realpath(__DIR__ . '/../../assets/covers/' . $row['gambar']));
            $covUrl = '/app-tbm-kurkam/assets/covers/' . $row['gambar'];
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 flex gap-3 items-start">

            <!-- Cover -->
            <div class="w-12 h-16 flex-shrink-0 bg-slate-100 rounded-xl overflow-hidden border border-slate-200 flex items-center justify-center">
                <?php if($covOk): ?>
                    <img src="<?= $covUrl ?>" class="w-full h-full object-cover" alt="cover">
                <?php else: ?>
                    <i class="fas fa-book text-slate-300 text-lg"></i>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="flex-grow min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <span class="inline-block text-[10px] font-black text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded mb-1"><?= htmlspecialchars($row['kode_buku']) ?></span>
                        <p class="font-black text-slate-800 text-sm leading-snug line-clamp-2"><?= htmlspecialchars($row['judul']) ?></p>
                        <p class="text-xs text-slate-400 mt-0.5 truncate"><?= htmlspecialchars($row['penulis'] ?? '-') ?> · <?= htmlspecialchars($row['penerbit'] ?? '-') ?></p>
                    </div>
                    <!-- Tombol aksi -->
                    <div class="flex gap-1.5 flex-shrink-0 mt-0.5">
                        <a href="edit.php?id=<?= $row['id'] ?>"
                           class="w-8 h-8 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition">
                            <i class="fas fa-edit text-xs"></i>
                        </a>
                        <button onclick="hapusBuku(<?= $row['id'] ?>)"
                                class="w-8 h-8 flex items-center justify-center bg-red-50 text-red-500 rounded-xl hover:bg-red-600 hover:text-white transition">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </button>
                    </div>
                </div>

                <!-- Badge bawah -->
                <div class="flex flex-wrap gap-1.5 mt-2 items-center">
                    <?php if($row['jenis_buku'] === 'digital'): ?>
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-black rounded-full">💻 Digital</span>
                    <?php else: ?>
                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[10px] font-black rounded-full">📖 Fisik</span>
                    <?php endif; ?>

                    <span class="px-2 py-0.5 <?= $usia_badge[$kat] ?? 'bg-gray-100 text-gray-600' ?> text-[10px] font-black rounded-full">
                        <?= $usia_label[$kat] ?? $kat ?>
                    </span>

                    <?php if($row['stok'] > 0): ?>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-full">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span><?= $row['stok'] ?> Tersedia
                        </span>
                    <?php else: ?>
                        <span class="px-2 py-0.5 bg-red-100 text-red-600 text-[10px] font-black rounded-full italic">Habis</span>
                    <?php endif; ?>

                    <?php if($row['jenis_buku'] === 'digital' && !empty($row['link_ebook'])): ?>
                        <a href="<?= htmlspecialchars($row['link_ebook']) ?>" target="_blank"
                           class="inline-flex items-center gap-1 text-[10px] text-blue-600 font-black hover:underline">
                            <i class="fas fa-external-link-alt text-[9px]"></i> Buka eBook
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Pagination mobile -->
        <div class="flex justify-between items-center pt-1">
            <p class="text-xs text-slate-400 font-black uppercase tracking-widest">Total: <?= $total ?> buku</p>
            <div class="flex gap-1 flex-wrap">
                <?php for($i = 1; $i <= $jumlahHalaman; $i++): ?>
                    <a href="?halaman=<?= $i ?>&usia=<?= $filter_usia ?>&jenis=<?= $filter_jenis ?>"
                       class="w-8 h-8 flex items-center justify-center text-xs font-bold rounded-lg transition
                              <?= $i==$halaman ? 'bg-indigo-600 text-white' : 'bg-white text-slate-500 border border-slate-200' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div><!-- /card view -->


    <!-- ══════════════════════════════════════
         TABLE VIEW — Desktop only (>= lg)
    ══════════════════════════════════════ -->
    <div class="hidden lg:block bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="tbl-scroll overflow-x-auto">
            <table class="min-w-[820px] w-full text-left border-collapse">
                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase tracking-widest">
                    <tr>
                        <th class="px-5 py-5 font-black text-center w-10 border-b border-slate-100">No</th>
                        <th class="px-5 py-5 font-black border-b border-slate-100">Sampul</th>
                        <th class="px-5 py-5 font-black border-b border-slate-100">Buku</th>
                        <th class="px-5 py-5 font-black border-b border-slate-100">Penulis / Penerbit</th>
                        <th class="px-5 py-5 font-black border-b border-slate-100">Jenis &amp; Usia</th>
                        <th class="px-5 py-5 font-black text-center border-b border-slate-100">Stok</th>
                        <th class="px-5 py-5 font-black text-center border-b border-slate-100">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php $no = $awal + 1; foreach($rows as $row):
                    $kat   = $row['kategori_usia'] ?? 'semua';
                    $covOk = !empty($row['gambar']) && file_exists(realpath(__DIR__ . '/../../assets/covers/' . $row['gambar']));
                    $covUrl= '/app-tbm-kurkam/assets/covers/' . $row['gambar'];
                ?>
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="px-5 py-4 text-center text-xs font-bold text-slate-300"><?= $no++ ?></td>
                    <td class="px-5 py-4">
                        <div class="w-12 h-16 bg-slate-100 rounded-xl overflow-hidden border border-slate-200 flex items-center justify-center">
                            <?php if($covOk): ?>
                                <img src="<?= $covUrl ?>" class="w-full h-full object-cover" alt="cover">
                            <?php else: ?>
                                <i class="fas fa-book text-slate-300 text-lg"></i>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <div class="text-[10px] font-black text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded w-fit mb-1"><?= htmlspecialchars($row['kode_buku']) ?></div>
                        <div class="font-bold text-slate-800"><?= htmlspecialchars($row['judul']) ?></div>
                        <div class="text-xs text-slate-400 mt-0.5"><?= htmlspecialchars($row['kategori'] ?? '-') ?></div>
                        <?php if($row['jenis_buku'] === 'digital' && !empty($row['link_ebook'])): ?>
                        <a href="<?= htmlspecialchars($row['link_ebook']) ?>" target="_blank"
                           class="inline-flex items-center gap-1 text-xs text-blue-600 font-bold mt-1 hover:underline">
                            <i class="fas fa-external-link-alt text-[9px]"></i> Buka eBook
                        </a>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4">
                        <div class="font-bold text-slate-700 text-sm"><?= htmlspecialchars($row['penulis'] ?? '-') ?></div>
                        <div class="text-xs text-slate-400"><?= htmlspecialchars($row['penerbit'] ?? '-') ?></div>
                    </td>
                    <td class="px-5 py-4">
                        <?php if($row['jenis_buku'] === 'digital'): ?>
                            <span class="px-2.5 py-1 bg-blue-100 text-blue-700 text-[10px] font-black rounded-full">💻 Digital</span>
                        <?php else: ?>
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[10px] font-black rounded-full">📖 Fisik</span>
                        <?php endif; ?>
                        <div class="mt-1">
                            <span class="px-2.5 py-1 <?= $usia_badge[$kat] ?? 'bg-gray-100 text-gray-600' ?> text-[10px] font-black rounded-full">
                                <?= $usia_label[$kat] ?? $kat ?>
                            </span>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <?php if($row['stok'] > 0): ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-black rounded-full">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span><?= $row['stok'] ?> Tersedia
                            </span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-red-100 text-red-600 text-xs font-black rounded-full italic">Habis</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="edit.php?id=<?= $row['id'] ?>"
                               class="w-9 h-9 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition">
                                <i class="fas fa-edit text-sm"></i>
                            </a>
                            <button onclick="hapusBuku(<?= $row['id'] ?>)"
                                    class="w-9 h-9 flex items-center justify-center bg-red-50 text-red-500 rounded-xl hover:bg-red-600 hover:text-white transition">
                                <i class="fas fa-trash-alt text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(!$found): ?>
                    <tr><td colspan="7" class="px-6 py-16 text-center text-slate-400 font-bold italic">Tidak ada buku ditemukan...</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Pagination desktop -->
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex justify-between items-center">
            <p class="text-xs text-slate-400 font-black uppercase tracking-widest">Total: <?= $total ?> buku</p>
            <div class="flex gap-1">
                <?php for($i = 1; $i <= $jumlahHalaman; $i++): ?>
                    <a href="?halaman=<?= $i ?>&usia=<?= $filter_usia ?>&jenis=<?= $filter_jenis ?>"
                       class="w-8 h-8 flex items-center justify-center text-xs font-bold rounded-lg transition
                              <?= $i==$halaman ? 'bg-indigo-600 text-white' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div><!-- /table view -->

</main>

<script>
function hapusBuku(id) {
    Swal.fire({
        title: 'Hapus Buku?', text: 'Data tidak bisa dikembalikan!', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-[2rem]' }
    }).then(r => { if (r.isConfirmed) location.href = 'hapus.php?id=' + id; });
}
</script>
<?php if(isset($_SESSION['sukses_tambah'])): unset($_SESSION['sukses_tambah']); ?>
<script>Swal.fire({ title: 'Buku Ditambahkan!', icon: 'success', confirmButtonColor: '#4f46e5', customClass: { popup: 'rounded-[2rem]' } });</script>
<?php endif; ?>
<?php if(isset($_SESSION['sukses_edit'])): unset($_SESSION['sukses_edit']); ?>
<script>Swal.fire({ title: 'Berhasil Diperbarui!', icon: 'success', confirmButtonColor: '#4f46e5', customClass: { popup: 'rounded-[2rem]' } });</script>
<?php endif; ?>
<?php if(isset($_SESSION['sukses_hapus'])): unset($_SESSION['sukses_hapus']); ?>
<script>Swal.fire({ title: 'Terhapus!', icon: 'success', confirmButtonColor: '#ef4444', customClass: { popup: 'rounded-[2rem]' } });</script>
<?php endif; ?>
</body>
</html>
