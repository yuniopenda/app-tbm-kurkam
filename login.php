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
    <title>Masuk - TBM Kurung Kambing</title>
    <meta name="description" content="Masuk ke sistem Taman Baca Masyarakat Kurung Kambing untuk mengelola koleksi buku dan peminjaman.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary:      #2563EB;
            --primary-dark: #1D4ED8;
            --primary-light:#EFF6FF;
            --accent:       #F59E0B;
            --accent-light: #FFFBEB;
            --green:        #10B981;
            --green-light:  #ECFDF5;
            --red:          #EF4444;
            --red-light:    #FEF2F2;
            --bg:           #F1F5F9;
            --white:        #FFFFFF;
            --panel:        #1E3A5F;
            --panel-mid:    #1A5276;
            --panel-accent: #2874A6;
            --text-dark:    #0F172A;
            --text-mid:     #475569;
            --text-light:   #94A3B8;
            --border:       #E2E8F0;
            --shadow:       0 4px 24px rgba(37,99,235,.12);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        /* ───── HERO PANEL ───────────────────────────── */
        .hero-panel {
            display: none;
            width: 48%;
            background-color: var(--panel);
            flex-direction: column;
            justify-content: space-between;
            padding: 48px;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        @media (min-width: 1024px) { .hero-panel { display: flex; } }

        /* deco circles */
        .hero-panel::before {
            content: '';
            position: absolute;
            width: 380px; height: 380px;
            border-radius: 50%;
            background: rgba(255,255,255,.04);
            top: -100px; right: -100px;
        }
        .hero-panel::after {
            content: '';
            position: absolute;
            width: 260px; height: 260px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
            bottom: -80px; left: -60px;
        }

        .hero-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative; z-index: 2;
        }
        .hero-logo-icon {
            width: 44px; height: 44px;
            background: var(--accent);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: #fff;
        }
        .hero-logo-text {
            font-size: 18px; font-weight: 800;
            color: #fff; line-height: 1.2;
        }
        .hero-logo-text span { color: var(--accent); display: block; font-size: 11px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }

        /* Central Illustration */
        .hero-illustration {
            position: relative; z-index: 2;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            flex: 1;
        }

        /* SVG Bookshelf */
        .bookshelf-svg { width: 100%; max-width: 340px; }

        .hero-headline {
            text-align: center; margin-top: 32px;
        }
        .hero-headline h1 {
            font-size: clamp(22px, 2.4vw, 32px);
            font-weight: 900; color: #fff; line-height: 1.25;
            margin-bottom: 10px;
        }
        .hero-headline p {
            font-size: 14px; color: rgba(255,255,255,.65);
            line-height: 1.6; max-width: 300px; margin: 0 auto;
        }

        /* Feature badges */
        .hero-badges {
            display: flex; gap: 10px; flex-wrap: wrap;
            position: relative; z-index: 2;
        }
        .badge {
            display: flex; align-items: center; gap: 7px;
            background: rgba(255,255,255,.09);
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 40px;
            padding: 8px 14px;
            font-size: 12px; font-weight: 600; color: rgba(255,255,255,.85);
        }
        .badge i { color: var(--accent); font-size: 11px; }

        /* ───── FORM PANEL ───────────────────────────── */
        .form-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
            background: var(--white);
        }

        .form-card {
            width: 100%;
            max-width: 440px;
        }

        /* Mobile logo */
        .mobile-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
        }
        .mobile-logo-icon {
            width: 40px; height: 40px;
            background: var(--primary);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: #fff;
        }
        .mobile-logo-text {
            font-size: 17px; font-weight: 800; color: var(--panel);
        }
        .mobile-logo-text span { color: var(--accent); }
        @media (min-width: 1024px) { .mobile-logo { display: none; } }

        .form-title { font-size: 28px; font-weight: 900; color: var(--text-dark); line-height: 1.2; }
        .form-subtitle { margin-top: 6px; font-size: 14px; color: var(--text-mid); }

        .form-divider {
            width: 48px; height: 4px;
            background: var(--primary);
            border-radius: 99px;
            margin: 16px 0 28px;
        }

        /* Alert */
        .alert-error {
            display: flex; align-items: center; gap: 10px;
            background: var(--red-light);
            border: 1px solid #FECACA;
            border-left: 4px solid var(--red);
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 24px;
            font-size: 13px; font-weight: 600; color: var(--red);
        }

        /* Input group */
        .input-group { margin-bottom: 20px; }
        .input-label {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 8px;
        }
        .input-label label {
            font-size: 12px; font-weight: 700;
            color: var(--text-mid); letter-spacing: .5px; text-transform: uppercase;
        }
        .input-label a {
            font-size: 12px; font-weight: 700;
            color: var(--primary); text-decoration: none;
        }
        .input-label a:hover { text-decoration: underline; }

        .input-box {
            position: relative;
        }
        .input-box i {
            position: absolute;
            left: 16px; top: 50%;
            transform: translateY(-50%);
            font-size: 14px; color: var(--text-light);
            transition: color .2s;
        }
        .input-box input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px; font-weight: 500;
            color: var(--text-dark);
            background: var(--bg);
            border: 2px solid var(--border);
            border-radius: 12px;
            outline: none;
            transition: border-color .2s, background .2s;
        }
        .input-box input::placeholder { color: var(--text-light); }
        .input-box input:focus {
            border-color: var(--primary);
            background: var(--white);
        }
        .input-box input:focus + i,
        .input-box:focus-within i { color: var(--primary); }

        /* fix icon layering with focus-within */
        .input-box { display: flex; align-items: center; position: relative; }
        .input-box input { flex: 1; }
        .input-box .input-icon {
            position: absolute; left: 16px;
            font-size: 14px; color: var(--text-light);
            transition: color .2s; pointer-events: none; z-index: 2;
        }
        .input-box:focus-within .input-icon { color: var(--primary); }

        .toggle-pw {
            position: absolute; right: 14px;
            background: none; border: none; cursor: pointer;
            font-size: 14px; color: var(--text-light);
            padding: 4px;
            transition: color .2s; z-index: 2;
        }
        .toggle-pw:hover { color: var(--primary); }

        /* Submit button */
        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--primary);
            border: none;
            border-radius: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 15px; font-weight: 800;
            color: #fff; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: background .2s, transform .1s, box-shadow .2s;
            box-shadow: 0 4px 16px rgba(37,99,235,.3);
            margin-top: 8px;
        }
        .btn-login:hover { background: var(--primary-dark); box-shadow: 0 6px 20px rgba(37,99,235,.4); }
        .btn-login:active { transform: scale(.98); }

        /* Info tip */
        .login-tip {
            margin-top: 20px;
            background: var(--primary-light);
            border-radius: 10px;
            padding: 12px 16px;
            display: flex; gap: 10px; align-items: flex-start;
        }
        .login-tip i { color: var(--primary); margin-top: 2px; font-size: 13px; flex-shrink: 0; }
        .login-tip p { font-size: 12.5px; color: var(--panel); line-height: 1.5; }
        .login-tip strong { font-weight: 700; }

        /* Footer */
        .form-footer {
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            text-align: center;
            font-size: 12px; color: var(--text-light); font-weight: 500;
        }
        .form-footer span { color: var(--accent); font-weight: 700; }

        /* ── Bounce animation for book icon ── */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-8px); }
        }
        .float-anim { animation: float 3s ease-in-out infinite; }

        /* ── Responsive tweaks ── */
        @media (max-width: 480px) {
            .form-wrapper { padding: 24px 16px; align-items: flex-start; padding-top: 40px; }
            .form-title { font-size: 24px; }
        }
    </style>
</head>
<body>

    <!-- ══ HERO PANEL (desktop) ══ -->
    <div class="hero-panel">

        <!-- Logo -->
        <div class="hero-logo">
            <div class="hero-logo-icon"><i class="fas fa-book-open"></i></div>
            <div class="hero-logo-text">
                TBM Kurung Kambing
                <span>Taman Baca Masyarakat</span>
            </div>
        </div>

        <!-- Illustration -->
        <div class="hero-illustration">
            <!-- SVG Bookshelf Illustration -->
            <svg class="bookshelf-svg float-anim" viewBox="0 0 340 280" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Shelf boards -->
                <rect x="20" y="230" width="300" height="14" rx="4" fill="#2874A6"/>
                <rect x="20" y="130" width="300" height="10" rx="4" fill="#2874A6"/>
                <rect x="20" y="30" width="300" height="10" rx="4" fill="#2874A6"/>
                <!-- Side walls -->
                <rect x="14" y="30" width="12" height="214" rx="4" fill="#2874A6"/>
                <rect x="314" y="30" width="12" height="214" rx="4" fill="#2874A6"/>

                <!-- Row 1 Books (y: 40–130) -->
                <!-- Book 1 -->
                <rect x="34" y="42" width="22" height="88" rx="3" fill="#F59E0B"/>
                <rect x="34" y="42" width="6" height="88" rx="2" fill="#D97706"/>
                <!-- Book 2 -->
                <rect x="60" y="55" width="18" height="75" rx="3" fill="#10B981"/>
                <rect x="60" y="55" width="5" height="75" rx="2" fill="#059669"/>
                <!-- Book 3 wide -->
                <rect x="82" y="45" width="30" height="85" rx="3" fill="#EF4444"/>
                <rect x="82" y="45" width="6" height="85" rx="2" fill="#DC2626"/>
                <text x="93" y="92" fill="white" font-size="9" font-family="sans-serif" transform="rotate(-90,93,92)" text-anchor="middle">NOVEL</text>
                <!-- Book 4 -->
                <rect x="116" y="58" width="16" height="72" rx="3" fill="#8B5CF6"/>
                <rect x="116" y="58" width="5" height="72" rx="2" fill="#7C3AED"/>
                <!-- Book 5 -->
                <rect x="136" y="48" width="24" height="82" rx="3" fill="#06B6D4"/>
                <rect x="136" y="48" width="5" height="82" rx="2" fill="#0891B2"/>
                <!-- Book 6 leaning -->
                <rect x="164" y="60" width="20" height="70" rx="3" fill="#F97316" transform="rotate(-5,174,90)"/>
                <!-- Book 7 -->
                <rect x="188" y="50" width="18" height="80" rx="3" fill="#EC4899"/>
                <rect x="188" y="50" width="5" height="80" rx="2" fill="#DB2777"/>
                <!-- Book 8 tall -->
                <rect x="210" y="42" width="14" height="88" rx="3" fill="#14B8A6"/>
                <!-- Book 9 -->
                <rect x="228" y="55" width="26" height="75" rx="3" fill="#6366F1"/>
                <rect x="228" y="55" width="6" height="75" rx="2" fill="#4F46E5"/>
                <text x="238" y="88" fill="white" font-size="8" font-family="sans-serif" transform="rotate(-90,238,88)" text-anchor="middle">SAINS</text>
                <!-- Book 10 -->
                <rect x="258" y="48" width="20" height="82" rx="3" fill="#84CC16"/>
                <!-- Book 11 -->
                <rect x="282" y="56" width="18" height="74" rx="3" fill="#F59E0B"/>

                <!-- Row 2 Books (y: 140–230) -->
                <!-- Book 1 -->
                <rect x="34" y="142" width="28" height="88" rx="3" fill="#3B82F6"/>
                <rect x="34" y="142" width="7" height="88" rx="2" fill="#2563EB"/>
                <text x="43" y="175" fill="white" font-size="8" font-family="sans-serif" transform="rotate(-90,43,175)" text-anchor="middle">BUKU</text>
                <!-- Book 2 -->
                <rect x="66" y="158" width="16" height="72" rx="3" fill="#10B981"/>
                <!-- Book 3 -->
                <rect x="86" y="148" width="22" height="82" rx="3" fill="#EF4444"/>
                <!-- Book 4 wide -->
                <rect x="112" y="142" width="32" height="88" rx="3" fill="#8B5CF6"/>
                <rect x="112" y="142" width="7" height="88" rx="2" fill="#7C3AED"/>
                <text x="121" y="178" fill="white" font-size="8" font-family="sans-serif" transform="rotate(-90,121,178)" text-anchor="middle">ILMU</text>
                <!-- Book 5 -->
                <rect x="148" y="155" width="18" height="75" rx="3" fill="#F59E0B"/>
                <!-- Book 6 leaning -->
                <rect x="170" y="148" width="20" height="82" rx="3" fill="#06B6D4" transform="rotate(4,180,185)"/>
                <!-- Book 7 -->
                <rect x="194" y="145" width="16" height="85" rx="3" fill="#EC4899"/>
                <!-- Book 8 -->
                <rect x="214" y="152" width="24" height="78" rx="3" fill="#84CC16"/>
                <!-- Book 9 tall -->
                <rect x="242" y="142" width="14" height="88" rx="3" fill="#F97316"/>
                <!-- Book 10 -->
                <rect x="260" y="150" width="20" height="80" rx="3" fill="#6366F1"/>
                <!-- Book 11 -->
                <rect x="284" y="158" width="18" height="72" rx="3" fill="#14B8A6"/>

                <!-- Decorative open book on top -->
                <g transform="translate(140,8)">
                    <path d="M30 0 L0 4 L0 20 L30 16 Z" fill="white" opacity=".9"/>
                    <path d="M30 0 L60 4 L60 20 L30 16 Z" fill="#EFF6FF" opacity=".9"/>
                    <line x1="30" y1="0" x2="30" y2="16" stroke="#2563EB" stroke-width="1.5"/>
                    <!-- Lines on pages -->
                    <line x1="5" y1="9" x2="25" y2="8" stroke="#94A3B8" stroke-width="1" opacity=".5"/>
                    <line x1="5" y1="13" x2="22" y2="12" stroke="#94A3B8" stroke-width="1" opacity=".5"/>
                    <line x1="35" y1="9" x2="55" y2="8" stroke="#94A3B8" stroke-width="1" opacity=".5"/>
                    <line x1="35" y1="13" x2="52" y2="12" stroke="#94A3B8" stroke-width="1" opacity=".5"/>
                </g>
            </svg>

            <div class="hero-headline">
                <h1>Kelola Perpustakaan<br>Dengan Mudah & Cepat</h1>
                <p>Sistem manajemen koleksi buku, anggota, dan peminjaman untuk TBM Kurung Kambing.</p>
            </div>
        </div>

        <!-- Feature badges -->
        <div class="hero-badges">
            <div class="badge"><i class="fas fa-book"></i> Kelola Koleksi Buku</div>
            <div class="badge"><i class="fas fa-users"></i> Manajemen Anggota</div>
            <div class="badge"><i class="fas fa-exchange-alt"></i> Catat Peminjaman</div>
            <div class="badge"><i class="fas fa-chart-bar"></i> Laporan & Statistik</div>
        </div>

    </div>

    <!-- ══ FORM PANEL ══ -->
    <div class="form-wrapper">
        <div class="form-card">

            <!-- Mobile logo -->
            <div class="mobile-logo">
                <div class="mobile-logo-icon"><i class="fas fa-book-open"></i></div>
                <div class="mobile-logo-text">TBM <span>Kurung Kambing</span></div>
            </div>

            <h2 class="form-title">Selamat Datang 👋</h2>
            <p class="form-subtitle">Masuk untuk mengelola Taman Baca Masyarakat.</p>
            <div class="form-divider"></div>

            <!-- Error alert -->
            <?php if (isset($error)) : ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>Username atau password salah. Silakan coba lagi.</span>
            </div>
            <?php endif; ?>

            <!-- Login form -->
            <form action="" method="POST" autocomplete="off">
                <!-- Username -->
                <div class="input-group">
                    <div class="input-label">
                        <label for="username">Username / Kode Anggota</label>
                    </div>
                    <div class="input-box">
                        <i class="fas fa-user input-icon"></i>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            required
                            placeholder="Masukkan username atau kode anggota"
                            style="padding-left: 44px;"
                        >
                    </div>
                </div>

                <!-- Password -->
                <div class="input-group">
                    <div class="input-label">
                        <label for="password">Password</label>
                        <a href="#">Lupa password?</a>
                    </div>
                    <div class="input-box">
                        <i class="fas fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="Masukkan password"
                            style="padding-left: 44px; padding-right: 44px;"
                        >
                        <button type="button" class="toggle-pw" id="togglePw" tabindex="-1" title="Tampilkan password">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" name="login" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <!-- Info tip -->
            <div class="login-tip">
                <i class="fas fa-circle-info"></i>
                <p>
                    <strong>Admin/Petugas:</strong> gunakan username & password yang diberikan.<br>
                    <strong>Anggota:</strong> gunakan kode anggota Anda sebagai username &amp; password.
                </p>
            </div>

            <!-- Footer -->
            <div class="form-footer">
                &copy; 2026 <span>TBM Kurung Kambing</span> &mdash; Taman Baca Masyarakat
            </div>

        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePw = document.getElementById('togglePw');
        const pwInput  = document.getElementById('password');
        const eyeIcon  = document.getElementById('eyeIcon');

        togglePw.addEventListener('click', () => {
            const isHidden = pwInput.type === 'password';
            pwInput.type   = isHidden ? 'text' : 'password';
            eyeIcon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
        });
    </script>
</body>
</html>