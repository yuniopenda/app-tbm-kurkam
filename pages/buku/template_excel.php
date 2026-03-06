<?php
session_start();
if (!isset($_SESSION['login'])) { header("Location: /app-tbm-kurkam/login.php"); exit; }
if (isset($_SESSION['role']) && $_SESSION['role'] === 'anggota') { header("Location: /app-tbm-kurkam/pages/user/katalog.php"); exit; }

// Download template Excel dalam format HTML (bisa dibuka & disimpan sebagai .xls)
$filename = 'template_impor_buku_tbm.xls';
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:x="urn:schemas-microsoft-com:office:excel"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
    <meta charset="UTF-8">
    <!--[if gte mso 9]><xml>
        <x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
            <x:Name>Template Buku</x:Name>
            <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
        </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook>
    </xml><![endif]-->
    <style>
        th { background-color: #4f46e5; color: white; font-weight: bold; }
        td.petunjuk { background-color: #eff6ff; font-style: italic; color: #6b7280; }
        table { border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 8px 12px; }
        tr.contoh { background-color: #f8fafc; }
    </style>
</head>
<body>
<table>
    <thead>
        <tr>
            <th>kode_buku</th>
            <th>judul_buku</th>
            <th>penulis_pengarang</th>
            <th>penerbit</th>
            <th>jumlah_stok</th>
            <th>kategori_buku</th>
            <th>jenis_buku</th>
            <th>kategori_usia</th>
            <th>link_ebook_jika_digital</th>
        </tr>
    </thead>
    <tbody>
        <tr class="contoh">
            <td>BK-001</td>
            <td>Bertani Padi Modern</td>
            <td>Agus Santoso</td>
            <td>Gramedia</td>
            <td>3</td>
            <td>Pertanian</td>
            <td>fisik</td>
            <td>dewasa</td>
            <td></td>
        </tr>
        <tr class="contoh">
            <td>BK-002</td>
            <td>Panduan Lengkap Python</td>
            <td>Budi Raharjo</td>
            <td>Informatika</td>
            <td>2</td>
            <td>Teknologi</td>
            <td>digital</td>
            <td>remaja</td>
            <td>https://drive.google.com/link-ke-pdf</td>
        </tr>
        <tr>
            <td class="petunjuk">Kode unik buku</td>
            <td class="petunjuk">Judul lengkap buku</td>
            <td class="petunjuk">Nama penulis/pengarang</td>
            <td class="petunjuk">Nama penerbit</td>
            <td class="petunjuk">Angka (1, 2, 3, ...)</td>
            <td class="petunjuk">Pertanian / Kesehatan / Agama &amp; Religi / Pendidikan / Teknologi / Sastra &amp; Fiksi / Umum / dll</td>
            <td class="petunjuk">fisik ATAU digital</td>
            <td class="petunjuk">semua / dewasa / remaja / anak-anak</td>
            <td class="petunjuk">Kosong jika fisik. Isi URL jika digital.</td>
        </tr>
    </tbody>
</table>
</body>
</html>
