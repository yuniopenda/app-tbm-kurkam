<?php
// ============================================
// LOAD FILE .ENV
// ============================================
$envFile = __DIR__ . '/../.env';

if (!file_exists($envFile)) {
    die("File <strong>.env</strong> tidak ditemukan. Salin <code>.env.example</code> menjadi <code>.env</code> dan isi kredensial database.");
}

// Parse setiap baris pada file .env
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    // Lewati baris komentar
    if (str_starts_with(trim($line), '#')) continue;

    // Pisahkan KEY=VALUE
    if (strpos($line, '=') !== false) {
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'"); // hapus spasi & tanda kutip
        $_ENV[$key] = $value;
    }
}

// ============================================
// KONEKSI DATABASE MENGGUNAKAN NILAI DARI .ENV
// ============================================
$conn = mysqli_connect(
    $_ENV['DB_HOST'] ?? 'localhost',
    $_ENV['DB_USER'] ?? 'root',
    $_ENV['DB_PASS'] ?? '',
    $_ENV['DB_NAME'] ?? 'db_pinjam_buku'
);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Set zona waktu dari .env, default ke Asia/Jakarta
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Jakarta');
