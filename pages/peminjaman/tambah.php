<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
include(__DIR__ . '/../../config/koneksi.php');

// Tambah pinjaman baru
if (isset($_POST['simpan'])) {
    $id_anggota = (int)$_POST['id_anggota'];
    $id_buku    = (int)$_POST['id_buku'];
    $tgl_pinjam = $_POST['tgl_pinjam'];
    $tgl_kembali= date('Y-m-d', strtotime('+7 days', strtotime($tgl_pinjam)));

    // Cek batas maksimal 3 buku aktif
    $cek_limit = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) AS total FROM t_peminjaman WHERE id_anggota='$id_anggota' AND status='Dipinjam'"));
    if ((int)$cek_limit['total'] >= 3) {
        $error_limit = true;
    } else {
        mysqli_begin_transaction($conn);
        try {
            mysqli_query($conn, "INSERT INTO t_peminjaman (id_anggota, id_buku, tgl_pinjam, tgl_kembali, status, created_at)
                                 VALUES ('$id_anggota','$id_buku','$tgl_pinjam','$tgl_kembali','Dipinjam', NOW())");
            mysqli_query($conn, "UPDATE m_buku SET stok = stok - 1 WHERE id='$id_buku' AND stok > 0");
            mysqli_commit($conn);
            echo "<script>alert('Peminjaman berhasil dicatat!'); window.location='daftar.php';</script>";
            exit;
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_db = 'Gagal menyimpan data peminjaman.';
        }
    }
}

// Data dropdown
$anggota_query = mysqli_query($conn, "SELECT id, nama_lengkap, kode_anggota, kategori_usia, telepon, alamat FROM m_anggota ORDER BY nama_lengkap ASC");
$buku_query    = mysqli_query($conn, "SELECT id, judul, kode_buku, stok, kategori_usia FROM m_buku WHERE stok > 0 ORDER BY judul ASC");

// Convert buku ke array untuk JS filter
$all_books = [];
while($b = mysqli_fetch_assoc($buku_query)) $all_books[] = $b;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Peminjaman - TBM Kurung Kambing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">
<?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

<main class="flex-grow lg:ml-64 p-4 lg:p-8 pt-16 lg:pt-8 flex items-start justify-center">
    <div class="max-w-3xl w-full">
        <a href="daftar.php" class="inline-flex items-center text-slate-500 font-bold mb-6 hover:text-indigo-600 transition gap-2">
            <i class="fas fa-arrow-left text-sm"></i> Kembali ke Daftar Peminjaman
        </a>

        <?php if(isset($error_limit)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-2xl text-red-700 font-bold mb-6 flex items-center gap-3">
            <i class="fas fa-ban text-xl"></i>
            <div>
                <p class="font-black">Batas Peminjaman Tercapai!</p>
                <p class="text-sm font-normal">Anggota ini sudah meminjam 3 buku. Buku harus dikembalikan terlebih dahulu.</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="bg-indigo-600 p-6 lg:p-10 text-white flex items-center justify-between">
                <div>
                    <h2 class="text-xl lg:text-3xl font-black uppercase">Tambah Pinjaman</h2>
                    <p class="text-indigo-200 text-sm mt-1">Maks. 3 buku/anggota · Batas kembali otomatis +7 hari · Denda Rp 1.000/hari</p>
                </div>
                <div class="bg-white/10 p-5 rounded-3xl"><i class="fas fa-calendar-plus text-4xl"></i></div>
            </div>

            <form action="" method="POST" class="p-6 lg:p-10 space-y-5 lg:space-y-6">
                <!-- Anggota -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Pilih Anggota *</label>
                    <select name="id_anggota" id="id_anggota" onchange="updateAnggotaInfo(this)" required
                            class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                        <option value="">-- Pilih Anggota --</option>
                        <?php mysqli_data_seek($anggota_query, 0); while($ag = mysqli_fetch_assoc($anggota_query)): ?>
                        <option value="<?= $ag['id'] ?>"
                                data-kategori="<?= $ag['kategori_usia'] ?>"
                                data-telepon="<?= htmlspecialchars($ag['telepon']) ?>"
                                data-alamat="<?= htmlspecialchars($ag['alamat'] ?? '') ?>">
                            <?= htmlspecialchars($ag['nama_lengkap']) ?> [<?= $ag['kode_anggota'] ?>]
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <!-- Info anggota -->
                    <div id="info_anggota" class="hidden mt-3 p-4 bg-indigo-50 rounded-2xl space-y-1">
                        <p class="text-xs font-black text-indigo-600 uppercase tracking-widest">Info Peminjam</p>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div><span class="text-slate-400 text-xs">Kategori:</span> <span id="info_kategori" class="font-bold text-slate-700"></span></div>
                            <div><span class="text-slate-400 text-xs">Telepon:</span> <span id="info_telepon" class="font-bold text-slate-700"></span></div>
                            <div class="col-span-2"><span class="text-slate-400 text-xs">Alamat:</span> <span id="info_alamat" class="font-bold text-slate-700"></span></div>
                            <div class="col-span-2"><span class="text-slate-400 text-xs">Pinjaman aktif:</span> <span id="info_pinjaman" class="font-bold text-slate-700">...</span></div>
                        </div>
                    </div>
                </div>

                <!-- Buku -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Pilih Buku *</label>
                    <select name="id_buku" id="id_buku" required
                            class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                        <option value="">-- Pilih Buku --</option>
                        <?php foreach($all_books as $bk): ?>
                        <option value="<?= $bk['id'] ?>" data-usia="<?= $bk['kategori_usia'] ?>">
                            [<?= $bk['kode_buku'] ?>] <?= htmlspecialchars($bk['judul']) ?> (Stok: <?= $bk['stok'] ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tanggal -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Tanggal Pinjam</label>
                        <input type="date" name="tgl_pinjam" id="tgl_pinjam" value="<?= date('Y-m-d') ?>" required onchange="hitungKembali()"
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-indigo-500 uppercase tracking-widest mb-2">Batas Kembali (auto +7 hari)</label>
                        <input type="text" id="tgl_kembali_preview" readonly
                               class="w-full px-5 py-4 bg-indigo-50 border-2 border-indigo-100 rounded-2xl font-black text-indigo-600 cursor-not-allowed">
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-50 flex flex-col sm:flex-row gap-3">
                    <a href="daftar.php" class="px-8 py-4 rounded-2xl font-bold text-slate-400 hover:bg-slate-50 transition text-center">Batal</a>
                    <button type="submit" name="simpan"
                            class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95 flex items-center justify-center gap-2">
                        <i class="fas fa-check-circle"></i> Konfirmasi Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
const allBooks = <?= json_encode($all_books) ?>;

function hitungKembali() {
    const tgl = document.getElementById('tgl_pinjam').value;
    if (!tgl) return;
    const d = new Date(tgl);
    d.setDate(d.getDate() + 7);
    document.getElementById('tgl_kembali_preview').value =
        d.toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' });
}

async function updateAnggotaInfo(sel) {
    const opt = sel.selectedOptions[0];
    const info = document.getElementById('info_anggota');
    if (!opt.value) { info.classList.add('hidden'); return; }

    const kategori = opt.dataset.kategori || 'semua';
    document.getElementById('info_kategori').textContent = kategori.charAt(0).toUpperCase() + kategori.slice(1);
    document.getElementById('info_telepon').textContent = opt.dataset.telepon || '-';
    document.getElementById('info_alamat').textContent  = opt.dataset.alamat || '-';
    info.classList.remove('hidden');

    // Cek pinjaman aktif
    try {
        const r = await fetch(`/app-tbm-kurkam/api/cek_pinjaman.php?id_anggota=${opt.value}`);
        const d = await r.json();
        const el = document.getElementById('info_pinjaman');
        el.textContent = `${d.total}/3 buku`;
        el.className = d.total >= 3 ? 'font-black text-red-600' : 'font-bold text-emerald-600';
    } catch(e) {}

    // Filter buku sesuai kategori anggota
    filterBuku(kategori);
}

function filterBuku(kategoriAnggota) {
    const sel = document.getElementById('id_buku');
    sel.innerHTML = '<option value="">-- Pilih Buku --</option>';
    allBooks.forEach(b => {
        // Tampilkan buku jika cocok atau kategori buku = semua
        if (b.kategori_usia === 'semua' || b.kategori_usia === kategoriAnggota || !kategoriAnggota) {
            const opt = document.createElement('option');
            opt.value = b.id;
            opt.textContent = `[${b.kode_buku}] ${b.judul} (Stok: ${b.stok})`;
            sel.appendChild(opt);
        }
    });
}

window.onload = hitungKembali;
</script>
</body>
</html>
