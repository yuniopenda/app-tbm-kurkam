<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: /app-tbm-kurkam/login.php");
    exit;
}
include(__DIR__ . '/../../config/koneksi.php');

// Ambil data anggota untuk dropdown
$anggota_query = mysqli_query($conn, "SELECT id, nama_lengkap, kode_anggota FROM m_anggota ORDER BY nama_lengkap ASC");

// Ambil data buku untuk dropdown (Hanya yang stoknya > 0)
$buku_query = mysqli_query($conn, "SELECT id, judul, stok FROM m_buku WHERE stok > 0 ORDER BY judul ASC");

if (isset($_POST['simpan'])) {
    $id_anggota   = $_POST['id_anggota'];
    $id_buku      = $_POST['id_buku'];
    $tgl_pinjam   = $_POST['tgl_pinjam'];
    
    // Hitung otomatis +7 hari untuk disimpan ke database
    $tgl_kembali  = date('Y-m-d', strtotime('+7 days', strtotime($tgl_pinjam)));
    $status       = "Dipinjam";

    mysqli_begin_transaction($conn);

    try {
        $query_pinjam = "INSERT INTO t_peminjaman (id_anggota, id_buku, tgl_pinjam, tgl_kembali, status) 
                         VALUES ('$id_anggota', '$id_buku', '$tgl_pinjam', '$tgl_kembali', '$status')";
        mysqli_query($conn, $query_pinjam);

        $query_stok = "UPDATE m_buku SET stok = stok - 1 WHERE id = '$id_buku'";
        mysqli_query($conn, $query_stok);

        mysqli_commit($conn);
        echo "<script>alert('Peminjaman Berhasil Dicatat!'); window.location='daftar.php';</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Gagal mencatat peminjaman.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pinjaman - PinjamBuku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">

    <?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

    <main class="flex-grow ml-64 p-8 flex items-center justify-center">
        <div class="max-w-4xl w-full">
            
            <a href="daftar.php" class="inline-flex items-center text-slate-500 font-bold mb-6 hover:text-indigo-600 transition">
                <i class="fas fa-arrow-left mr-2 text-sm"></i> Kembali ke Sirkulasi
            </a>

            <div class="bg-white rounded-[2rem] shadow-2xl shadow-slate-200 overflow-hidden border border-slate-50">
                <div class="bg-indigo-600 p-10 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-3xl font-black uppercase tracking-tighter">Tambah Pinjaman</h2>
                        <p class="text-indigo-100 text-sm mt-1">Sistem otomatis menghitung batas waktu 7 hari pengembalian.</p>
                    </div>
                    <div class="bg-white/10 p-5 rounded-3xl backdrop-blur-md">
                        <i class="fas fa-calendar-plus text-4xl"></i>
                    </div>
                </div>

                <form action="" method="POST" class="p-12 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <div class="col-span-1">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Nama Peminjam</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-300 group-focus-within:text-indigo-500 transition-colors">
                                    <i class="fas fa-user-tag text-lg"></i>
                                </span>
                                <select name="id_anggota" required class="w-full pl-14 pr-6 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white focus:ring-4 focus:ring-indigo-500/5 transition-all appearance-none cursor-pointer font-bold text-slate-700">
                                    <option value="">Pilih Anggota Perpustakaan</option>
                                    <?php while($agt = mysqli_fetch_assoc($anggota_query)) : ?>
                                        <option value="<?= $agt['id']; ?>"><?= $agt['nama_lengkap']; ?> (<?= $agt['kode_anggota']; ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Judul Buku</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-300 group-focus-within:text-indigo-500 transition-colors">
                                    <i class="fas fa-book text-lg"></i>
                                </span>
                                <select name="id_buku" required class="w-full pl-14 pr-6 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white focus:ring-4 focus:ring-indigo-500/5 transition-all appearance-none cursor-pointer font-bold text-slate-700">
                                    <option value="">Pilih Judul Buku</option>
                                    <?php while($buku = mysqli_fetch_assoc($buku_query)) : ?>
                                        <option value="<?= $buku['id']; ?>"><?= $buku['judul']; ?> (Stok: <?= $buku['stok']; ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Tanggal Pinjam</label>
                            <input type="date" name="tgl_pinjam" id="tgl_pinjam" value="<?= date('Y-m-d'); ?>" required 
                                   class="w-full px-8 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700">
                        </div>

                        <div>
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 ml-1 text-indigo-500">Batas Kembali (Auto +7 Hari)</label>
                            <input type="date" name="tgl_kembali" id="tgl_kembali" required readonly
                                   class="w-full px-8 py-5 bg-indigo-50 border-2 border-indigo-100 rounded-2xl outline-none text-indigo-600 font-black cursor-not-allowed">
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
                            Konfirmasi Peminjaman
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        const tglPinjam = document.getElementById('tgl_pinjam');
        const tglKembali = document.getElementById('tgl_kembali');

        function hitungOtomatis() {
            if (tglPinjam.value) {
                let d = new Date(tglPinjam.value);
                d.setDate(d.getDate() + 7);
                
                let yyyy = d.getFullYear();
                let mm = String(d.getMonth() + 1).padStart(2, '0');
                let dd = String(d.getDate()).padStart(2, '0');
                
                tglKembali.value = `${yyyy}-${mm}-${dd}`;
            }
        }

        window.onload = hitungOtomatis;
        tglPinjam.addEventListener('change', hitungOtomatis);
    </script>

</body>
</html>
