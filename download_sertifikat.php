<?php
// download_sertifikat.php
// Pastikan tidak ada spasi/enter sebelum tag <?php

session_start();
require 'koneksi.php'; // Pastikan file koneksi database Anda benar

// 1. Validasi Akses (Cek Parameter Invoice)
if (!isset($_GET['inv'])) {
    die("Error: Parameter Invoice tidak ditemukan.");
}

$invoice_number = urldecode($_GET['inv']);

// 2. Ambil Data dari Database (JOIN donations, donors, tree_types)
// Kita ambil nama donatur, jenis pohon, jumlah pohon, dan tanggal transaksi
$sql = "SELECT d.invoice_number, d.tree_count, d.transaction_date, d.is_anonymous,
               u.name AS nama_donatur, 
               t.name AS jenis_pohon
        FROM donations d
        LEFT JOIN donors u ON d.donor_id = u.id
        LEFT JOIN tree_types t ON d.tree_type_id = t.id
        WHERE d.invoice_number = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $invoice_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Data donasi tidak ditemukan.");
}

$data = $result->fetch_assoc();

// 3. Siapkan Data Variabel untuk Sertifikat
// Jika anonim, gunakan nama samaran
$nama_tampil = ($data['is_anonymous'] == 1) ? "Hamba Allah" : strtoupper($data['nama_donatur']);
$jumlah_pohon = $data['tree_count'];
// Default 'Pohon' jika jenis pohon null
$jenis_pohon  = $data['jenis_pohon'] ? strtoupper($data['jenis_pohon']) : 'POHON'; 
$tanggal_transaksi = date("d F Y", strtotime($data['transaction_date']));

// Hitung dampak oksigen (Logika: 1 pohon = 100 Liter/hari - sesuaikan jika ada rumus lain)
$oksigen = number_format($jumlah_pohon * 100, 0, ',', '.'); 

// 4. Konfigurasi Aset (Path File)
// Sesuaikan path ini dengan struktur folder Anda yang sebenarnya
$template_path = __DIR__ . '/assets/images/template-sertif.jpeg';
$font_bold     = __DIR__ . '/assets/fonts/poppins.bold.ttf';
$font_reg      = __DIR__ . '/assets/fonts/poppins.regular.ttf';

// Validasi keberadaan file aset
if (!file_exists($template_path)) die("Error: Template gambar tidak ditemukan di $template_path");
if (!file_exists($font_bold)) die("Error: Font Bold tidak ditemukan di $font_bold");
if (!file_exists($font_reg)) die("Error: Font Regular tidak ditemukan di $font_reg");

// 5. Buat Kanvas Gambar dari Template
$image = imagecreatefromjpeg($template_path);
if (!$image) die("Error: Gagal memuat gambar template.");

// Ambil dimensi gambar
$img_width  = imagesx($image);
$img_height = imagesy($image);

// Definisi Warna (RGB) - Diambil dari nuansa gambar sertifikat Anda
$color_dark_green = imagecolorallocate($image, 21, 87, 36);   // Hijau Tua Gelap (Judul/Nama)
$color_black      = imagecolorallocate($image, 50, 50, 50);   // Hitam Abu (Teks Biasa)
$color_highlight  = imagecolorallocate($image, 46, 125, 50);  // Hijau Highlight (Angka)

// --- Helper Function: Teks Rata Tengah ---
function printCenteredText($img, $size, $angle, $font, $text, $y, $color, $width) {
    $bbox = imagettfbbox($size, $angle, $font, $text);
    $text_width = $bbox[2] - $bbox[0];
    $x = ($width - $text_width) / 2;
    imagettftext($img, $size, $angle, $x, $y, $color, $font, $text);
}

// 6. Tulis Teks ke Sertifikat (Sesuaikan Koordinat Y dengan Gambar Template Anda)
// Asumsi resolusi gambar template Anda cukup besar (misal lebar > 2000px).
// Jika teks tidak pas, ubah angka parameter Y (argumen ke-5).

// A. NAMA DONATUR (Besar di Tengah)
// Koordinat Y sekitar 45% - 50% ke bawah gambar
printCenteredText($image, 70, 0, $font_bold, $nama_tampil, 650, $color_dark_green, $img_width);

// B. NARASI DAMPAK (Detail Donasi)
// Baris 1: "Atas kontribusi nyata dalam menanam..."
$text_line1 = "Atas kontribusi nyata dalam menanam " . $jumlah_pohon . " Pohon " . $jenis_pohon;
printCenteredText($image, 24, 0, $font_reg, $text_line1, 750, $color_black, $img_width);

// Baris 2: "...dan membantu menghasilkan X liter oksigen / hari untuk bumi."
$text_line2 = "dan membantu menghasilkan " . $oksigen . " liter oksigen / hari untuk bumi.";
printCenteredText($image, 24, 0, $font_reg, $text_line2, 800, $color_black, $img_width);

// C. TANGGAL (Di Tengah Bawah)
$text_date = "Tanggal " . $tanggal_transaksi;
printCenteredText($image, 20, 0, $font_bold, $text_date, 850, $color_black, $img_width);

// 7. Output File (Download)
// Bersihkan output buffer agar file gambar tidak corrupt
if (ob_get_length()) ob_clean();

header('Content-Type: image/jpeg');
$filename = "Sertifikat_Donoxygen_" . preg_replace('/[^a-zA-Z0-9]/', '_', $nama_tampil) . ".jpg";
header('Content-Disposition: attachment; filename="' . $filename . '"');

imagejpeg($image);
imagedestroy($image);
exit;
?>