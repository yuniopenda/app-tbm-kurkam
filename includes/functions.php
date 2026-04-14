<?php
/**
 * Log deleted data to log_hapus_data table
 */
function logDeletion($conn, $tableName, $dataId, $deletedBy) {
    // Get the data before deletion
    $q = mysqli_query($conn, "SELECT * FROM `$tableName` WHERE id = '$dataId'");
    if ($row = mysqli_fetch_assoc($q)) {
        $dataJson = mysqli_real_escape_string($conn, json_encode($row));
        $tableNameEsc = mysqli_real_escape_string($conn, $tableName);
        $deletedByEsc = mysqli_real_escape_string($conn, $deletedBy);
        
        $sql = "INSERT INTO log_hapus_data (nama_tabel, data_id, data_json, dihapus_oleh) 
                VALUES ('$tableNameEsc', '$dataId', '$dataJson', '$deletedByEsc')";
        return mysqli_query($conn, $sql);
    }
    return false;
}

/**
 * Format currency to IDR
 */
function formatRupiah($angka) {
    return "Rp " . number_of_format($angka, 0, ',', '.');
}

/**
 * Get monthly statistics for dashboard/reports
 */
function getMonthlyStats($conn, $month, $year) {
    $stats = [];
    
    // 1. Total Peminjaman
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM t_peminjaman 
                             WHERE MONTH(tgl_pinjam) = '$month' AND YEAR(tgl_pinjam) = '$year'");
    $stats['total_peminjaman'] = mysqli_fetch_assoc($q)['total'];
    
    // 2. Total Telat (Telat Kembali atau Belum Kembali melewati deadline)
    $today = date('Y-m-d');
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM t_peminjaman 
                             WHERE MONTH(tgl_pinjam) = '$month' AND YEAR(tgl_pinjam) = '$year'
                             AND (
                                (status = 'Kembali' AND tgl_dikembalikan > tgl_kembali_seharusnya) OR
                                (status = 'Dipinjam' AND tgl_kembali_seharusnya < '$today')
                             )");
    $stats['total_telat'] = mysqli_fetch_assoc($q)['total'];
    
    // 3. Buku Masuk (Buku Baru ditambahkan)
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM m_buku 
                             WHERE MONTH(created_at) = '$month' AND YEAR(created_at) = '$year'");
    // Note: m_buku.created_at is TIME in schema, looking at migration it might be TIMESTAMP. 
    // Let's re-verify m_buku structure in migration.
    $stats['buku_masuk'] = mysqli_fetch_assoc($q)['total'];
    
    // 4. Tambahan Anggota
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM m_anggota 
                             WHERE MONTH(tanggal_daftar) = '$month' AND YEAR(tanggal_daftar) = '$year'");
    $stats['anggota_baru'] = mysqli_fetch_assoc($q)['total'];
    
    return $stats;
}
