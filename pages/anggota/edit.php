<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'anggota') { header("Location: /app-tbm-kurkam/pages/user/katalog.php"); exit; }
include(__DIR__ . '/../../config/koneksi.php');

if (!isset($_GET['id'])) { header("Location: daftar.php"); exit; }
$id  = (int)$_GET['id'];
$row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM m_anggota WHERE id = '$id'"));
if (!$row) { echo "<script>alert('Data tidak ditemukan!'); window.location='daftar.php';</script>"; exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama          = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $nik           = mysqli_real_escape_string($conn, $_POST['nik'] ?? '');
    $kategori_usia = $_POST['kategori_usia'];
    $telepon       = mysqli_real_escape_string($conn, $_POST['telepon']);
    $alamat        = mysqli_real_escape_string($conn, $_POST['alamat']);

    mysqli_query($conn, "UPDATE m_anggota SET 
        nama_lengkap='$nama', jenis_kelamin='$jenis_kelamin', tanggal_lahir='$tanggal_lahir',
        nik='$nik', kategori_usia='$kategori_usia', telepon='$telepon', alamat='$alamat',
        updated_by='{$_SESSION['user']}', updated_at=NOW()
        WHERE id='$id'");

    $_SESSION['sukses_edit_anggota'] = true;
    header("Location: daftar.php"); exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Anggota - TBM Kurung Kambing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">
<?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

<main class="flex-grow ml-64 p-8 flex items-start justify-center pt-12">
    <div class="max-w-3xl w-full">
        <a href="daftar.php" class="inline-flex items-center text-slate-500 font-bold mb-6 hover:text-indigo-600 transition gap-2">
            <i class="fas fa-arrow-left text-sm"></i> Kembali
        </a>
        <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="bg-indigo-600 p-10 text-white flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-black uppercase">Edit Anggota</h2>
                    <p class="text-indigo-200 text-sm mt-1">Kode: <strong><?= $row['kode_anggota'] ?></strong></p>
                </div>
                <div class="bg-white/10 p-5 rounded-3xl"><i class="fas fa-user-edit text-4xl"></i></div>
            </div>
            <form id="editForm" action="" method="POST" class="p-10 space-y-6" novalidate>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Kode Anggota</label>
                        <input type="text" value="<?= $row['kode_anggota'] ?>" readonly
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-100 rounded-2xl font-black text-slate-400 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Kategori Usia <span class="text-red-500">*</span></label>
                        <select name="kategori_usia" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                            <option value="dewasa"    <?= $row['kategori_usia']=='dewasa'?'selected':'' ?>>🧑 Dewasa (19+)</option>
                            <option value="remaja"    <?= $row['kategori_usia']=='remaja'?'selected':'' ?>>👦 Remaja (13-18)</option>
                            <option value="anak-anak" <?= $row['kategori_usia']=='anak-anak'?'selected':'' ?>>👶 Anak-anak (5-12)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?= htmlspecialchars($row['nama_lengkap']) ?>"
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                            <option value="Laki-laki"  <?= $row['jenis_kelamin']=='Laki-laki'?'selected':'' ?>>Laki-laki</option>
                            <option value="Perempuan"  <?= $row['jenis_kelamin']=='Perempuan'?'selected':'' ?>>Perempuan</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="<?= $row['tanggal_lahir'] != '0000-00-00' ? $row['tanggal_lahir'] : '' ?>"
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">NIK <span class="text-slate-300 font-normal">(opsional)</span></label>
                        <input type="text" name="nik" value="<?= htmlspecialchars($row['nik'] ?? '') ?>" maxlength="20"
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">No. Telepon</label>
                        <input type="text" name="telepon" value="<?= htmlspecialchars($row['telepon']) ?>"
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Alamat Lengkap</label>
                        <input type="text" name="alamat" value="<?= htmlspecialchars($row['alamat'] ?? '') ?>"
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                    </div>
                </div>
                <div class="pt-6 border-t border-slate-50 flex gap-4">
                    <a href="daftar.php" class="px-8 py-4 rounded-2xl font-bold text-slate-400 hover:bg-slate-50 transition">Batal</a>
                    <button type="button" onclick="konfirmasi()"
                            class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95 flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>
<script>
function konfirmasi() {
    // Validasi field wajib sebelum konfirmasi
    const nama = document.getElementById('nama_lengkap');
    if (!nama || nama.value.trim() === '') {
        nama.classList.add('border-red-400');
        Swal.fire({
            icon: 'warning',
            title: 'Data Belum Lengkap!',
            html: '<p style="color:#64748b;">Mohon lengkapi field berikut:</p><ul style="list-style:none;padding:0;"><li style="text-align:left;"><i class="fas fa-circle-xmark" style="color:#ef4444;margin-right:6px;"></i>Nama Lengkap</li></ul>',
            confirmButtonText: 'OK, Saya Lengkapi',
            confirmButtonColor: '#4f46e5',
            customClass: { popup: 'rounded-3xl' }
        }).then(() => nama.focus());
        return;
    }

    Swal.fire({ title: 'Simpan perubahan?', icon: 'question', showCancelButton: true,
        confirmButtonColor: '#4f46e5', cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Simpan!', cancelButtonText: 'Batal',
        customClass: { popup: 'rounded-[2rem]' }
    }).then(r => { if (r.isConfirmed) document.getElementById('editForm').submit(); });
}

// Hapus highlight merah saat user mulai mengetik
document.querySelectorAll('input').forEach(el => {
    el.addEventListener('input', () => el.classList.remove('border-red-400'));
});
</script>
</body>
</html>
