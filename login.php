<?php
session_start();
include __DIR__ . '/config/koneksi.php';

if (isset($_POST['login'])) {
    $username_input = trim($_POST['username']);
    $password_input = $_POST['password'];

    // -- 1. Cek admin / petugas dari tabel m_users
    $username = mysqli_real_escape_string($conn, $username_input);
    $result   = mysqli_query($conn, "SELECT * FROM m_users WHERE username = '$username'");

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password_input, $row['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['user']  = $row['nama_lengkap'];
            $_SESSION['role']  = $row['role'] ?? 'admin';
            header("Location: index.php");
            exit;
        }
    }

    // -- 2. Cek anggota dari tabel m_anggota
    $kode           = mysqli_real_escape_string($conn, $username_input);
    $result_anggota = mysqli_query($conn, "SELECT * FROM m_anggota WHERE kode_anggota = '$kode'");

    if (mysqli_num_rows($result_anggota) === 1) {
        $anggota = mysqli_fetch_assoc($result_anggota);

        // Default password = kode_anggota jika belum di-set
        $valid = false;
        if (empty($anggota['password'])) {
            $valid = ($password_input === $anggota['kode_anggota']);
        } else {
            $valid = password_verify($password_input, $anggota['password']);
        }

        if ($valid) {
            $_SESSION['login']        = true;
            $_SESSION['user']         = $anggota['nama_lengkap'];
            $_SESSION['role']         = 'anggota';
            $_SESSION['id_anggota']   = $anggota['id'];
            $_SESSION['kode_anggota'] = $anggota['kode_anggota'];
            $_SESSION['usia_anggota'] = $anggota['kategori_usia'] ?? 'semua';
            header("Location: pages/user/katalog.php");
            exit;
        }
    }

    $error = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pinjam Buku</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bg-mesh-gradient {
            background-color: #4f46e5;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
        }
    </style>
</head>
<body class="min-h-screen bg-white flex font-sans">

            <div class="hidden lg:flex w-1/2 bg-mesh-gradient relative items-center justify-center p-16">
                <div class="absolute inset-0 opacity-10" style="background-image: url('https://www.transparenttextures.com/patterns/circuit-board.png');"></div>
                
                <div class="relative z-10 text-center">
                    <!-- <div class="mb-8 inline-flex p-4 bg-white/10 rounded-3xl backdrop-blur-xl border border-white/20 shadow-2xl"> -->
                        <!-- <img src="assets/image_2.png" alt="Logo LibraTech" class="h-50 w-auto object-contain"> -->
                    </div>
                    
                    <h1 class="text-6xl font-black text-white tracking-tighter mb-6">Pinjam<span class="text-indigo-400">Buku</span></h1>
                    </div>
            </div>
            <!-- <h1 class="text-6xl font-black text-white tracking-tighter mb-6">Pinjam<span class="text-indigo-400">Buku</span></h1> -->
            <!-- <p class="text-indigo-100 text-xl font-light max-w-md mx-auto leading-relaxed">
                Platform manajemen perpustakaan terpadu untuk efisiensi data koleksi dan sirkulasi buku Anda.
            </p> -->
            
            <!-- <div class="mt-12 flex items-center justify-center gap-6">
                <div class="text-center">
                    <p class="text-white font-bold text-2xl">24/7</p>
                    <p class="text-indigo-300 text-xs uppercase tracking-widest">Support</p>
                </div>
                <div class="w-px h-8 bg-white/20"></div>
                <div class="text-center">
                    <p class="text-white font-bold text-2xl">Cloud</p>
                    <p class="text-indigo-300 text-xs uppercase tracking-widest">Storage</p>
                </div>
                <div class="w-px h-8 bg-white/20"></div>
                <div class="text-center">
                    <p class="text-white font-bold text-2xl">Fast</p>
                    <p class="text-indigo-300 text-xs uppercase tracking-widest">Access</p>
                </div> -->
            </div>
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-slate-50 lg:bg-white">
        <div class="max-w-md w-full">
            <div class="lg:hidden text-center mb-10">
                <h1 class="text-4xl font-black text-indigo-600 tracking-tighter">Pinjam Buku</h1>
            </div>

            <div class="mb-10">
                <h2 class="text-4xl font-extrabold text-slate-800 mb-2">Selamat Datang</h2>
                <p class="text-slate-500">Gunakan akun admin Anda untuk mengelola sistem.</p>
            </div>

            <?php if (isset($error)) : ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-8 shadow-sm flex items-center gap-3">
                    <i class="fas fa-exclamation-circle text-lg"></i>
                    <p class="text-sm font-bold">Username atau Password salah!</p>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="space-y-6">
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Username</label>
                    <div class="relative group">
                        <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
                        <input type="text" name="username" required autocomplete="off"
                            class="w-full pl-12 pr-4 py-4 bg-slate-50 lg:bg-white border-2 border-slate-100 rounded-2xl outline-none focus:border-indigo-500 transition-all text-slate-700"
                            placeholder="Masukkan username">
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-widest ml-1">Password</label>
                        <a href="#" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">Lupa?</a>
                    </div>
                    <div class="relative group">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
                        <input type="password" name="password" required
                            class="w-full pl-12 pr-4 py-4 bg-slate-50 lg:bg-white border-2 border-slate-100 rounded-2xl outline-none focus:border-indigo-500 transition-all text-slate-700"
                            placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" name="login" 
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-2xl shadow-xl shadow-indigo-100 transition-all transform active:scale-95 flex items-center justify-center gap-3">
                    <span>Masuk ke Dashboard</span>
                    <i class="fas fa-sign-in-alt"></i>
                </button>
            </form>

            <div class="mt-12 pt-8 border-t border-slate-100 text-center">
                <p class="text-slate-400 text-xs font-medium uppercase tracking-widest">
                    &copy; 2026 Pinjam Buku
                </p>
            </div>
        </div>
    </div>
</body>
</html>