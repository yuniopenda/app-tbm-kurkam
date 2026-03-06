<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'anggota') { header("Location: /app-tbm-kurkam/pages/user/katalog.php"); exit; }

include(__DIR__ . '/../../config/koneksi.php');

$pesan_sukses = '';
$pesan_error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_excel'])) {
    $file    = $_FILES['file_excel'];
    $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $tmpPath = $file['tmp_name'];

    if (!in_array($ext, ['csv'])) {
        $pesan_error = 'Format file harus CSV. Simpan template Excel sebagai CSV terlebih dahulu.';
    } else {
        $handle = fopen($tmpPath, 'r');
        $header = fgetcsv($handle, 0, ';'); // Baca baris header
        if (!$header) $header = fgetcsv($handle, 0, ',');

        $sukses = 0;
        $gagal  = 0;
        $duplikat = 0;
        $by = $_SESSION['user'] ?? 'admin';

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < 2) { // skip baris kosong
                $row2 = explode(',', implode(';', $row));
                if (count($row2) >= 2) $row = $row2;
                else { continue; }
            }

            // Mapping kolom CSV: kode_buku, judul, penulis, penerbit, stok, kategori, jenis_buku, kategori_usia, link_ebook
            $kode_buku    = trim($row[0] ?? '');
            $judul        = trim($row[1] ?? '');
            $penulis      = trim($row[2] ?? '');
            $penerbit     = trim($row[3] ?? '');
            $stok         = (int)trim($row[4] ?? 1);
            $kategori     = trim($row[5] ?? 'Umum');
            $jenis_buku   = strtolower(trim($row[6] ?? 'fisik'));
            $kat_usia     = strtolower(trim($row[7] ?? 'semua'));
            $link_ebook   = trim($row[8] ?? '');

            if (empty($kode_buku) || empty($judul)) continue;

            // Skip baris petunjuk
            if (stripos($kode_buku, 'kode') !== false || stripos($judul, 'judul') !== false) continue;

            $kode_buku  = mysqli_real_escape_string($conn, $kode_buku);
            $judul      = mysqli_real_escape_string($conn, $judul);
            $penulis    = mysqli_real_escape_string($conn, $penulis);
            $penerbit   = mysqli_real_escape_string($conn, $penerbit);
            $kategori   = mysqli_real_escape_string($conn, $kategori);
            $link_ebook = mysqli_real_escape_string($conn, $link_ebook);
            $jenis_buku = in_array($jenis_buku, ['fisik','digital']) ? $jenis_buku : 'fisik';
            $kat_usia   = in_array($kat_usia, ['semua','dewasa','remaja','anak-anak']) ? $kat_usia : 'semua';

            // Cek duplikat kode
            $cek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM m_buku WHERE kode_buku = '$kode_buku'"));
            if ($cek) { $duplikat++; continue; }

            $q = "INSERT INTO m_buku (kode_buku, judul, penulis, penerbit, stok, kategori, jenis_buku, link_ebook, kategori_usia, created_by, created_at)
                  VALUES ('$kode_buku','$judul','$penulis','$penerbit','$stok','$kategori','$jenis_buku','$link_ebook','$kat_usia','$by', NOW())";
            if (mysqli_query($conn, $q)) $sukses++;
            else $gagal++;
        }
        fclose($handle);

        $pesan_sukses = "Import selesai: <b>$sukses buku berhasil</b>" .
                        ($duplikat ? ", $duplikat dilewati (kode duplikat)" : '') .
                        ($gagal    ? ", $gagal gagal"                        : '') . ".";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Import Buku - TBM Kurung Kambing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">
<?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

<main class="flex-grow ml-64 p-8 flex items-center justify-center">
    <div class="max-w-2xl w-full">
        <a href="daftar.php" class="inline-flex items-center text-slate-500 font-bold mb-6 hover:text-indigo-600 transition gap-2">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Buku
        </a>

        <?php if($pesan_sukses): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-6 py-4 rounded-2xl font-bold mb-6">
            <i class="fas fa-check-circle mr-2"></i> <?= $pesan_sukses ?>
        </div>
        <?php endif; ?>
        <?php if($pesan_error): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-2xl font-bold mb-6">
            <i class="fas fa-exclamation-circle mr-2"></i> <?= $pesan_error ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-600 to-emerald-700 p-10 text-white">
                <h2 class="text-3xl font-black tracking-tight">Import Buku</h2>
                <p class="text-emerald-100 mt-1">Upload file CSV untuk menambah buku secara massal</p>
            </div>

            <div class="p-10 space-y-6">
                <!-- Info format -->
                <div class="bg-slate-50 rounded-2xl p-5 text-sm text-slate-600 border border-slate-100">
                    <p class="font-black text-slate-700 mb-2">📋 Format CSV yang diterima:</p>
                    <code class="text-xs bg-white border border-slate-200 p-3 rounded-xl block font-mono">
                        kode_buku ; judul ; penulis ; penerbit ; stok ; kategori ; jenis_buku ; kategori_usia ; link_ebook
                    </code>
                    <ul class="mt-3 space-y-1 text-xs">
                        <li>• <b>jenis_buku:</b> fisik / digital</li>
                        <li>• <b>kategori_usia:</b> semua / dewasa / remaja / anak-anak</li>
                        <li>• <b>link_ebook:</b> isi URL hanya jika digital, kosongkan jika fisik</li>
                    </ul>
                    <a href="template_excel.php" class="mt-4 inline-flex items-center gap-2 bg-emerald-600 text-white px-5 py-2 rounded-xl text-xs font-bold hover:bg-emerald-700 transition">
                        <i class="fas fa-download"></i> Download Template Excel
                    </a>
                </div>

                <!-- Form upload -->
                <form method="POST" enctype="multipart/form-data" class="space-y-5">
                    <div>
                        <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3">File CSV</label>
                        <div class="border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center hover:border-emerald-300 transition cursor-pointer"
                             onclick="document.getElementById('file_excel').click()">
                            <i class="fas fa-file-csv text-4xl text-emerald-400 mb-3"></i>
                            <p class="font-bold text-slate-600" id="filename-label">Klik untuk pilih file CSV</p>
                            <p class="text-xs text-slate-400 mt-1">Format: .csv (pisahkan kolom dengan titik koma)</p>
                            <input type="file" name="file_excel" id="file_excel" accept=".csv"
                                   class="hidden" onchange="document.getElementById('filename-label').textContent = this.files[0]?.name || 'Klik untuk pilih file'">
                        </div>
                    </div>
                    <button type="submit"
                            class="w-full bg-emerald-600 text-white py-5 rounded-2xl font-black text-lg hover:bg-emerald-700 transition shadow-lg shadow-emerald-100 flex items-center justify-center gap-3">
                        <i class="fas fa-upload"></i> Proses Import
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>
</body>
</html>
