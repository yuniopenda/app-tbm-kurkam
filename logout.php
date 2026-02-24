<?php
// Memulai session
session_start();

// Menghapus semua data session yang tersimpan
session_unset();

// Menghancurkan session (benar-benar menghapusnya dari server)
session_destroy();

// Mengarahkan user kembali ke halaman login (atau index)
echo "<script>
    alert('Anda telah berhasil keluar.');
    window.location.href = 'login.php'; 
</script>";
exit;
?>