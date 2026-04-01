<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'anggota') { header("Location: /app-tbm-kurkam/pages/user/katalog.php"); exit; }

// 1. Pastikan koneksi sudah benar
include(__DIR__ . '/../../config/koneksi.php');

// --- START: LOGIKA KODE OTOMATIS ---
// Ambil kode terbesar dari kolom kode_buku
$query_kode = mysqli_query($conn, "SELECT kode_buku FROM m_buku ORDER BY kode_buku DESC LIMIT 1");
$data_kode  = mysqli_fetch_array($query_kode);

if ($data_kode) {
    $kodeTerakhir = $data_kode['kode_buku'];
    // Mengambil angka saja dari string (misal BK-001 menjadi 1)
    $angka = (int) preg_replace('/[^0-9]/', '', $kodeTerakhir);
    $noUrut = $angka + 1;
} else {
    // Jika tabel masih kosong, mulai dari 1
    $noUrut = 1;
}

// Membentuk kembali kode baru dengan format 3 digit (contoh: BK-002)
$kode_otomatis = "BK-" . sprintf("%03s", $noUrut);

// Proses simpan buku baru
if (isset($_POST['simpan'])) {
    $kode_buku   = mysqli_real_escape_string($conn, $_POST['kode_buku']);
    $judul       = mysqli_real_escape_string($conn, $_POST['judul']);
    $penulis     = mysqli_real_escape_string($conn, $_POST['penulis']);
    $penerbit    = mysqli_real_escape_string($conn, $_POST['penerbit']);
    $kategori    = mysqli_real_escape_string($conn, $_POST['kategori']);   // jenis topik: pertanian, dll
    $jenis_buku  = $_POST['jenis_buku'];
    $link_ebook  = mysqli_real_escape_string($conn, $_POST['link_ebook'] ?? '');
    $kat_usia    = $_POST['kategori_usia'];
    $stok        = (int)$_POST['stok'];

    // Proses upload gambar
    $nama_gambar = '';
    if (!empty($_FILES['gambar']['name'])) {
        $ext_allowed = ['jpg','jpeg','png','webp','gif'];
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $ext_allowed)) {
            $upload_dir = realpath(__DIR__ . '/../../assets/covers') . DIRECTORY_SEPARATOR;
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $nama_gambar = 'cover_' . time() . '_' . uniqid() . '.' . $ext;
            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $nama_gambar)) {
                $nama_gambar = '';
                $error_upload = 'Gagal mengupload gambar.';
            }
        } else {
            $error_upload = 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.';
        }
    }

    $nama_gambar_db = mysqli_real_escape_string($conn, $nama_gambar);
    $q = "INSERT INTO m_buku (kode_buku, judul, penulis, penerbit, kategori, jenis_buku, link_ebook, kategori_usia, stok, gambar)
          VALUES ('$kode_buku','$judul','$penulis','$penerbit','$kategori','$jenis_buku','$link_ebook','$kat_usia','$stok','$nama_gambar_db')";
    if (mysqli_query($conn, $q)) {
        $_SESSION['sukses_tambah'] = true;
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
    <title>Tambah Buku - TBM Kurung Kambing</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">
<?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

<main class="flex-grow lg:ml-64 p-4 lg:p-8 pt-16 lg:pt-8 flex items-start justify-center">
    <div class="max-w-3xl w-full">
        <a href="daftar.php" class="inline-flex items-center text-slate-500 font-bold mb-6 hover:text-indigo-600 transition gap-2">
            <i class="fas fa-arrow-left text-sm"></i> Kembali ke Katalog
        </a>
        <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden">
            <div class="bg-indigo-600 p-6 lg:p-10 text-white flex items-center justify-between">
                <div>
                    <h2 class="text-xl lg:text-3xl font-black uppercase">Tambah Buku</h2>
                    <p class="text-indigo-200 text-sm mt-1">Masukkan informasi lengkap buku koleksi perpustakaan.</p>
                </div>
                <div class="bg-white/10 p-5 rounded-3xl"><i class="fas fa-book-medical text-4xl"></i></div>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="p-6 lg:p-10 space-y-5 lg:space-y-6" id="formTambahBuku" novalidate>

                <?php if(isset($error_db)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-xl text-red-700 text-sm font-bold">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= $error_db ?>
                </div>
                <?php endif; ?>
                <?php if(isset($error_upload)): ?>
                <div class="bg-orange-50 border-l-4 border-orange-400 p-4 rounded-xl text-orange-700 text-sm font-bold">
                    <i class="fas fa-image mr-2"></i><?= $error_upload ?>
                </div>
                <?php endif; ?>

                <!-- Row 1: Kode Buku + Jenis Buku -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Kode Buku (dari perpustakaan) <span class="text-red-500">*</span></label>
                        <input type="text" 
                                name="kode_buku" 
                                id="kode_buku" 
                                value="<?php echo $kode_otomatis; ?>" 
                                class="w-full px-5 py-4 bg-slate-100 border-2 border-slate-200 rounded-2xl outline-none font-bold text-slate-700 cursor-not-allowed" 
                                readonly>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Jenis Buku <span class="text-red-500">*</span></label>
                        <select name="jenis_buku" id="jenis_buku" onchange="toggleEbook(this.value)"
                                class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                            <option value="fisik">📖 Buku Fisik</option>
                            <option value="digital">💻 Digital (eBook)</option>
                        </select>
                    </div>
                </div>

                <!-- Link eBook (muncul jika digital) -->
                <div id="row_ebook" class="hidden">
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Link eBook (URL Langsung) <span class="text-red-500">*</span></label>
                    <input type="url" name="link_ebook" placeholder="https://drive.google.com/..." id="link_ebook"
                           class="w-full px-5 py-4 bg-blue-50 border-2 border-blue-100 rounded-2xl outline-none focus:border-blue-300 font-bold text-slate-700">
                    <p class="text-xs text-slate-400 mt-1 ml-1">Masukkan link langsung ke file PDF / Google Drive / dll.</p>
                </div>

                <!-- Row 2: Judul -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Judul Buku <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" id="judul" placeholder="Judul lengkap buku..."
                           class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700 text-lg">
                </div>

                <!-- Row 3: Penulis + Penerbit -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Penulis / Pengarang <span class="text-red-500">*</span></label>
                        <input type="text" name="penulis" id="penulis" placeholder="Nama penulis..."
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Penerbit <span class="text-red-500">*</span></label>
                        <input type="text" name="penerbit" id="penerbit" placeholder="Nama penerbit..."
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                    </div>
                </div>

                <!-- Row 4: Kategori Usia + Jenis Topik + Stok -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Untuk Usia</label>
                        <select name="kategori_usia" class="w-full px-4 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                            <option value="semua">👥 Semua Usia</option>
                            <option value="dewasa">🧑 Dewasa (19+)</option>
                            <option value="remaja">👦 Remaja (13-18)</option>
                            <option value="anak-anak">👶 Anak-anak (5-12)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Jenis / Topik Buku</label>
                        <select name="kategori" class="w-full px-4 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700">
                            <option value="">-- Pilih Topik --</option>
                            <option>Pertanian</option>
                            <option>Peternakan</option>
                            <option>Agama & Religi</option>
                            <option>Kesehatan</option>
                            <option>Pendidikan</option>
                            <option>Ekonomi</option>
                            <option>Teknologi</option>
                            <option>Sastra & Fiksi</option>
                            <option>Sejarah</option>
                            <option>Sains</option>
                            <option>Seni & Budaya</option>
                            <option>Umum</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Jumlah Stok <span class="text-red-500">*</span></label>
                        <input type="number" name="stok" id="stok" value="1" min="0"
                               class="w-full px-5 py-4 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-200 font-bold text-slate-700 text-xl">
                    </div>
                </div>

                <!-- Upload Gambar Cover -->
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-2">Gambar Cover Buku <span class="text-slate-300 font-normal">(opsional)</span></label>
                    <div class="flex items-start gap-4">
                        <div id="previewWrap" class="w-24 h-32 bg-slate-100 rounded-2xl overflow-hidden border-2 border-dashed border-slate-300 flex items-center justify-center shrink-0">
                            <i class="fas fa-image text-3xl text-slate-300" id="previewIcon"></i>
                            <img id="previewImg" src="" class="w-full h-full object-cover hidden" alt="Preview">
                        </div>
                        <div class="flex-grow">
                            <label for="gambar" class="flex flex-col items-center justify-center w-full h-32 bg-slate-50 border-2 border-dashed border-slate-300 rounded-2xl cursor-pointer hover:bg-indigo-50 hover:border-indigo-300 transition">
                                <i class="fas fa-cloud-upload-alt text-2xl text-slate-400 mb-2"></i>
                                <span class="text-sm text-slate-400 font-bold">Klik untuk pilih gambar</span>
                                <span class="text-xs text-slate-300 mt-1">JPG, PNG, WEBP (maks. 5MB)</span>
                                <input type="file" name="gambar" id="gambar" accept="image/*" class="hidden" onchange="previewGambar(this)">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-50 flex flex-col sm:flex-row gap-3">
                    <a href="daftar.php" class="px-8 py-4 rounded-2xl font-bold text-slate-400 hover:bg-slate-50 transition text-center">Batal</a>
                    <button type="submit" name="simpan"
                            class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl font-black shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition active:scale-95 flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i> Simpan Buku
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>
<script>
function previewGambar(input) {
    const icon = document.getElementById('previewIcon');
    const img  = document.getElementById('previewImg');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            img.classList.remove('hidden');
            icon.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function toggleEbook(val) {
    const row = document.getElementById('row_ebook');
    const link = document.getElementById('link_ebook');
    row.classList.toggle('hidden', val !== 'digital');
}

document.getElementById('formTambahBuku').addEventListener('submit', function(e) {
    const isDigital = document.getElementById('jenis_buku').value === 'digital';

    const fields = [
        { id: 'kode_buku',  label: 'Kode Buku' },
        { id: 'judul',      label: 'Judul Buku' },
        { id: 'penulis',    label: 'Penulis / Pengarang' },
        { id: 'penerbit',   label: 'Penerbit' },
        { id: 'stok',       label: 'Jumlah Stok' },
    ];

    if (isDigital) {
        fields.push({ id: 'link_ebook', label: 'Link eBook' });
    }

    const kosong = fields.filter(f => {
        const el = document.getElementById(f.id);
        return !el || el.value.trim() === '' || (f.id === 'stok' && el.value === '');
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
            // Fokus ke field pertama yang kosong
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
