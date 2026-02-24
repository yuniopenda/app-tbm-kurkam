<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: /app-tbm-kurkam/login.php");
    exit;
}
include(__DIR__ . '/../../config/koneksi.php');

// 1. LOGIKA GENERATE KODE OTOMATIS
$tahun_sekarang = date('Y'); 

$query_kode = mysqli_query($conn, "SELECT max(kode_anggota) as kode_terbesar 
                                   FROM m_anggota 
                                   WHERE kode_anggota LIKE '$tahun_sekarang%'");
$data = mysqli_fetch_array($query_kode);
$kode_terakhir = $data['kode_terbesar'];

if ($kode_terakhir) {
    // Mengambil 3 digit terakhir dari format YYYYXXX (misal 2024001)
    $urutan = (int) substr($kode_terakhir, 4, 3);
    $urutan++;
} else {
    $urutan = 1;
}

$kode_otomatis = $tahun_sekarang . sprintf("%03s", $urutan);

// 2. LOGIKA SIMPAN DATA
if (isset($_POST['simpan'])) {
    $kode_anggota   = mysqli_real_escape_string($conn, $_POST['kode_anggota']);
    $nama_lengkap   = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $jenis_kelamin  = $_POST['jenis_kelamin'];
    $telepon        = mysqli_real_escape_string($conn, $_POST['telepon']);
    $alamat         = mysqli_real_escape_string($conn, $_POST['alamat']);
    $tanggal_daftar = $_POST['tanggal_daftar']; 

    $oleh = $_SESSION['user'] ?? $_SESSION['nama_lengkap'];

    if (!$oleh) {
        echo "<script>alert('Gagal! Sesi login tidak ditemukan. Silakan login ulang.'); window.location='/app-tbm-kurkam/login.php';</script>";
        exit;
    }

    try {
        $query = "INSERT INTO m_anggota (kode_anggota, nama_lengkap, jenis_kelamin, telepon, alamat, tanggal_daftar, created_by) 
                  VALUES ('$kode_anggota', '$nama_lengkap', '$jenis_kelamin', '$telepon', '$alamat', '$tanggal_daftar', '$oleh')";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Anggota Berhasil Didaftarkan oleh $oleh!'); window.location='daftar.php';</script>";
        }
    } catch (mysqli_sql_exception $e) {
        $pesan_error = $e->getMessage();
        echo "<script>alert('Gagal Simpan: $pesan_error'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Anggota - LibraTech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans flex min-h-screen">

    <?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

    <main class="flex-grow ml-64 p-8 flex items-center justify-center">
        <div class="max-w-2xl w-full">
            <a href="daftar.php" class="inline-flex items-center text-indigo-600 font-bold mb-6 hover:text-indigo-800 transition">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Anggota
            </a>

            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
                <div class="bg-indigo-600 p-8 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold uppercase tracking-wider">Registrasi Anggota</h2>
                        <p class="text-indigo-100 text-sm opacity-80">Masukkan informasi detail anggota baru</p>
                    </div>
                    <div class="bg-white/20 p-4 rounded-2xl">
                        <i class="fas fa-user-plus text-3xl"></i>
                    </div>
                </div>

                <form action="" method="POST" class="p-10 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Kode Anggota</label>
                            <div class="relative">
                                <i class="fas fa-id-badge absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="kode_anggota" value="<?= $kode_otomatis; ?>" readonly
                                    class="pl-11 pr-4 py-4 w-full bg-gray-50 border-2 border-gray-100 rounded-2xl outline-none font-bold text-indigo-600">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Jenis Kelamin</label>
                            <div class="relative">
                                <i class="fas fa-venus-mars absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                                <select name="jenis_kelamin" required
                                    class="w-full pl-12 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl outline-none focus:border-indigo-500 transition appearance-none cursor-pointer text-gray-700">
                                    <option value="">Pilih Gender</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Nama Lengkap</label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="nama_lengkap" required placeholder="Nama Sesuai KTP"
                                    class="w-full pl-12 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl outline-none focus:border-indigo-500 transition">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Nomor HP Aktif</label>
                            <div class="relative">
                                <i class="fas fa-phone absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="telepon" required placeholder="0812xxxx"
                                    class="w-full pl-12 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl outline-none focus:border-indigo-500 transition">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Tanggal Mendaftar</label>
                            <div class="relative">
                                <i class="fas fa-calendar-day absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="date" name="tanggal_daftar" required value="<?= date('Y-m-d'); ?>"
                                    class="w-full pl-12 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl outline-none focus:border-indigo-500 transition text-gray-700">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Alamat Lengkap</label>
                            <div class="relative">
                                <i class="fas fa-map-marker-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="alamat" required placeholder="Kec, Kota, Prov"
                                    class="w-full pl-12 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl outline-none focus:border-indigo-500 transition">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex gap-4">
                        <button type="submit" name="simpan" 
                                class="flex-[2] bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-100 transition transform active:scale-95 flex items-center justify-center gap-3">
                            <i class="fas fa-save"></i> Simpan Anggota
                        </button>
                        <a href="daftar.php" class="flex-1 bg-gray-100 text-gray-500 py-4 rounded-2xl font-bold hover:bg-gray-200 transition text-center flex items-center justify-center">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
            <p class="text-center text-gray-400 text-xs mt-8 italic">LibraTech © 2026</p>
        </div>
    </main>
</body>
</html>
