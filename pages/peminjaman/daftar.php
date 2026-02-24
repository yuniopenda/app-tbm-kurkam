<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: /app-tbm-kurkam/login.php");
    exit;
}
include(__DIR__ . '/../../config/koneksi.php');

// 1. Logika Search
$keyword = "";
if (isset($_POST['cari'])) {
    $keyword = $_POST['keyword'];
    $_SESSION['keyword'] = $keyword;
} else {
    $keyword = isset($_SESSION['keyword']) ? $_SESSION['keyword'] : "";
}

// 2. Logika Limit (10, 50, 100)
$limit = isset($_SESSION['limit']) ? $_SESSION['limit'] : 10;
if (isset($_GET['limit'])) {
    $limit = (int)$_GET['limit'];
    $_SESSION['limit'] = $limit;
}

// 3. Logika Pagination
$halamanAktif = (isset($_GET['halaman'])) ? (int)$_GET['halaman'] : 1;
$awalData = ($limit * $halamanAktif) - $limit;

// 4. Hitung Total Data
$queryTotal = mysqli_query($conn, "SELECT COUNT(*) AS total FROM t_peminjaman t 
               JOIN m_anggota m ON t.id_anggota = m.id 
               JOIN m_buku b ON t.id_buku = b.id
               WHERE m.nama_lengkap LIKE '%$keyword%' OR b.judul LIKE '%$keyword%' OR b.kode_buku LIKE '%$keyword%'");
$dataTotal = mysqli_fetch_assoc($queryTotal);
$totalData = $dataTotal['total'];
$jumlahHalaman = ceil($totalData / $limit);

// 5. Ambil Data
$data = mysqli_query($conn, "SELECT t.*, m.nama_lengkap, b.judul, b.kode_buku 
               FROM t_peminjaman t 
               JOIN m_anggota m ON t.id_anggota = m.id 
               JOIN m_buku b ON t.id_buku = b.id 
               WHERE m.nama_lengkap LIKE '%$keyword%' OR b.judul LIKE '%$keyword%' OR b.kode_buku LIKE '%$keyword%'
               ORDER BY t.id DESC LIMIT $awalData, $limit");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Peminjam - PinjamBuku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">

    <?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

    <main class="flex-grow ml-64 p-8">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Daftar Peminjaman Buku</h1>
                <p class="text-slate-500">Manajemen peminjaman dan pengembalian buku.</p>
            </div>
            <a href="tambah.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-indigo-100 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Pinjam Baru
            </a>
        </div>

        <div class="flex flex-wrap gap-4 items-center justify-between mb-6">
            <form action="" method="POST" class="flex gap-2 flex-grow max-w-md">
                <div class="relative flex-grow">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <i class="fas fa-search text-sm"></i>
                    </span>
                    <input type="text" name="keyword" placeholder="Cari data..." value="<?= $keyword; ?>"
                           class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm transition-all text-sm">
                </div>
                <button type="submit" name="cari" class="bg-indigo-600 text-white px-8 py-3 rounded-2xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition-all transform active:scale-95">
                    Cari
</button>
            </form>

            <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm">
                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Baris:</span>
                <select onchange="location = this.value;" class="bg-transparent border-none text-sm font-bold text-indigo-600 outline-none cursor-pointer">
                    <option value="?limit=10" <?= ($limit == 10) ? 'selected' : ''; ?>>10</option>
                    <option value="?limit=50" <?= ($limit == 50) ? 'selected' : ''; ?>>50</option>
                    <option value="?limit=100" <?= ($limit == 100) ? 'selected' : ''; ?>>100</option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase tracking-[0.15em]">
                        <tr>
                            <th class="px-6 py-5 font-black text-center w-16 border-b border-slate-100">No</th>
                            <th class="px-6 py-5 font-black border-b border-slate-100">Peminjam & Buku</th>
                            <th class="px-6 py-5 font-black border-b border-slate-100">Tgl Pinjam</th>
                            <th class="px-6 py-5 font-black border-b border-slate-100">Batas Kembali</th>
                            <th class="px-6 py-5 font-black text-center border-b border-slate-100">Status</th>
                            <th class="px-6 py-5 font-black text-center border-b border-slate-100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php 
                        $no = $awalData + 1; 
                        if(mysqli_num_rows($data) > 0):
                            while($row = mysqli_fetch_assoc($data)) : 
                        ?>
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-6 py-5 text-center text-xs font-bold text-slate-400"><?= $no++; ?></td>
                            <td class="px-6 py-5">
                                <div class="font-bold text-slate-800 text-sm"><?= $row['nama_lengkap']; ?></div>
                                <div class="text-[10px] font-bold text-indigo-500 uppercase mt-1">
                                    <span class="bg-indigo-50 px-1.5 py-0.5 rounded text-indigo-600 mr-1">[<?= $row['kode_buku']; ?>]</span> 
                                    - <?= $row['judul']; ?>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm text-slate-500 font-medium"><?= date('d/m/Y', strtotime($row['tgl_pinjam'])); ?></td>
                            <td class="px-6 py-5 text-sm text-slate-800 font-bold"><?= date('d/m/Y', strtotime($row['tgl_kembali'])); ?></td>
                            <td class="px-6 py-5 text-center">
                                <?php if($row['status'] == 'Dipinjam'): ?>
                                    <span class="px-3 py-1 bg-amber-50 text-amber-600 text-[9px] font-black rounded-lg uppercase border border-amber-100 italic">Dipinjam</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-black rounded-lg uppercase border border-emerald-100 italic">Kembali</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <?php if($row['status'] == 'Dipinjam'): ?>
                                    <button onclick="konfirmasiKembali(<?= $row['id']; ?>)" 
                                    class="bg-orange-500 text-white text-[10px] px-4 py-2 rounded-xl font-bold hover:bg-orange-600 shadow-md shadow-orange-100 transition-all inline-block">
                                    Kembalikan
                                    </button>
                                <?php else: ?>
                                    <span class="text-slate-300 text-[10px] font-bold italic">Selesai</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-20 text-center text-slate-400 font-bold italic">Data tidak ditemukan...</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-5 bg-slate-50/80 border-t border-slate-100 flex justify-between items-center">
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">
                    Halaman <?= $halamanAktif; ?> dari <?= $jumlahHalaman ?: 1; ?>
                </p>
                
                <div class="flex gap-1">
                    <?php if($halamanAktif > 1) : ?>
                        <a href="?halaman=<?= $halamanAktif - 1; ?>" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-indigo-600 transition"><i class="fas fa-chevron-left text-xs"></i></a>
                    <?php endif; ?>

                    <?php 
                    for($i = 1; $i <= $jumlahHalaman; $i++) : 
                        if($i == $halamanAktif || ($i > $halamanAktif - 3 && $i < $halamanAktif + 3)):
                    ?>
                        <a href="?halaman=<?= $i; ?>" class="w-8 h-8 flex items-center justify-center <?= ($i == $halamanAktif) ? 'bg-indigo-600 text-white shadow-md shadow-indigo-100' : 'bg-white text-slate-500 hover:bg-slate-50'; ?> border border-slate-200 rounded-lg text-xs font-bold transition-all">
                            <?= $i; ?>
                        </a>
                    <?php endif; endfor; ?>

                    <?php if($halamanAktif < $jumlahHalaman) : ?>
                        <a href="?halaman=<?= $halamanAktif + 1; ?>" class="w-8 h-8 flex items-center justify-center bg-white border border-slate-200 rounded-lg text-slate-400 hover:text-indigo-600 transition"><i class="fas fa-chevron-right text-xs"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
function konfirmasiKembali(id) {
    Swal.fire({
        title: 'Konfirmasi Buku Kembali',
        text: "Apakah Anda yakin buku ini sudah diterima kembali?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#f97316',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Sudah!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-[2rem]'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "kembalikan.php?id=" + id;
        }
    })
}
</script>

</body>
</html>
