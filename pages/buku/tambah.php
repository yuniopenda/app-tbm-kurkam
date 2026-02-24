<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: /app-tbm-kurkam/login.php");
    exit;
}
include(__DIR__ . '/../../config/koneksi.php');

// --- 1. LOGIKA GENERATE KODE BUKU OTOMATIS ---
$query_kode = mysqli_query($conn, "SELECT max(kode_buku) as kodeTerbesar FROM m_buku");
$data_kode = mysqli_fetch_array($query_kode);
$kodeBuku = $data_kode['kodeTerbesar'];

// Mengambil angka dari kode buku (misal BK001 diambil 001)
$urutan = (int) substr($kodeBuku, 2, 3);
$urutan++;

// Membentuk kode baru: BK001, BK002, dst.
$huruf = "BK";
$kodeOtomatis = $huruf . sprintf("%03s", $urutan);

// --- 2. LOGIKA PROSES SIMPAN (Jika tombol diklik) ---
if (isset($_POST['simpan'])) {
    $kode_buku = $_POST['kode_buku'];
    $judul     = mysqli_real_escape_string($conn, $_POST['judul']);
    $penulis   = mysqli_real_escape_string($conn, $_POST['penulis']);
    $penerbit  = mysqli_real_escape_string($conn, $_POST['penerbit']);
    $kategori  = $_POST['kategori'];
    $stok      = $_POST['stok'];

    $query_simpan = "INSERT INTO m_buku (kode_buku, judul, penulis, penerbit, kategori, stok) 
                     VALUES ('$kode_buku', '$judul', '$penulis', '$penerbit', '$kategori', '$stok')";

    if (mysqli_query($conn, $query_simpan)) {
        $_SESSION['sukses_tambah'] = true;
        header("Location: daftar.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Buku Baru - PinjamBuku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">

    <?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

    <main class="flex-grow ml-64 p-8 flex items-center justify-center">
        <div class="max-w-3xl w-full">
            
            <a href="daftar.php" class="inline-flex items-center text-slate-500 font-bold mb-6 hover:text-indigo-600 transition gap-2 text-lg">
                <i class="fas fa-arrow-left text-sm"></i> Kembali ke Katalog
            </a>

            <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200 overflow-hidden border border-slate-50">
                <div class="bg-indigo-600 p-10 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-black uppercase tracking-tighter">Tambah Buku</h2>
                        <p class="text-indigo-100 text-lg mt-1 opacity-80">Masukkan informasi buku baru ke sistem.</p>
                    </div>
                    <div class="bg-white/10 p-6 rounded-3xl backdrop-blur-md">
                        <i class="fas fa-book-medical text-4xl"></i>
                    </div>
                </div>

                <form action="" method="POST" class="p-10 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div class="col-span-1">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Kode Buku (Sistem)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-indigo-500">
                                    <i class="fas fa-magic"></i>
                                </span>
                                <input type="text" name="kode_buku" value="<?= $kodeOtomatis; ?>" readonly
                                       class="w-full pl-14 pr-6 py-4 bg-indigo-50 border-2 border-indigo-100 rounded-2xl outline-none font-black text-indigo-600 cursor-not-allowed text-lg">
                            </div>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Kategori</label>
                            <select name="kategori" class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700">
                                <option value="Umum">Umum</option>
                                <option value="Sains">Sains</option>
                                <option value="Fiksi">Fiksi</option>
                                <option value="Sejarah">Sejarah</option>
                                <option value="Teknologi">Teknologi</option>
                                <option value="Sastra">Sastra</option>
                                <option value="Self Improvement">Self Improvement</option>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Judul Lengkap Buku</label>
                            <input type="text" name="judul" placeholder="Contoh: Belajar PHP Dasar" required
                                   class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700 text-lg">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Penulis / Pengarang</label>
                            <input type="text" name="penulis" placeholder="Nama penulis..." required
                                   class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Penerbit</label>
                            <input type="text" name="penerbit" placeholder="Nama penerbit..." required
                                   class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Jumlah Stok</label>
                            <input type="number" name="stok" value="1" min="1" required
                                   class="w-full px-6 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700 text-xl">
                        </div>
                    </div>

                   <div class="pt-8 border-t border-slate-50 flex items-center justify-end gap-4">
                        <a href="daftar.php" 
                           class="px-8 py-5 rounded-2xl font-bold text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-all flex items-center gap-2">
                            Batal
                        </a>
                        <button type="submit" name="simpan" 
                                class="flex-[1] bg-indigo-600 text-white px-10 py-5 rounded-2xl font-black shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all transform active:scale-95 flex items-center justify-center gap-3">
                            <i class="fas fa-check-circle text-lg"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>
