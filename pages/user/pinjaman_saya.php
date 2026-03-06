<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
// Hanya anggota yang bisa akses halaman ini
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'anggota') {
    header("Location: /app-tbm-kurkam/index.php"); exit;
}
include(__DIR__ . '/../../config/koneksi.php');

$id_anggota = (int)$_SESSION['id_anggota'];
$data = mysqli_query($conn,
    "SELECT t.*, b.judul, b.kode_buku, b.jenis_buku FROM t_peminjaman t
     JOIN m_buku b ON t.id_buku = b.id
     WHERE t.id_anggota = '$id_anggota'
     ORDER BY t.id DESC");
$anggota = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM m_anggota WHERE id = '$id_anggota'"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pinjaman Saya - TBM Kurung Kambing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">
<?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

<main class="flex-grow ml-64 p-8">
    <!-- Profil card anggota -->
    <div class="bg-indigo-700 rounded-[2.5rem] p-8 text-white mb-8 flex items-center gap-8 shadow-2xl shadow-indigo-200 bg-gradient-to-r from-indigo-700 to-indigo-900">
        <div class="w-20 h-20 rounded-full bg-white/20 flex items-center justify-center text-4xl font-black">
            <?= strtoupper(substr($anggota['nama_lengkap'] ?? 'A', 0, 1)) ?>
        </div>
        <div>
            <p class="text-indigo-200 text-sm font-bold uppercase tracking-widest">Anggota TBM</p>
            <h1 class="text-3xl font-black"><?= htmlspecialchars($anggota['nama_lengkap']) ?></h1>
            <p class="text-indigo-200 mt-1"><?= $anggota['kode_anggota'] ?> · <?= ucfirst($anggota['kategori_usia'] ?? '') ?></p>
        </div>
        <div class="ml-auto text-right">
            <p class="text-indigo-200 text-xs uppercase tracking-widest font-black">Pinjaman Aktif</p>
            <?php
            $aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM t_peminjaman WHERE id_anggota='$id_anggota' AND status='Dipinjam'"));
            ?>
            <p class="text-5xl font-black"><?= $aktif['c'] ?><span class="text-indigo-300 text-xl">/3</span></p>
        </div>
    </div>

    <h2 class="text-xl font-black text-slate-700 mb-4">Riwayat Pinjaman Saya</h2>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-[10px] uppercase tracking-widest text-slate-400">
                    <tr>
                        <th class="px-6 py-5 font-black border-b border-slate-100">Buku</th>
                        <th class="px-6 py-5 font-black border-b border-slate-100">Tgl Pinjam</th>
                        <th class="px-6 py-5 font-black border-b border-slate-100">Batas Kembali</th>
                        <th class="px-6 py-5 font-black text-center border-b border-slate-100">Status</th>
                        <th class="px-6 py-5 font-black text-center border-b border-slate-100">Denda</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php $found = false; while($row = mysqli_fetch_assoc($data)): $found = true;
                    $today = date('Y-m-d');
                    $telat = max(0, (int)floor((strtotime($today) - strtotime($row['tgl_kembali'])) / 86400));
                    $denda = ($row['status'] === 'Dipinjam') ? $telat * 1000 : (int)($row['denda'] ?? 0);
                    $is_ov = ($row['status'] === 'Dipinjam' && $today > $row['tgl_kembali']);
                    $sisa  = ($row['status'] === 'Dipinjam') ? max(0, (int)floor((strtotime($row['tgl_kembali']) - strtotime($today)) / 86400)) : null;
                ?>
                <tr class="hover:bg-slate-50 transition <?= $is_ov ? 'bg-red-50/30' : '' ?>">
                    <td class="px-6 py-4">
                        <div class="text-[10px] font-black text-indigo-500"><?= $row['kode_buku'] ?></div>
                        <div class="font-bold text-slate-800"><?= htmlspecialchars($row['judul']) ?></div>
                        <?php if($row['jenis_buku'] === 'digital'): ?>
                            <span class="text-[9px] bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full font-black">💻 Digital</span>
                        <?php else: ?>
                            <span class="text-[9px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-black">📖 Fisik</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500 font-medium"><?= date('d/m/Y', strtotime($row['tgl_pinjam'])) ?></td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-bold <?= $is_ov ? 'text-red-600' : '' ?>">
                            <?= date('d/m/Y', strtotime($row['tgl_kembali'])) ?>
                        </div>
                        <?php if($is_ov): ?>
                            <div class="text-[10px] text-red-500 font-black">⚠ Terlambat <?= $telat ?> hari</div>
                        <?php elseif($sisa !== null && $sisa <= 3): ?>
                            <div class="text-[10px] text-amber-500 font-black">⏰ <?= $sisa ?> hari lagi</div>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php if($row['status'] === 'Dipinjam'): ?>
                            <span class="px-3 py-1 bg-amber-100 text-amber-700 text-[10px] font-black rounded-full">Dipinjam</span>
                        <?php else: ?>
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-full">Kembali</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php if($denda > 0): ?>
                            <span class="font-black text-red-600 text-sm">Rp <?= number_format($denda,0,',','.') ?></span>
                        <?php else: ?>
                            <span class="text-emerald-600 font-bold text-sm">✓ Lunas</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if(!$found): ?>
                <tr><td colspan="5" class="px-6 py-16 text-center text-slate-400 font-bold italic">Belum ada riwayat peminjaman.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>
