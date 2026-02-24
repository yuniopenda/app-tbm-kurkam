<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: /app-tbm-kurkam/login.php");
    exit;
}
include(__DIR__ . '/../../config/koneksi.php');

// 1. AMBIL DATA LAMA BERDASARKAN ID
if (!isset($_GET['id'])) {
    header("Location: daftar.php");
    exit;
}

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM m_anggota WHERE id = '$id'");
$data = mysqli_fetch_assoc($result);

// Jika data tidak ditemukan
if (mysqli_num_rows($result) < 1) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='daftar.php';</script>";
    exit;
}

// 2. LOGIKA UPDATE DATA
if (isset($_POST['ubah'])) {
    $nama_lengkap   = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $jenis_kelamin  = $_POST['jenis_kelamin'];
    $telepon        = mysqli_real_escape_string($conn, $_POST['telepon']);
    $alamat         = mysqli_real_escape_string($conn, $_POST['alamat']);
    $tanggal_daftar = $_POST['tanggal_daftar'];
    
    // Identitas pengubah dari session
    $updated_by = $_SESSION['user'] ?? 'Admin';

    try {
        $query = "UPDATE m_anggota SET 
                    nama_lengkap = '$nama_lengkap',
                    jenis_kelamin = '$jenis_kelamin',
                    telepon = '$telepon',
                    alamat = '$alamat',
                    tanggal_daftar = '$tanggal_daftar',
                    updated_by = '$updated_by',
                    updated_at = NOW()
                  WHERE id = '$id'";
        
        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Data Anggota Berhasil Diperbarui!'); window.location='daftar.php';</script>";
        }
    } catch (mysqli_sql_exception $e) {
        $pesan_error = $e->getMessage();
        echo "<script>alert('Gagal Ubah: $pesan_error'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ubah Anggota - LibraTech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans flex min-h-screen">

    <?php include(__DIR__ . '/../../includes/sidebar.php'); ?>

    <main class="flex-grow ml-64 p-8 flex items-center justify-center">
        <div class="max-w-2xl w-full">
            <a href="daftar.php" class="inline-flex items-center text-indigo-600 font-bold mb-6 hover:text-indigo-800 transition">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>

            <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
                <div class="bg-amber-500 p-8 text-white flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold uppercase tracking-wider">Edit Anggota</h2>
                        <p class="text-amber-100 text-sm opacity-80">Mengubah data: <?= $data['kode_anggota']; ?></p>
                    </div>
                    <div class="bg-white/20 p-4 rounded-2xl">
                        <i class="fas fa-user-edit text-3xl"></i>
                    </div>
                </div>

                <form action="" method="POST" class="p-10 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Kode Anggota</label>
                            <input type="text" value="<?= $data['kode_anggota']; ?>" readonly
                                class="pl-6 pr-4 py-4 w-full bg-gray-100 border-2 border-gray-100 rounded-2xl outline-none font-bold text-gray-500">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Jenis Kelamin</label>
                            <select name="jenis_kelamin" required class="w-full px-6 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl outline-none focus:border-amber-500 transition">
                                <option value="Laki-laki" <?= ($data['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?= ($data['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= $data['nama_lengkap']; ?>" required class="w-full px-6 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl outline-none focus:border-amber-500 transition">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Nomor HP</label>
                            <input type="text" name="telepon" value="<?= $data['telepon']; ?>" required class="w-full px-6 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl outline-none focus:border-amber-500 transition">
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Tanggal Daftar</label>
                            <input type="date" name="tanggal_daftar" value="<?= $data['tanggal_daftar']; ?>" required class="w-full px-6 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl outline-none focus:border-amber-500 transition">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">Alamat</label>
                        <input type="text" name="alamat" value="<?= $data['alamat']; ?>" required class="w-full px-6 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl outline-none focus:border-amber-500 transition">
                    </div>

                    <div class="pt-4 flex gap-4">
                        <button type="submit" name="ubah" class="flex-[2] bg-amber-500 hover:bg-amber-600 text-white font-bold py-4 rounded-2xl shadow-lg transition transform active:scale-95">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
