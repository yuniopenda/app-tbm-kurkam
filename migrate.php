<?php
// MIGRATION HELPER - Jalankan sekali, lalu hapus file ini!
// Akses: http://localhost/app-tbm-kurkam/migrate.php

include(__DIR__ . '/config/koneksi.php');
// $conn sudah tersedia dari koneksi.php

$migrations = [
    // m_anggota
    "ALTER TABLE `m_anggota` ADD COLUMN `tanggal_lahir` DATE NULL AFTER `jenis_kelamin`",
    "ALTER TABLE `m_anggota` ADD COLUMN `nik` VARCHAR(20) NULL AFTER `tanggal_lahir`",
    "ALTER TABLE `m_anggota` ADD COLUMN `kategori_usia` ENUM('dewasa','remaja','anak-anak') NOT NULL DEFAULT 'dewasa' AFTER `nik`",
    "ALTER TABLE `m_anggota` ADD COLUMN `password` VARCHAR(255) NULL AFTER `alamat`",
    "ALTER TABLE `m_anggota` MODIFY COLUMN `kode_anggota` VARCHAR(20) NOT NULL",

    // m_buku
    "ALTER TABLE `m_buku` ADD COLUMN `jenis_buku` ENUM('fisik','digital') NOT NULL DEFAULT 'fisik' AFTER `stok`",
    "ALTER TABLE `m_buku` ADD COLUMN `link_ebook` TEXT NULL AFTER `jenis_buku`",
    "ALTER TABLE `m_buku` ADD COLUMN `kategori_usia` ENUM('semua','dewasa','remaja','anak-anak') NOT NULL DEFAULT 'semua' AFTER `link_ebook`",

    // t_peminjaman
    "ALTER TABLE `t_peminjaman` ADD COLUMN `denda` INT NOT NULL DEFAULT 0 AFTER `tgl_dikembalikan`",

    // data lama default
    "UPDATE `m_anggota` SET `kategori_usia` = 'dewasa' WHERE `kategori_usia` IS NULL OR `kategori_usia` = ''",
];

$results = [];
foreach ($migrations as $sql) {
    $label = substr($sql, 0, 80) . '...';
    $r = mysqli_query($conn, $sql);
    if ($r) {
        $results[] = ['status' => 'ok', 'query' => $label, 'msg' => 'Berhasil!'];
    } else {
        $err = mysqli_error($conn);
        $isOk = (strpos($err, 'Duplicate column') !== false || strpos($err, 'already exists') !== false);
        $results[] = ['status' => $isOk ? 'skip' : 'err', 'query' => $label, 'msg' => $err];
    }
}

// Validasi kolom sekarang
$cols_anggota = [];
$r = mysqli_query($conn, "DESCRIBE m_anggota");
while ($rr = mysqli_fetch_assoc($r)) $cols_anggota[] = $rr['Field'];

$cols_buku = [];
$r = mysqli_query($conn, "DESCRIBE m_buku");
while ($rr = mysqli_fetch_assoc($r)) $cols_buku[] = $rr['Field'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Migration - TBM KurKam</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 p-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 mb-6">
            <h1 class="text-2xl font-black text-slate-800 mb-2">🔧 Migration V2 - TBM Kurung Kambing</h1>
            <p class="text-slate-500 text-sm">Jalankan sekali, lalu <b class="text-red-500">hapus file migrate.php</b> ini untuk keamanan.</p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 mb-6 space-y-3">
            <h2 class="font-black text-slate-700 mb-4">Hasil Migration:</h2>
            <?php foreach($results as $r): ?>
            <div class="flex items-start gap-3 p-3 rounded-xl <?= $r['status']==='ok' ? 'bg-emerald-50' : ($r['status']==='skip' ? 'bg-amber-50' : 'bg-red-50') ?>">
                <span class="text-lg mt-0.5"><?= $r['status']==='ok' ? '✅' : ($r['status']==='skip' ? '⏭' : '❌') ?></span>
                <div>
                    <p class="text-xs font-mono text-slate-600"><?= htmlspecialchars($r['query']) ?></p>
                    <p class="text-xs font-bold mt-1 <?= $r['status']==='ok' ? 'text-emerald-700' : ($r['status']==='skip' ? 'text-amber-700' : 'text-red-700') ?>">
                        <?= htmlspecialchars($r['msg']) ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="grid grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-3xl p-6 border border-slate-100">
                <p class="font-black text-slate-700 mb-3">✅ Kolom m_anggota:</p>
                <?php foreach($cols_anggota as $c): ?>
                <span class="inline-block bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-full m-0.5 font-mono"><?= $c ?></span>
                <?php endforeach; ?>
            </div>
            <div class="bg-white rounded-3xl p-6 border border-slate-100">
                <p class="font-black text-slate-700 mb-3">✅ Kolom m_buku:</p>
                <?php foreach($cols_buku as $c): ?>
                <span class="inline-block bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-full m-0.5 font-mono"><?= $c ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-sm text-amber-800 font-bold mb-6">
            ⚠️ Setelah migration selesai, <b>hapus file ini</b> atau akses akan tetap terbuka tanpa autentikasi!
        </div>

        <div class="flex gap-4">
            <a href="index.php" class="flex-1 text-center bg-indigo-600 text-white py-4 rounded-2xl font-black hover:bg-indigo-700 transition">
                🏠 Kembali ke Dashboard
            </a>
            <a href="?hapus=1" class="flex-1 text-center bg-red-500 text-white py-4 rounded-2xl font-black hover:bg-red-600 transition"
               onclick="return confirm('Hapus file migrate.php sekarang?')">
                🗑 Hapus File Ini
            </a>
        </div>
    </div>
</body>
</html>
<?php
if (isset($_GET['hapus'])) {
    unlink(__FILE__);
    header("Location: index.php");
    exit;
}
