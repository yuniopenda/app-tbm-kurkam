<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
include(__DIR__ . '/../../config/koneksi.php');

if (!isset($_GET['id'])) { header("Location: daftar.php"); exit; }
$id_pinjam = (int)$_GET['id'];

// Ambil data peminjaman
$pinjam = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT t.*, m.nama_lengkap, b.judul, b.kode_buku FROM t_peminjaman t
     JOIN m_anggota m ON t.id_anggota = m.id
     JOIN m_buku b ON t.id_buku = b.id
     WHERE t.id = '$id_pinjam' AND t.status = 'Dipinjam'"));

if (!$pinjam) { echo "<script>alert('Data tidak ditemukan atau sudah dikembalikan!'); window.location='daftar.php';</script>"; exit; }

// Hitung denda
$today         = date('Y-m-d');
$tgl_jatuh     = $pinjam['tgl_kembali'];
$selisih_hari  = (int)floor((strtotime($today) - strtotime($tgl_jatuh)) / 86400);
$hari_telat    = max(0, $selisih_hari);
$denda         = $hari_telat * 1000; 

// Jika ada denda dan belum dikirim metode pembayarannya, tampilkan form
if ($denda > 0 && !isset($_POST['proses_kembali'])) {
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pembayaran Denda - TBM Kurung Kambing</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body class="bg-slate-100 font-sans flex items-center justify-center min-h-screen p-4">
        <div class="max-w-md w-full bg-white rounded-[2.5rem] shadow-2xl shadow-indigo-100 overflow-hidden border border-slate-50">
            <div class="bg-indigo-600 p-8 text-white text-center">
                <div class="w-20 h-20 bg-white/20 rounded-3xl mx-auto flex items-center justify-center text-4xl mb-4">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
                <h2 class="text-2xl font-black uppercase tracking-tighter">Pembayaran Denda</h2>
                <p class="text-indigo-100 text-sm mt-1">Harap pilih metode pembayaran untuk melanjutkan.</p>
            </div>
            
            <form action="" method="POST" class="p-8 space-y-6">
                <input type="hidden" name="proses_kembali" value="1">
                
                <div class="bg-rose-50 border-2 border-rose-100 rounded-2xl p-4 flex flex-col items-center">
                    <p class="text-xs font-black text-rose-400 uppercase tracking-widest mb-1">Total Denda</p>
                    <h3 class="text-3xl font-black text-rose-600">Rp <?= number_format($denda, 0, ',', '.') ?></h3>
                    <p class="text-xs font-bold text-rose-400 mt-1"><?= $hari_telat ?> Hari Keterlambatan</p>
                </div>

                <div class="space-y-3">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Metode Pembayaran</label>
                    
                    <label class="flex items-center gap-4 p-4 rounded-2xl border-2 border-slate-100 hover:border-indigo-100 transition cursor-pointer group">
                        <input type="radio" name="metode_pembayaran" value="Tunai" checked class="w-5 h-5 accent-indigo-600">
                        <div class="flex-grow">
                            <span class="block font-black text-slate-700 group-hover:text-indigo-600">💵 Tunai (Cash)</span>
                            <span class="text-[10px] text-slate-400 uppercase font-bold">Bayar langsung di kasir</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-4 p-4 rounded-2xl border-2 border-slate-100 hover:border-indigo-100 transition cursor-pointer group">
                        <input type="radio" name="metode_pembayaran" value="Non-tunai" class="w-5 h-5 accent-indigo-600">
                        <div class="flex-grow">
                            <span class="block font-black text-slate-700 group-hover:text-indigo-600">💳 Non-Tunai</span>
                            <span class="text-[10px] text-slate-400 uppercase font-bold">Transfer / QRIS / Dompet Digital</span>
                        </div>
                    </label>
                </div>

                <div class="pt-4 flex gap-3">
                    <a href="daftar.php" class="flex-1 text-center py-4 text-slate-400 font-bold hover:bg-slate-50 rounded-2xl transition">Batal</a>
                    <button type="submit" class="flex-[2] bg-indigo-600 text-white py-4 rounded-2xl font-black shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95">
                        Proses & Selesai
                    </button>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Proses pengembalian (Baik yang ada denda & sudah pilih metode, atau yang tidak ada denda)
$id_buku = $pinjam['id_buku'];
$metode  = $_POST['metode_pembayaran'] ?? 'Tunai';

mysqli_begin_transaction($conn);
try {
    mysqli_query($conn, "UPDATE t_peminjaman 
                        SET status='Kembali', 
                            tgl_dikembalikan='$today', 
                            denda='$denda', 
                            metode_pembayaran='$metode',
                            updated_at=NOW() 
                        WHERE id='$id_pinjam'");
    mysqli_query($conn, "UPDATE m_buku SET stok = stok + 1 WHERE id='$id_buku'");
    mysqli_commit($conn);

    $_SESSION['info_kembali'] = [
        'nama'   => $pinjam['nama_lengkap'],
        'judul'  => $pinjam['judul'],
        'denda'  => $denda,
        'telat'  => $hari_telat,
    ];
    header("Location: daftar.php"); exit;

} catch(Exception $e) {
    mysqli_rollback($conn);
    echo "<script>alert('Gagal memproses pengembalian.'); window.location='daftar.php';</script>";
    exit;
}
