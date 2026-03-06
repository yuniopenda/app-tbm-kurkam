<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'anggota') { header("Location: /app-tbm-kurkam/pages/user/katalog.php"); exit; }
include(__DIR__ . '/../../config/koneksi.php');

// --- Generate kode anggota berdasarkan kategori ---
$kategori_default = $_GET['kategori'] ?? 'dewasa';
$prefix_map = ['dewasa' => 'D', 'remaja' => 'R', 'anak-anak' => 'A'];
$prefix = $prefix_map[$kategori_default] ?? 'D';
$tahun  = date('Y');
$like   = "$prefix-$tahun%";
$q = mysqli_query($conn, "SELECT MAX(CAST(SUBSTRING(kode_anggota, 8) AS UNSIGNED)) AS n FROM m_anggota WHERE kode_anggota LIKE '$like'");
$maxn  = (int)(mysqli_fetch_assoc($q)['n'] ?? 0);
$kodeOtomatis = "$prefix-$tahun" . sprintf("%03d", $maxn + 1);

// --- Proses Simpan ---
if (isset($_POST['simpan'])) {
    $kode_anggota  = mysqli_real_escape_string($conn, $_POST['kode_anggota']);
    $nama_lengkap  = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $kategori_usia = $_POST['kategori_usia'];
    $tanggal_daftar= date('Y-m-d');
    $created_by    = $_SESSION['user'];

    // Field opsional: kirim NULL jika kosong agar tidak error tipe data di MySQL
    $tgl_raw       = trim($_POST['tanggal_lahir'] ?? '');
    $tanggal_lahir = !empty($tgl_raw) ? "'$tgl_raw'" : 'NULL';

    $nik_raw       = mysqli_real_escape_string($conn, trim($_POST['nik'] ?? ''));
    $nik           = !empty($nik_raw) ? "'$nik_raw'" : 'NULL';

    $telepon_raw   = mysqli_real_escape_string($conn, trim($_POST['telepon'] ?? ''));
    $telepon       = !empty($telepon_raw) ? "'$telepon_raw'" : 'NULL';

    $alamat_raw    = mysqli_real_escape_string($conn, trim($_POST['alamat'] ?? ''));
    $alamat        = !empty($alamat_raw) ? "'$alamat_raw'" : 'NULL';

    $q = "INSERT INTO m_anggota (kode_anggota, nama_lengkap, jenis_kelamin, tanggal_lahir, nik, kategori_usia, telepon, alamat, tanggal_daftar, created_by)
          VALUES ('$kode_anggota','$nama_lengkap','$jenis_kelamin',$tanggal_lahir,$nik,'$kategori_usia',$telepon,$alamat,'$tanggal_daftar','$created_by')";

    if (mysqli_query($conn, $q)) {
        $_SESSION['sukses_tambah_anggota'] = true;
        header("Location: daftar.php"); exit;
    } else {
        $error_db = mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Anggota - TBM Kurung Kambing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">
<?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

<main class="flex-grow lg:ml-64 p-4 lg:p-8 pt-16 lg:pt-8 flex items-start justify-center">
    <div class="max-w-3xl w-full">
        <a href="daftar.php" class="inline-flex items-center text-slate-500 font-bold mb-6 hover:text-indigo-600 transition gap-2">
            <i class="fas fa-arrow-left text-sm"></i> Kembali ke Daftar Anggota
        </a>

        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200 overflow-hidden border border-slate-50">
            <div class="bg-indigo-600 p-6 lg:p-10 text-white flex items-center justify-between">
                <div>
                    <h2 class="text-xl lg:text-3xl font-black uppercase tracking-tighter">Tambah Anggota</h2>
                    <p class="text-indigo-100 text-sm mt-1">Pilih kategori usia untuk generate kode anggota otomatis.</p>
                </div>
                <div class="bg-white/10 p-5 rounded-3xl"><i class="fas fa-user-plus text-4xl"></i></div>
            </div>

            <form action="" method="POST" class="p-6 lg:p-10 space-y-5 lg:space-y-6" id="formTambahAnggota" novalidate>

                <?php if(isset($error_db)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl text-red-700 text-sm font-bold">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= $error_db ?>
                </div>
                <?php endif; ?>

                <!-- Baris 1: Kategori Usia + Kode Anggota -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Kategori Usia <span class="text-red-500">*</span></label>
                        <select name="kategori_usia" id="kategori_usia" onchange="updateKode(this.value)"
                                class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                            <option value="dewasa"    <?= $kategori_default=='dewasa'    ?'selected':'' ?>>🧑 Dewasa (19+ tahun)</option>
                            <option value="remaja"    <?= $kategori_default=='remaja'    ?'selected':'' ?>>👦 Remaja (13-18 tahun)</option>
                            <option value="anak-anak" <?= $kategori_default=='anak-anak' ?'selected':'' ?>>👶 Anak-anak (5-12 tahun)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Kode Anggota (Auto)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-indigo-500"><i class="fas fa-id-card"></i></span>
                            <input type="text" name="kode_anggota" id="kode_anggota" value="<?= $kodeOtomatis ?>" readonly
                                   class="w-full pl-12 pr-4 py-4 bg-indigo-50 border-2 border-indigo-100 rounded-2xl font-black text-indigo-600 cursor-not-allowed">
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 ml-1">D=Dewasa · R=Remaja · A=Anak-anak</p>
                    </div>
                </div>

                <!-- Baris 2: Nama + Jenis Kelamin -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" placeholder="Nama lengkap sesuai KTP..."
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="jenis_kelamin" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                </div>

                <!-- Baris 3: Tanggal Lahir + NIK -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" onchange="hitungKategori(this.value)"
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                        <p class="text-[10px] text-slate-400 mt-1 ml-1">Kategori usia akan otomatis terdeteksi</p>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">NIK / No. Identitas <span class="text-slate-300 font-normal">(opsional)</span></label>
                        <input type="text" name="nik" placeholder="16 digit NIK..." maxlength="20"
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                    </div>
                </div>

                <!-- Baris 4: Telepon + Alamat -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">No. Telepon</label>
                        <input type="text" name="telepon" placeholder="08xxxxxxxxxx"
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Alamat Lengkap</label>
                        <input type="text" name="alamat" placeholder="Jl. ..., RT/RW, Desa Kurung Kambing"
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-50 flex flex-col sm:flex-row gap-3">
                    <a href="daftar.php" class="px-8 py-4 rounded-2xl font-bold text-slate-400 hover:bg-slate-50 transition text-center">Batal</a>
                    <button type="submit" name="simpan"
                            class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95 flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> Simpan Anggota
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
const apiBase = '/app-tbm-kurkam/api/kode_anggota.php';

function updateKode(kategori) {
    fetch(`${apiBase}?kategori=${kategori}`)
        .then(r => r.json())
        .then(d => { document.getElementById('kode_anggota').value = d.kode; })
        .catch(() => {});
}

function hitungKategori(tglLahir) {
    if (!tglLahir) return;
    const today = new Date();
    const lahir = new Date(tglLahir);
    let usia = today.getFullYear() - lahir.getFullYear();
    const m = today.getMonth() - lahir.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < lahir.getDate())) usia--;

    let kategori = 'dewasa';
    if (usia >= 13 && usia <= 18) kategori = 'remaja';
    else if (usia >= 5 && usia <= 12) kategori = 'anak-anak';

    document.getElementById('kategori_usia').value = kategori;
    updateKode(kategori);
}

// Validasi form sebelum submit
document.getElementById('formTambahAnggota').addEventListener('submit', function(e) {
    const fields = [
        { id: 'nama_lengkap', label: 'Nama Lengkap' },
    ];

    const kosong = fields.filter(f => {
        const el = document.getElementById(f.id);
        return !el || el.value.trim() === '';
    });

    if (kosong.length > 0) {
        e.preventDefault();
        const listHTML = kosong.map(f =>
            `<li style="text-align:left;"><i class="fas fa-circle-xmark" style="color:#ef4444;margin-right:6px;"></i>${f.label}</li>`
        ).join('');

        Swal.fire({
            icon: 'warning',
            title: 'Data Belum Lengkap!',
            html: `<p style="margin-bottom:10px;color:#64748b;">Mohon lengkapi field berikut:</p><ul style="list-style:none;padding:0;">${listHTML}</ul>`,
            confirmButtonText: 'OK, Saya Lengkapi',
            confirmButtonColor: '#4f46e5',
            customClass: { popup: 'rounded-3xl' }
        }).then(() => {
            const firstEl = document.getElementById(kosong[0].id);
            if (firstEl) { firstEl.focus(); firstEl.classList.add('border-red-400'); }
        });
    }
});

// Hapus highlight merah saat user mulai mengetik
document.querySelectorAll('input, select').forEach(el => {
    el.addEventListener('input', () => el.classList.remove('border-red-400'));
});
</script>
</body>
</html>
