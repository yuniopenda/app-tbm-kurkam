<?php
$current_path = $_SERVER['PHP_SELF'];
$current_page = basename($current_path);
$role = $_SESSION['role'] ?? 'admin';
$user = $_SESSION['user'] ?? 'Pengguna';

// Helper: aktif jika path mengandung kata kunci
function isActive(string $needle): string {
    $path = $_SERVER['PHP_SELF'];
    return (strpos($path, $needle) !== false) ? 'bg-white/15 font-bold' : 'hover:bg-white/10 text-indigo-100';
}
function isExact(string $page): string {
    return (basename($_SERVER['PHP_SELF']) === $page) ? 'bg-white/15 font-bold' : 'hover:bg-white/10 text-indigo-100';
}

// Badge keterlambatan (hanya untuk admin/petugas)
$overdue_count = 0;
if ($role !== 'anggota' && isset($conn)) {
    $today = date('Y-m-d');
    $r = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) AS c FROM t_peminjaman WHERE status='Dipinjam' AND tgl_kembali < '$today'"));
    $overdue_count = (int)($r['c'] ?? 0);
}
?>

<!-- Hamburger Button (Mobile) -->
<button id="sidebarToggle" onclick="toggleSidebar()"
        class="fixed top-4 left-4 z-[60] lg:hidden bg-indigo-600 text-white w-10 h-10 rounded-xl flex items-center justify-center shadow-lg">
    <i class="fas fa-bars" id="hamburgerIcon"></i>
</button>

<!-- Overlay (Mobile) -->
<div id="sidebarOverlay" onclick="closeSidebar()"
     class="fixed inset-0 bg-black/40 z-40 lg:hidden hidden"></div>

<aside id="mainSidebar" class="w-64 bg-indigo-700 text-white flex flex-col shadow-2xl fixed h-full z-50 transition-transform duration-300 -translate-x-full lg:translate-x-0"
       style="background: linear-gradient(160deg, #3730a3 0%, #4338ca 50%, #4f46e5 100%);">

    <!-- App Header -->
    <div class="px-6 py-7 border-b border-white/10">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-9 h-9 bg-white/10 rounded-xl flex items-center justify-center">
                <i class="fas fa-book-open text-lg text-white"></i>
            </div>
            <div>
                <h1 class="text-base font-black leading-tight">TBM Kurung Kambing</h1>
                <p class="text-[10px] text-indigo-300 font-medium leading-tight">literasikurkam.com</p>
            </div>
        </div>
    </div>

    <!-- User info -->
    <div class="px-6 py-4 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center font-black text-sm">
                <?= strtoupper(substr($user, 0, 1)) ?>
            </div>
            <div class="overflow-hidden">
                <p class="text-xs font-bold text-white truncate"><?= htmlspecialchars($user) ?></p>
                <p class="text-[10px] text-indigo-300 font-medium uppercase tracking-wider"><?= $role ?></p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-grow px-3 py-4 space-y-1 overflow-y-auto">

        <?php if ($role === 'anggota'): ?>
            <!-- ===== MENU ANGGOTA ===== -->
            <p class="text-[9px] font-black text-indigo-400 uppercase tracking-[0.15em] px-3 mb-2">Menu</p>

            <a href="/app-tbm-kurkam/pages/user/katalog.php"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm <?= isActive('/user/katalog') ?>">
                <i class="fas fa-book-open w-4 text-center"></i>
                <span>Katalog Buku</span>
            </a>

            <a href="/app-tbm-kurkam/pages/user/pinjaman_saya.php"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm <?= isActive('/user/pinjaman_saya') ?>">
                <i class="fas fa-bookmark w-4 text-center"></i>
                <span>Pinjaman Saya</span>
            </a>

        <?php else: ?>
            <!-- ===== MENU ADMIN / PETUGAS ===== -->
            <p class="text-[9px] font-black text-indigo-400 uppercase tracking-[0.15em] px-3 mb-2">Utama</p>

            <a href="/app-tbm-kurkam/index.php"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm <?= isExact('index.php') ?>">
                <i class="fas fa-th-large w-4 text-center"></i>
                <span>Dashboard</span>
            </a>

            <a href="/app-tbm-kurkam/pages/user/katalog.php"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm <?= isActive('/user/katalog') ?>">
                <i class="fas fa-book-open w-4 text-center"></i>
                <span>Katalog Buku</span>
            </a>

            <p class="text-[9px] font-black text-indigo-400 uppercase tracking-[0.15em] px-3 mt-4 mb-2">Koleksi</p>

            <a href="/app-tbm-kurkam/pages/buku/daftar.php"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm <?= isActive('/buku/daftar') ?>">
                <i class="fas fa-book w-4 text-center"></i>
                <span>Daftar Buku</span>
            </a>

            <!-- <a href="/app-tbm-kurkam/pages/buku/tambah.php"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm <?= isActive('/buku/tambah') ?>">
                <i class="fas fa-plus-circle w-4 text-center"></i>
                <span>Tambah Buku</span>
            </a> -->

            <a href="/app-tbm-kurkam/pages/buku/template_excel.php" target="_blank"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all hover:bg-white/10 text-indigo-100 text-sm">
                <i class="fas fa-file-excel w-4 text-center text-emerald-400"></i>
                <span>Template Excel</span>
            </a>

            <a href="/app-tbm-kurkam/pages/buku/import.php"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm <?= isActive('/buku/import') ?>">
                <i class="fas fa-file-upload w-4 text-center text-emerald-400"></i>
                <span>Import Buku</span>
            </a>

            <p class="text-[9px] font-black text-indigo-400 uppercase tracking-[0.15em] px-3 mt-4 mb-2">Sirkulasi</p>

            <a href="/app-tbm-kurkam/pages/peminjaman/daftar.php"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm <?= isActive('/peminjaman/') ?> relative">
                <i class="fas fa-exchange-alt w-4 text-center"></i>
                <span>Peminjaman</span>
                <?php if ($overdue_count > 0): ?>
                <span class="ml-auto bg-red-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full animate-pulse">
                    <?= $overdue_count ?>
                </span>
                <?php endif; ?>
            </a>

            <p class="text-[9px] font-black text-indigo-400 uppercase tracking-[0.15em] px-3 mt-4 mb-2">Keanggotaan</p>

            <a href="/app-tbm-kurkam/pages/anggota/daftar.php"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm <?= isActive('/anggota/daftar') ?>">
                <i class="fas fa-users w-4 text-center"></i>
                <span>Data Anggota</span>
            </a>

            <a href="/app-tbm-kurkam/pages/anggota/tambah.php"
               class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm <?= isActive('/anggota/tambah') ?>">
                <i class="fas fa-user-plus w-4 text-center"></i>
                <span>Tambah Anggota</span>
            </a>

        <?php endif; ?>
    </nav>

    <!-- Logout -->
    <div class="px-3 py-4 border-t border-white/10">
        <a href="/app-tbm-kurkam/logout.php"
           onclick="return confirm('Yakin ingin keluar?')"
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-red-300 hover:bg-red-500 hover:text-white text-sm">
            <i class="fas fa-sign-out-alt w-4 text-center"></i>
            <span class="font-bold">Keluar</span>
        </a>
        <p class="text-center text-[9px] text-indigo-500 mt-3 font-medium">
            &copy; <?= date('Y') ?> TBM Desa Kurung Kambing
        </p>
    </div>
</aside>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const icon    = document.getElementById('hamburgerIcon');
    const isOpen  = !sidebar.classList.contains('-translate-x-full');
    if (isOpen) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        icon.className = 'fas fa-bars';
    } else {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        icon.className = 'fas fa-times';
    }
}
function closeSidebar() {
    const sidebar = document.getElementById('mainSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const icon    = document.getElementById('hamburgerIcon');
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
    icon.className = 'fas fa-bars';
}
</script>
