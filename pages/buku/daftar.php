<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: /app-tbm-kurkam/login.php");
    exit;
}
include(__DIR__ . '/../../config/koneksi.php');

// 1. Logika Search Buku
$keyword = "";
if (isset($_POST['cari'])) {
    $keyword = $_POST['keyword'];
    $_SESSION['keyword_buku'] = $keyword;
} else {
    $keyword = isset($_SESSION['keyword_buku']) ? $_SESSION['keyword_buku'] : "";
}

// 2. Logika Limit & Pagination
$limit = isset($_SESSION['limit_buku']) ? $_SESSION['limit_buku'] : 10;
if (isset($_GET['limit'])) {
    $limit = (int)$_GET['limit'];
    $_SESSION['limit_buku'] = $limit;
}

$halamanAktif = (isset($_GET['halaman'])) ? (int)$_GET['halaman'] : 1;
$awalData = ($limit * $halamanAktif) - $limit;

// 3. Hitung Total Data Buku
$queryTotal = mysqli_query($conn, "SELECT COUNT(*) AS total FROM m_buku 
               WHERE judul LIKE '%$keyword%' OR kode_buku LIKE '%$keyword%' OR penulis LIKE '%$keyword%'");
$dataTotal = mysqli_fetch_assoc($queryTotal);
$totalData = $dataTotal['total'];
$jumlahHalaman = ceil($totalData / $limit);

// 4. Ambil Data Buku
$data = mysqli_query($conn, "SELECT * FROM m_buku 
               WHERE judul LIKE '%$keyword%' OR kode_buku LIKE '%$keyword%' OR penulis LIKE '%$keyword%'
               ORDER BY id DESC LIMIT $awalData, $limit");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Buku - PinjamBuku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">

    <?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

    <main class="flex-grow ml-64 p-8">
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="text-4xl font-black text-slate-800 tracking-tight">Katalog Buku</h1>
                <p class="text-lg text-slate-500 mt-1">Kelola koleksi buku perpustakaan Anda.</p>
            </div>
            <a href="tambah.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl font-bold shadow-lg shadow-indigo-100 transition-all flex items-center gap-3">
                <i class="fas fa-plus-circle text-xl"></i>
                <span class="text-base">Koleksi Baru</span>
            </a>
        </div>

        <div class="flex flex-wrap gap-4 items-center justify-between mb-8">
            <form action="" method="POST" class="flex gap-3 flex-grow max-w-xl">
                <div class="relative flex-grow">
                    <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-400">
                        <i class="fas fa-search text-base"></i>
                    </span>
                    <input type="text" name="keyword" placeholder="Cari judul atau kode buku..." value="<?= $keyword; ?>"
                           class="w-full pl-14 pr-6 py-4 bg-white border border-slate-200 rounded-[1.25rem] outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 shadow-sm transition-all text-base font-medium">
                </div>
                <button type="submit" name="cari" class="bg-indigo-600 text-white px-8 py-4 rounded-[1.25rem] font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-50 transition-all text-base">Cari</button>
            </form>

            <div class="flex items-center gap-4 bg-white px-6 py-3 rounded-[1.25rem] border border-slate-200 shadow-sm">
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">Baris:</span>
                <select onchange="location = this.value;" class="bg-transparent border-none text-base font-bold text-indigo-600 outline-none cursor-pointer">
                    <option value="?limit=10" <?= ($limit == 10) ? 'selected' : ''; ?>>10 </option>
                    <option value="?limit=50" <?= ($limit == 50) ? 'selected' : ''; ?>>50 </option>
                    <option value="?limit=100" <?= ($limit == 100) ? 'selected' : ''; ?>>100 </option>
                </select>
            </div>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/60 border border-slate-200/50 overflow-hidden flex flex-col">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 text-slate-400 text-xs uppercase tracking-[0.2em] border-b border-slate-100">
                    <tr>
                        <th class="px-8 py-7 font-black text-center w-24">No</th>
                        <th class="px-8 py-7 font-black">Informasi Buku</th>
                        <th class="px-8 py-7 font-black">Penulis</th>
                        <th class="px-8 py-7 font-black text-center">Stok</th>
                        <th class="px-8 py-7 font-black text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php 
                    $no = $awalData + 1; 
                    while($row = mysqli_fetch_assoc($data)) : 
                    ?>
                    <tr class="hover:bg-indigo-50/30 transition-colors group">
                        <td class="px-8 py-8 text-center text-base font-bold text-slate-300 group-hover:text-indigo-300"><?= $no++; ?></td>
                        <td class="px-8 py-8">
                            <div class="text-xs font-black text-indigo-500 bg-indigo-100/50 px-3 py-1 rounded-md w-fit mb-2 tracking-wider uppercase"><?= $row['kode_buku']; ?></div>
                            <div class="font-bold text-slate-800 text-xl tracking-tight leading-tight"><?= $row['judul']; ?></div>
                            <div class="text-sm text-slate-400 font-medium mt-1 uppercase tracking-wide">Kategori: <span class="text-slate-500"><?= $row['kategori'] ?? 'Umum'; ?></span></div>
                        </td>
                        <td class="px-8 py-8 text-center sm:text-left">
                            <div class="text-lg font-bold text-slate-700"><?= $row['penulis'] ?? 'Anonim'; ?></div>
                            <div class="text-sm text-slate-400 font-medium mt-0.5"><?= $row['penerbit'] ?? '-'; ?></div>
                        </td>
                        <td class="px-8 py-8 text-center">
                            <?php if($row['stok'] > 0): ?>
                                <span class="inline-flex items-center gap-2 px-5 py-2 bg-emerald-100 text-emerald-700 text-sm font-black rounded-full border border-emerald-200">
                                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                                    <?= $row['stok']; ?> Tersedia
                                </span>
                            <?php else: ?>
                                <span class="px-5 py-2 bg-red-100 text-red-600 text-sm font-black rounded-full border border-red-200 italic">
                                    Stok Habis
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-8 py-8 text-center">
                            <div class="flex justify-center gap-3">
                                <a href="edit.php?id=<?= $row['id']; ?>" class="w-12 h-12 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-2xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm group-hover:shadow-indigo-200">
                                    <i class="fas fa-edit text-lg"></i>
                                </a>
                                <button onclick="hapusBuku(<?= $row['id']; ?>)" class="w-12 h-12 flex items-center justify-center bg-red-50 text-red-600 rounded-2xl hover:bg-red-600 hover:text-white transition-all shadow-sm group-hover:shadow-red-200">
                                    <i class="fas fa-trash-alt text-lg"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div class="px-10 py-8 bg-slate-50/50 border-t border-slate-100 flex justify-between items-center">
                <p class="text-sm text-slate-400 font-black uppercase tracking-[0.2em]">Total Koleksi: <span class="text-slate-800"><?= $totalData; ?></span></p>
                <div class="flex gap-2">
                    <?php for($i = 1; $i <= $jumlahHalaman; $i++) : ?>
                        <a href="?halaman=<?= $i; ?>" class="w-11 h-11 flex items-center justify-center <?= ($i == $halamanAktif) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white text-slate-500 hover:bg-slate-50'; ?> border border-slate-200 rounded-xl text-sm font-bold transition-all">
                            <?= $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
    function hapusBuku(id) {
        Swal.fire({
            title: 'Hapus Buku?',
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: '#64748b',  
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[2.5rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "hapus.php?id=" + id;
            }
        })
    }
    </script>

    <?php if(isset($_SESSION['sukses_tambah'])) : ?>
    <script>
        Swal.fire({
            title: 'Buku Ditambahkan!',
            text: 'Koleksi baru telah berhasil masuk ke database.',
            icon: 'success',
            confirmButtonColor: '#4f46e5',
            customClass: { popup: 'rounded-[2.5rem]' }
        });
    </script>
    <?php unset($_SESSION['sukses_tambah']); endif; ?>

    <?php if(isset($_SESSION['sukses_edit'])) : ?>
    <script>
        Swal.fire({
            title: 'Berhasil!',
            text: 'Data buku telah diperbarui.',
            icon: 'success',
            confirmButtonColor: '#4f46e5',
            customClass: { popup: 'rounded-[2.5rem]' }
        });
    </script>
    <?php unset($_SESSION['sukses_edit']); endif; ?>

    <?php if(isset($_SESSION['sukses_hapus'])) : ?>
    <script>
        Swal.fire({
            title: 'Terhapus!',
            text: 'Buku telah dihapus dari katalog.',
            icon: 'success',
            confirmButtonColor: '#ef4444',
            customClass: { popup: 'rounded-[2.5rem]' }
        });
    </script>
    <?php unset($_SESSION['sukses_hapus']); endif; ?>

</body>
</html>
