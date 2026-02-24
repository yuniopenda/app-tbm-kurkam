<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: /app-tbm-kurkam/login.php");
    exit;
}
include(__DIR__ . '/../../config/koneksi.php');

// 1. Ambil ID dari URL dan validasi data
if (!isset($_GET['id'])) {
    header("Location: daftar.php");
    exit;
}

$id = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM m_buku WHERE id = '$id'");
$buku = mysqli_fetch_assoc($query);

// Jika buku tidak ditemukan
if (!$buku) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='daftar.php';</script>";
    exit;
}

// 2. Logika Update Data (Proses ketika form disubmit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul      = mysqli_real_escape_string($conn, $_POST['judul']);
    $penulis    = mysqli_real_escape_string($conn, $_POST['penulis']);
    $penerbit   = mysqli_real_escape_string($conn, $_POST['penerbit']);
    $kategori   = $_POST['kategori'];
    $stok       = $_POST['stok'];

    $update_query = "UPDATE m_buku SET 
                    judul = '$judul', 
                    penulis = '$penulis', 
                    penerbit = '$penerbit', 
                    kategori = '$kategori', 
                    stok = '$stok' 
                    WHERE id = '$id'";

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['sukses_edit'] = true;
        header("Location: daftar.php");
        exit;
    } else {
        $error_db = mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Koleksi - PinjamBuku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">

    <?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

    <main class="flex-grow ml-64 p-8 flex items-center justify-center">
        <div class="max-w-4xl w-full">
            
            <a href="daftar.php" class="inline-flex items-center text-slate-500 font-bold mb-6 hover:text-indigo-600 transition gap-2 text-lg">
                <i class="fas fa-arrow-left"></i> Kembali ke Katalog
            </a>

            <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-50">
                <div class="bg-indigo-600 p-10 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-black uppercase tracking-tighter italic">Edit Data Buku</h2>
                        <p class="text-indigo-100 text-lg mt-1">Sesuaikan informasi buku: <strong><?= $buku['kode_buku']; ?></strong></p>
                    </div>
                    <div class="bg-white/10 p-6 rounded-3xl backdrop-blur-md">
                        <i class="fas fa-edit text-4xl"></i>
                    </div>
                </div>

                <form id="formEdit" action="" method="POST" class="p-12 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <div class="col-span-1">
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Kode Buku (Terkunci)</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-300">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="text" value="<?= $buku['kode_buku']; ?>" readonly
                                       class="w-full pl-14 pr-6 py-5 bg-slate-50 border-2 border-slate-100 rounded-2xl outline-none font-bold text-slate-400 cursor-not-allowed">
                            </div>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Kategori</label>
                            <select name="kategori" class="w-full px-6 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700">
                                <option value="Umum" <?= $buku['kategori'] == 'Umum' ? 'selected' : ''; ?>>Umum</option>
                                <option value="Sains" <?= $buku['kategori'] == 'Sains' ? 'selected' : ''; ?>>Sains</option>
                                <option value="Fiksi" <?= $buku['kategori'] == 'Fiksi' ? 'selected' : ''; ?>>Fiksi</option>
                                <option value="Sejarah" <?= $buku['kategori'] == 'Sejarah' ? 'selected' : ''; ?>>Sejarah</option>
                                <option value="Teknologi" <?= $buku['kategori'] == 'Teknologi' ? 'selected' : ''; ?>>Teknologi</option>
                                <option value="Sastra" <?= $buku['kategori'] == 'Sastra' ? 'selected' : ''; ?>>Sastra</option>
                                <option value="Self Improvement" <?= $buku['kategori'] == 'Self Improvement' ? 'selected' : ''; ?>>Self Improvement</option>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Judul Lengkap</label>
                            <input type="text" name="judul" value="<?= htmlspecialchars($buku['judul']); ?>" required
                                   class="w-full px-8 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700 text-xl">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Penulis</label>
                            <input type="text" name="penulis" value="<?= htmlspecialchars($buku['penulis']); ?>" required
                                   class="w-full px-8 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Penerbit</label>
                            <input type="text" name="penerbit" value="<?= htmlspecialchars($buku['penerbit']); ?>" required
                                   class="w-full px-8 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Stok Tersedia</label>
                            <input type="number" name="stok" value="<?= $buku['stok']; ?>" required
                                   class="w-full px-8 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700 text-xl">
                        </div>
                    </div>

                    <div class="pt-8 border-t border-slate-50 flex items-center justify-end gap-4">
                        <a href="daftar.php" class="px-8 py-5 rounded-2xl font-bold text-slate-400 hover:text-slate-600 transition-all">Batal</a>
                        <button type="button" onclick="konfirmasiUpdate()" 
                                class="flex-grow bg-indigo-600 text-white px-10 py-5 rounded-2xl font-black shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all transform active:scale-95 flex items-center justify-center gap-3 text-lg">
                            <i class="fas fa-save"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
    function konfirmasiUpdate() {
        Swal.fire({
            title: 'Simpan Perubahan?',
            text: "Data buku akan diperbarui secara permanen.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[2.5rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formEdit').submit();
            }
        })
    }
    </script>

    <?php if(isset($error_db)): ?>
    <script>
        Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan: <?= $error_db; ?>',
            icon: 'error',
            confirmButtonColor: '#ef4444',
            customClass: { popup: 'rounded-[2.5rem]' }
        });
    </script>
    <?php endif; ?>

</body>
</html>
