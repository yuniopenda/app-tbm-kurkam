<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'anggota') { header("Location: /app-tbm-kurkam/pages/user/katalog.php"); exit; }
include(__DIR__ . '/../../config/koneksi.php');

// Search & pagination
$keyword = isset($_POST['cari']) ? $_POST['keyword'] : ($_SESSION['keyword_anggota'] ?? '');
if (isset($_POST['cari'])) $_SESSION['keyword_anggota'] = $keyword;

$filter_kategori = $_GET['kategori'] ?? '';

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : ($_SESSION['limit_anggota'] ?? 10);
$_SESSION['limit_anggota'] = $limit;
$halaman = (int)($_GET['halaman'] ?? 1);
$awal = ($limit * $halaman) - $limit;

$where = "WHERE (nama_lengkap LIKE '%$keyword%' OR kode_anggota LIKE '%$keyword%' OR telepon LIKE '%$keyword%')";
if ($filter_kategori) $where .= " AND kategori_usia = '$filter_kategori'";

$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM m_anggota $where"))['t'];
$jumlahHalaman = ceil($total / $limit);
$data = mysqli_query($conn, "SELECT * FROM m_anggota $where ORDER BY id DESC LIMIT $awal, $limit");

$badge = ['dewasa' => ['bg-blue-100 text-blue-700', '🧑 Dewasa'],
          'remaja' => ['bg-purple-100 text-purple-700', '👦 Remaja'],
          'anak-anak' => ['bg-pink-100 text-pink-700', '👶 Anak-anak']];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Anggota - TBM Kurung Kambing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">
<?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

<main class="flex-grow lg:ml-64 p-4 lg:p-8 pt-16 lg:pt-8">
    <div class="flex flex-wrap justify-between items-start gap-3 mb-6">
        <div>
            <h1 class="text-2xl lg:text-3xl font-black text-slate-800">Data Anggota</h1>
            <p class="text-slate-500 mt-1 text-sm">Kelola data anggota TBM Desa Kurung Kambing.</p>
        </div>
        <a href="tambah.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 lg:px-6 py-2.5 lg:py-3 rounded-2xl font-bold shadow-lg shadow-indigo-100 transition flex items-center gap-2 text-sm">
            <i class="fas fa-user-plus"></i> <span class="hidden sm:inline">Tambah Anggota</span>
        </a>
    </div>

    <!-- Filter Kategori Usia -->
    <div class="flex gap-3 mb-5 flex-wrap">
        <a href="daftar.php" class="px-5 py-2 rounded-full font-bold text-sm transition <?= !$filter_kategori ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200' ?>">
            Semua
        </a>
        <a href="?kategori=dewasa" class="px-5 py-2 rounded-full font-bold text-sm transition <?= $filter_kategori=='dewasa' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200' ?>">
            🧑 Dewasa (19+)
        </a>
        <a href="?kategori=remaja" class="px-5 py-2 rounded-full font-bold text-sm transition <?= $filter_kategori=='remaja' ? 'bg-purple-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200' ?>">
            👦 Remaja (13-18)
        </a>
        <a href="?kategori=anak-anak" class="px-5 py-2 rounded-full font-bold text-sm transition <?= $filter_kategori=='anak-anak' ? 'bg-pink-600 text-white shadow-md' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-200' ?>">
            👶 Anak-anak (5-12)
        </a>
    </div>

    <!-- Search + Limit -->
    <div class="flex gap-4 mb-6 flex-wrap">
        <form method="POST" class="flex gap-2 flex-grow max-w-lg">
            <div class="relative flex-grow">
                <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="keyword" value="<?= htmlspecialchars($keyword) ?>" placeholder="Cari nama atau kode anggota..."
                       class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm text-sm">
            </div>
            <button type="submit" name="cari" class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition">Cari</button>
        </form>
        <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Baris:</span>
            <select onchange="location=this.value" class="bg-transparent border-none text-sm font-bold text-indigo-600 outline-none cursor-pointer">
                <option value="?limit=10" <?= $limit==10?'selected':'' ?>>10</option>
                <option value="?limit=25" <?= $limit==25?'selected':'' ?>>25</option>
                <option value="?limit=50" <?= $limit==50?'selected':'' ?>>50</option>
            </select>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase tracking-widest">
                    <tr>
                        <th class="px-6 py-5 font-black text-center w-12 border-b border-slate-100">No</th>
                        <th class="px-6 py-5 font-black border-b border-slate-100">Anggota</th>
                        <th class="px-6 py-5 font-black border-b border-slate-100">Kategori</th>
                        <th class="px-6 py-5 font-black border-b border-slate-100">Kontak & Alamat</th>
                        <th class="px-6 py-5 font-black border-b border-slate-100">NIK</th>
                        <th class="px-6 py-5 font-black text-center border-b border-slate-100">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $no = $awal + 1; while($row = mysqli_fetch_assoc($data)): ?>
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-6 py-4 text-center text-xs font-bold text-slate-300"><?= $no++ ?></td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800"><?= htmlspecialchars($row['nama_lengkap']) ?></div>
                            <div class="text-[10px] font-black text-indigo-500 bg-indigo-50 px-2 py-0.5 rounded w-fit mt-1"><?= $row['kode_anggota'] ?></div>
                            <div class="text-xs text-slate-400 mt-0.5"><?= $row['jenis_kelamin'] ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php 
                            $kat = $row['kategori_usia'] ?? 'dewasa';
                            [$cls, $label] = $badge[$kat] ?? ['bg-gray-100 text-gray-600', $kat];
                            ?>
                            <span class="px-3 py-1 text-xs font-black rounded-full <?= $cls ?>"><?= $label ?></span>
                            <?php $tgl_lahir = $row['tanggal_lahir'] ?? ''; if($tgl_lahir && $tgl_lahir !== '0000-00-00'): ?>
                            <div class="text-[10px] text-slate-400 mt-1">
                                <?php
                                $lahir = new DateTime($tgl_lahir);
                                $usia = $lahir->diff(new DateTime())->y;
                                echo "$usia tahun";
                                ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-slate-700 font-medium"><?= $row['telepon'] ?: '-' ?></div>
                            <div class="text-xs text-slate-400 mt-0.5 max-w-[180px] truncate"><?= htmlspecialchars($row['alamat'] ?: '-') ?></div>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-500"><?= ($row['nik'] ?? '') ?: '<span class="text-slate-300 italic">-</span>' ?></td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="edit.php?id=<?= $row['id'] ?>" class="w-9 h-9 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition">
                                    <i class="fas fa-edit text-sm"></i>
                                </a>
                                <button onclick="hapusAnggota(<?= $row['id'] ?>)" class="w-9 h-9 flex items-center justify-center bg-red-50 text-red-500 rounded-xl hover:bg-red-600 hover:text-white transition">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 flex justify-between items-center">
            <p class="text-xs text-slate-400 font-black uppercase tracking-widest">Total: <?= $total ?> anggota</p>
            <div class="flex gap-1">
                <?php for($i = 1; $i <= $jumlahHalaman; $i++): ?>
                    <a href="?halaman=<?= $i ?>&kategori=<?= $filter_kategori ?>" class="w-8 h-8 flex items-center justify-center <?= $i==$halaman ? 'bg-indigo-600 text-white' : 'bg-white text-slate-500 border border-slate-200 hover:bg-slate-50' ?> rounded-lg text-xs font-bold transition"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</main>

<script>
function hapusAnggota(id) {
    Swal.fire({ title: 'Hapus Anggota?', text: 'Data tidak bisa dikembalikan!', icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#ef4444', cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!', cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-[2rem]' }
    }).then(r => { if (r.isConfirmed) location.href = 'hapus.php?id=' + id; });
}
</script>
<?php if(isset($_SESSION['sukses_tambah_anggota'])): unset($_SESSION['sukses_tambah_anggota']); ?>
<script>Swal.fire({ title: 'Berhasil!', text: 'Anggota baru berhasil ditambahkan.', icon: 'success', confirmButtonColor: '#4f46e5', customClass: { popup: 'rounded-[2rem]' } });</script>
<?php endif; ?>
</body>
</html>
