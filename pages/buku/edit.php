<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: /app-tbm-kurkam/login.php");
    exit;
}
include(__DIR__ . '/../../config/koneksi.php');

// 1. Ambil ID dari URL dan validasi data
if (!isset($_GET['id'])) {
    header("Location: daftar.php");
    exit;
}

$id = $_GET['id'];
$query = mysqli_query($conn, "SELECT * FROM m_buku WHERE id = '$id'");
$buku = mysqli_fetch_assoc($query);

// Jika buku tidak ditemukan
if (!$buku) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='daftar.php';</script>";
    exit;
}

// 2. Logika Update Data (Proses ketika form disubmit)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul       = mysqli_real_escape_string($conn, $_POST['judul']);
    $penulis     = mysqli_real_escape_string($conn, $_POST['penulis']);
    $penerbit    = mysqli_real_escape_string($conn, $_POST['penerbit']);
    $kategori    = mysqli_real_escape_string($conn, $_POST['kategori']);
    $jenis_buku  = $_POST['jenis_buku'];
    $link_ebook  = mysqli_real_escape_string($conn, $_POST['link_ebook'] ?? '');
    $kat_usia    = $_POST['kategori_usia'];
    $stok        = (int)$_POST['stok'];

    $update_query = "UPDATE m_buku SET 
                    judul = '$judul', penulis = '$penulis', penerbit = '$penerbit', 
                    kategori = '$kategori', jenis_buku = '$jenis_buku', 
                    link_ebook = '$link_ebook', kategori_usia = '$kat_usia', stok = '$stok' 
                    WHERE id = '$id'";

    // Proses upload gambar baru jika ada
    if (!empty($_FILES['gambar']['name'])) {
        $ext_allowed = ['jpg','jpeg','png','webp','gif'];
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $ext_allowed)) {
            $upload_dir = realpath(__DIR__ . '/../../assets/covers') . DIRECTORY_SEPARATOR;
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $nama_gambar_baru = 'cover_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $nama_gambar_baru)) {
                // Hapus gambar lama jika ada
                if (!empty($buku['gambar'])) {
                    $gambar_lama = $upload_dir . $buku['gambar'];
                    if (file_exists($gambar_lama)) unlink($gambar_lama);
                }
                $nama_gambar_esc = mysqli_real_escape_string($conn, $nama_gambar_baru);
                $update_query = "UPDATE m_buku SET 
                    judul = '$judul', penulis = '$penulis', penerbit = '$penerbit', 
                    kategori = '$kategori', jenis_buku = '$jenis_buku', 
                    link_ebook = '$link_ebook', kategori_usia = '$kat_usia', stok = '$stok',
                    gambar = '$nama_gambar_esc' 
                    WHERE id = '$id'";
            } else {
                $error_upload = 'Gagal mengupload gambar.';
            }
        } else {
            $error_upload = 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WEBP.';
        }
    }

    if (mysqli_query($conn, $update_query)) {
        $_SESSION['sukses_edit'] = true;
        header("Location: daftar.php");
        exit;
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
    <title>Edit Koleksi - PinjamBuku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-slate-100 font-sans flex min-h-screen">

    <?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

    <main class="flex-grow lg:ml-64 p-4 lg:p-8 pt-16 lg:pt-8 flex items-start justify-center">
        <div class="max-w-4xl w-full">
            
            <a href="daftar.php" class="inline-flex items-center text-slate-500 font-bold mb-6 hover:text-indigo-600 transition gap-2 text-lg">
                <i class="fas fa-arrow-left"></i> Kembali ke Katalog
            </a>

            <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden border border-slate-50">
            <div class="bg-indigo-600 p-6 lg:p-10 text-white flex items-center justify-between">
                <div>
                    <h2 class="text-xl lg:text-3xl font-black uppercase tracking-tighter italic">Edit Data Buku</h2>
                        <p class="text-indigo-100 text-lg mt-1">Sesuaikan informasi buku: <strong><?= $buku['kode_buku']; ?></strong></p>
                    </div>
                    <div class="bg-white/10 p-6 rounded-3xl backdrop-blur-md">
                        <i class="fas fa-edit text-4xl"></i>
                    </div>
                </div>

                <form id="formEdit" action="" method="POST" enctype="multipart/form-data" class="p-6 lg:p-12 space-y-5 lg:space-y-8" novalidate>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 lg:gap-8">
                        
                        <div class="col-span-1">
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Kode Buku (Terkunci)</label>
                            <div class="relative group">
                                <span class="absolute inset-y-0 left-0 pl-5 flex items-center text-slate-300">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="text" value="<?= $buku['kode_buku']; ?>" readonly
                                       class="w-full pl-14 pr-6 py-5 bg-slate-50 border-2 border-slate-100 rounded-2xl outline-none font-bold text-slate-400 cursor-not-allowed">
                            </div>
                        </div>

                        <div class="col-span-1">
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Jenis Buku <span class="text-red-500">*</span></label>
                            <select name="jenis_buku" id="jenis_buku" onchange="toggleEbook(this.value)"
                                    class="w-full px-6 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700">
                                <option value="fisik"   <?= ($buku['jenis_buku']??'fisik')==='fisik'   ?'selected':'' ?>>📖 Buku Fisik</option>
                                <option value="digital" <?= ($buku['jenis_buku']??'fisik')==='digital' ?'selected':'' ?>>💻 Digital (eBook)</option>
                            </select>
                        </div>

                        <div class="col-span-2" id="row_ebook" <?= ($buku['jenis_buku']??'fisik')==='digital' ? '' : 'style="display:none"' ?>>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Link eBook <span class="text-red-500">*</span></label>
                            <input type="url" name="link_ebook" id="link_ebook" value="<?= htmlspecialchars($buku['link_ebook'] ?? '') ?>" placeholder="https://..."
                                   class="w-full px-8 py-5 bg-blue-50 border-2 border-blue-100 rounded-2xl outline-none focus:border-blue-300 transition-all font-bold text-slate-700">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Untuk Usia</label>
                            <select name="kategori_usia" class="w-full px-6 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 font-bold text-slate-700">
                                <option value="semua"    <?= ($buku['kategori_usia']??'semua')==='semua'    ?'selected':'' ?>>👥 Semua Usia</option>
                                <option value="dewasa"   <?= ($buku['kategori_usia']??'semua')==='dewasa'   ?'selected':'' ?>>🧑 Dewasa (19+)</option>
                                <option value="remaja"   <?= ($buku['kategori_usia']??'semua')==='remaja'   ?'selected':'' ?>>👦 Remaja (13-18)</option>
                                <option value="anak-anak" <?= ($buku['kategori_usia']??'semua')==='anak-anak' ?'selected':'' ?>>👶 Anak-anak (5-12)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Jenis / Topik</label>
                            <select name="kategori" class="w-full px-6 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 font-bold text-slate-700">
                                <?php foreach(['Pertanian','Peternakan','Agama & Religi','Kesehatan','Pendidikan','Ekonomi','Teknologi','Sastra & Fiksi','Sejarah','Sains','Seni & Budaya','Umum'] as $kat): ?>
                                <option value="<?= $kat ?>" <?= $buku['kategori']===$kat?'selected':'' ?>><?= $kat ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Judul Lengkap <span class="text-red-500">*</span></label>
                            <input type="text" name="judul" id="judul" value="<?= htmlspecialchars($buku['judul']); ?>"
                                   class="w-full px-8 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700 text-xl">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Penulis <span class="text-red-500">*</span></label>
                            <input type="text" name="penulis" id="penulis" value="<?= htmlspecialchars($buku['penulis']); ?>"
                                   class="w-full px-8 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Penerbit <span class="text-red-500">*</span></label>
                            <input type="text" name="penerbit" id="penerbit" value="<?= htmlspecialchars($buku['penerbit']); ?>"
                                   class="w-full px-8 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Stok Tersedia <span class="text-red-500">*</span></label>
                            <input type="number" name="stok" id="stok" value="<?= $buku['stok']; ?>"
                                   class="w-full px-8 py-5 bg-slate-50 border-2 border-slate-50 rounded-2xl outline-none focus:border-indigo-100 focus:bg-white transition-all font-bold text-slate-700 text-xl">
                        </div>
                    </div>

                    <!-- Upload Gambar Cover -->
                    <div class="col-span-2">
                        <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Gambar Cover Buku <span class="text-slate-300 font-normal">(opsional – kosongkan jika tidak ingin mengubah)</span></label>
                        <div class="flex items-start gap-4">
                            <!-- Preview gambar -->
                            <div class="w-24 h-32 bg-slate-100 rounded-2xl overflow-hidden border-2 border-dashed border-slate-300 flex items-center justify-center shrink-0">
                                <?php
                                $gambar_ada = !empty($buku['gambar']);
                                $path_browser = '/app-tbm-kurkam/assets/covers/' . $buku['gambar'];
                                $path_fisik   = $gambar_ada ? realpath(__DIR__ . '/../../assets/covers/' . $buku['gambar']) : false;
                                $gambar_valid = $gambar_ada && $path_fisik && file_exists($path_fisik);
                                ?>
                                <img id="previewImg" src="<?= $gambar_valid ? $path_browser : '' ?>" class="w-full h-full object-cover <?= $gambar_valid ? '' : 'hidden' ?>" alt="Cover">
                                <i class="fas fa-image text-3xl text-slate-300 <?= $gambar_valid ? 'hidden' : '' ?>" id="previewIcon"></i>
                            </div>
                            <!-- Input file -->
                            <div class="flex-grow">
                                <label for="gambar" class="flex flex-col items-center justify-center w-full h-32 bg-slate-50 border-2 border-dashed border-slate-300 rounded-2xl cursor-pointer hover:bg-indigo-50 hover:border-indigo-300 transition">
                                    <i class="fas fa-cloud-upload-alt text-2xl text-slate-400 mb-2"></i>
                                    <span class="text-sm text-slate-400 font-bold" id="namaFile"><?= $gambar_valid ? 'Ganti gambar cover' : 'Klik untuk pilih gambar' ?></span>
                                    <span class="text-xs text-slate-300 mt-1">JPG, PNG, WEBP (maks. 5MB)</span>
                                    <input type="file" name="gambar" id="gambar" accept="image/*" class="hidden" onchange="previewGambar(this)">
                                </label>
                                <?php if($gambar_valid): ?>
                                <p class="text-xs text-slate-400 mt-2 ml-1"><i class="fas fa-check-circle text-emerald-500 mr-1"></i>Gambar saat ini: <strong><?= htmlspecialchars($buku['gambar']) ?></strong></p>
                                <?php elseif($gambar_ada): ?>
                                <p class="text-xs text-red-400 mt-2 ml-1"><i class="fas fa-exclamation-circle mr-1"></i>File gambar tidak ditemukan di server.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if(isset($error_upload)): ?>
                        <div class="mt-3 bg-orange-50 border-l-4 border-orange-400 p-3 rounded-xl text-orange-700 text-sm font-bold">
                            <i class="fas fa-image mr-2"></i><?= $error_upload ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="pt-6 border-t border-slate-50 flex flex-col sm:flex-row items-center justify-end gap-3">
                        <a href="daftar.php" class="px-8 py-4 rounded-2xl font-bold text-slate-400 hover:text-slate-600 transition-all text-center">Batal</a>
                        <button type="button" onclick="konfirmasiUpdate()" 
                                class="flex-grow bg-indigo-600 text-white px-10 py-5 rounded-2xl font-black shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all transform active:scale-95 flex items-center justify-center gap-3 text-lg">
                            <i class="fas fa-save"></i>
                            Simpan Perubahan
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
        const label = document.getElementById('namaFile');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                img.src = e.target.result;
                img.classList.remove('hidden');
                if (icon) icon.classList.add('hidden');
                if (label) label.textContent = input.files[0].name;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    function konfirmasiUpdate() {
        const isDigital = document.getElementById('jenis_buku').value === 'digital';
        const fields = [
            { id: 'judul',   label: 'Judul Lengkap' },
            { id: 'penulis', label: 'Penulis' },
            { id: 'penerbit',label: 'Penerbit' },
            { id: 'stok',    label: 'Stok Tersedia' },
        ];
        if (isDigital) fields.push({ id: 'link_ebook', label: 'Link eBook' });

        const kosong = fields.filter(f => {
            const el = document.getElementById(f.id);
            return !el || el.value.trim() === '';
        });

        if (kosong.length > 0) {
            const listHTML = kosong.map(f =>
                `<li style="text-align:left;"><i class="fas fa-circle-xmark" style="color:#ef4444;margin-right:6px;"></i>${f.label}</li>`
            ).join('');
            kosong.forEach(f => {
                const el = document.getElementById(f.id);
                if (el) el.classList.add('border-red-400');
            });
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap!',
                html: `<p style="margin-bottom:10px;color:#64748b;">Mohon lengkapi field berikut:</p><ul style="list-style:none;padding:0;">${listHTML}</ul>`,
                confirmButtonText: 'OK, Saya Lengkapi',
                confirmButtonColor: '#4f46e5',
                customClass: { popup: 'rounded-3xl' }
            }).then(() => {
                const firstEl = document.getElementById(kosong[0].id);
                if (firstEl) firstEl.focus();
            });
            return;
        }

        Swal.fire({
            title: 'Simpan Perubahan?',
            text: "Data buku akan diperbarui secara permanen.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[2.5rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formEdit').submit();
            }
        })
    }
    function toggleEbook(val) {
        const row  = document.getElementById('row_ebook');
        const link = document.getElementById('link_ebook');
        const show = val === 'digital';
        row.style.display = show ? '' : 'none';
    }

    // Hapus highlight merah saat user mulai mengetik
    document.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('input', () => el.classList.remove('border-red-400'));
    });</script>

    <?php if(isset($error_db)): ?>
    <script>
        Swal.fire({
            title: 'Gagal!',
            text: 'Terjadi kesalahan: <?= $error_db; ?>',
            icon: 'error',
            confirmButtonColor: '#ef4444',
            customClass: { popup: 'rounded-[2.5rem]' }
        });
    </script>
    <?php endif; ?>

</body>
</html>
