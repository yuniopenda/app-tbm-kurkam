<?php
// Mendapatkan nama file yang sedang dibuka
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="w-64 bg-indigo-600 text-white flex flex-col shadow-xl fixed h-full">
    <div class="p-6">
        <h1 class="text-2xl font-bold italic">Pinjam Buku 📚</h1>
    </div>

    <nav class="flex-grow px-4 space-y-2">
        <a href="/app-tbm-kurkam/index.php" class="flex items-center space-x-3 p-3 rounded-lg transition <?php echo ($current_page == 'index.php') ? 'bg-indigo-800 font-semibold shadow-inner' : 'hover:bg-indigo-500 text-indigo-100'; ?>">
            <i class="fas fa-th-large w-6"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="/app-tbm-kurkam/pages/buku/daftar.php" class="flex items-center space-x-3 p-3 rounded-lg transition <?php echo ($current_page == 'daftar.php' && strpos($_SERVER['PHP_SELF'], '/buku/') !== false) ? 'bg-indigo-800 font-semibold shadow-inner' : 'hover:bg-indigo-500 text-indigo-100'; ?>">
            <i class="fas fa-book w-6"></i>
            <span>Daftar Buku</span>
        </a>
        
        <a href="/app-tbm-kurkam/pages/peminjaman/daftar.php" class="flex items-center space-x-3 p-3 rounded-lg transition <?php echo (strpos($_SERVER['PHP_SELF'], '/peminjaman/') !== false) ? 'bg-indigo-800 font-semibold shadow-inner' : 'hover:bg-indigo-500 text-indigo-100'; ?>">
            <i class="fas fa-exchange-alt w-6"></i>
            <span>Peminjaman</span>
        </a>

        <a href="/app-tbm-kurkam/pages/anggota/daftar.php" class="flex items-center space-x-3 p-3 rounded-lg transition <?php echo (strpos($_SERVER['PHP_SELF'], '/anggota/') !== false) ? 'bg-indigo-800 font-semibold shadow-inner' : 'hover:bg-indigo-500 text-indigo-100'; ?>">
            <i class="fas fa-users w-6"></i>
            <span>Anggota</span>
        </a>
    </nav>

    <div class="mt-auto p-4 border-t border-indigo-500">
        <a href="/app-tbm-kurkam/logout.php" 
           class="flex items-center space-x-3 text-red-300 hover:text-white transition p-3 rounded-lg hover:bg-red-500"
           onclick="return confirm('Apakah Anda yakin ingin keluar?')">
            <i class="fas fa-sign-out-alt w-6"></i>
            <span class="font-bold">Keluar</span>
        </a>
    </div>
</aside>
