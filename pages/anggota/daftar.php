<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: /app-tbm-kurkam/login.php");
    exit;
}
include(__DIR__ . '/../../config/koneksi.php');

// 1. LOGIKA PENCARIAN
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$where_clause = $search != '' ? "WHERE nama_lengkap LIKE '%$search%' OR kode_anggota LIKE '%$search%'" : '';

// 2. LOGIKA PAGINATION (Pembagian Halaman)
$limit = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$page  = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

// Hitung total data anggota
$total_query = mysqli_query($conn, "SELECT id FROM m_anggota $where_clause");
$total_data  = mysqli_num_rows($total_query);
$pages       = ceil($total_data / $limit);

// 3. AMBIL DATA ANGGOTA
$daftar_anggota = mysqli_query($conn, "SELECT * FROM m_anggota $where_clause ORDER BY id DESC LIMIT $start, $limit");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Anggota - LibraTech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans flex min-h-screen">

    <?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

    <main class="flex-grow ml-64 p-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Daftar Anggota</h2>
                <p class="text-gray-500 text-sm">Kelola data seluruh anggota perpustakaan</p>
            </div>
            
            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                <a href="tambah.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold shadow-lg transition transform active:scale-95 flex items-center justify-center gap-2">
                    <i class="fas fa-user-plus"></i> Tambah Anggota
                </a>

                <form action="" method="GET" class="flex gap-2">
                    <input type="hidden" name="per_page" value="<?= $limit ?>">
                    <div class="relative flex-grow">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="<?= $search ?>" placeholder="Cari nama atau kode..." 
                               class="pl-11 pr-4 py-2.5 w-full md:w-64 bg-white border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm transition">
                    </div>
                    <button type="submit" class="bg-gray-800 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-black transition shadow-md">
                        Cari
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
    <thead>
        <tr class="text-xs font-bold text-gray-400 uppercase tracking-wider border-b border-gray-50 bg-gray-50/50">
            <th class="px-6 py-4">NAMA</th>
            <th class="px-6 py-4">KODE ANGGOTA</th>
            <th class="px-6 py-4">JENIS KELAMIN</th>
            <th class="px-6 py-4">NO HP AKTIF</th>
            <th class="px-6 py-4">ALAMAT</th>
            <th class="px-6 py-4 text-center">AKSI</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-50 text-sm">
        <?php while($row = mysqli_fetch_assoc($daftar_anggota)) : ?>
        <tr class="hover:bg-gray-50/50 transition duration-200">
            <td class="px-6 py-4 font-bold text-gray-800">
                <?= $row['nama_lengkap']; ?>
            </td>

            <td class="px-6 py-4">
                <span class="text-indigo-600 font-bold"><?= $row['kode_anggota']; ?></span>
            </td>

            <td class="px-6 py-4">
                <?php if($row['jenis_kelamin'] == 'Laki-laki'): ?>
                    <span class="bg-blue-50 text-blue-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase">Laki-laki</span>
                <?php else: ?>
                    <span class="bg-pink-50 text-pink-600 text-[10px] font-bold px-3 py-1 rounded-full uppercase">Perempuan</span>
                <?php endif; ?>
            </td>

            <td class="px-6 py-4 text-gray-600">
                <?= $row['telepon']; ?>
            </td>

            <td class="px-6 py-4 text-gray-500 italic">
                <?= $row['alamat']; ?>
            </td>

            <td class="px-6 py-4 text-center">
                <div class="flex justify-center gap-4">
                    <a href="edit.php?id=<?= $row['id']; ?>" class="text-blue-400 hover:text-blue-600">
                        <i class="far fa-edit text-lg"></i>
                    </a>
                    <a href="hapus.php?id=<?= $row['id']; ?>" onclick="return confirm('Hapus data ini?')" class="text-red-400 hover:text-red-600">
                        <i class="fas fa-trash-alt text-lg"></i>
                    </a>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
            </div>

            <div class="p-6 border-t border-gray-50 flex flex-col md:flex-row justify-between items-center gap-4 bg-gray-50/30 text-sm">
                <div class="flex items-center gap-3 text-gray-500">
                    <span>Tampilkan</span>
                    <select onchange="location = this.value;" class="bg-white border border-gray-200 rounded-lg px-2 py-1.5 outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="?search=<?= $search ?>&per_page=10&halaman=1" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                        <option value="?search=<?= $search ?>&per_page=25&halaman=1" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
                        <option value="?search=<?= $search ?>&per_page=50&halaman=1" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                    </select>
                </div>

                <div class="flex items-center gap-1">
                    <?php if($page > 1): ?>
                        <a href="?search=<?= $search ?>&per_page=<?= $limit ?>&halaman=<?= $page - 1 ?>" class="px-3 py-2 bg-white border border-gray-200 rounded-xl text-gray-400 hover:text-indigo-600 transition shadow-sm"><i class="fas fa-chevron-left text-xs"></i></a>
                    <?php endif; ?>

                    <?php for($i=1; $i<=$pages; $i++): ?>
                        <a href="?search=<?= $search ?>&per_page=<?= $limit ?>&halaman=<?= $i ?>" 
                           class="px-4 py-2 rounded-xl font-bold transition <?= ($i == $page) ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600 bg-white border border-gray-200' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if($page < $pages): ?>
                        <a href="?search=<?= $search ?>&per_page=<?= $limit ?>&halaman=<?= $page + 1 ?>" class="px-3 py-2 bg-white border border-gray-200 rounded-xl text-gray-400 hover:text-indigo-600 transition shadow-sm"><i class="fas fa-chevron-right text-xs"></i></a>
                    <?php endif; ?>
                </div>

                <div class="text-gray-400 font-medium">
                    Total Anggota: <span class="text-indigo-600 font-bold"><?= $total_data ?></span>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
