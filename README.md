# 🌿 Donoxygen - Platform Donasi Pohon & Edukasi Lingkungan

![Donoxygen Banner](assets/images/logo-donoxygen.svg)

> **"Satu Pohon, Sejuta Oksigen."**

[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
[![License](https://img.shields.io/badge/License-Academic-green?style=for-the-badge)](LICENSE)

## 📖 Tentang Proyek

**Donoxygen** adalah sebuah aplikasi berbasis web yang dibangun untuk memfasilitasi donasi pohon secara transparan, mudah, dan berdampak. Proyek ini dikembangkan sebagai **Tugas Akhir Praktikum Pemrograman Web**.

Aplikasi ini menjembatani kesenjangan kepercayaan donatur dengan menyediakan fitur **Laporan Transparansi Real-time** dan **Visualisasi Dampak Lingkungan**. Donatur tidak hanya menyumbang uang, tetapi langsung mengetahui berapa liter oksigen yang akan dihasilkan dari donasi mereka.

## ✨ Fitur Unggulan

### 🌍 Untuk Donatur (Frontend)
* **Kalkulator Dampak Real-time:** Konversi otomatis nominal rupiah ke jumlah pohon dan estimasi liter oksigen.
* **Sertifikat Donasi Otomatis:** Generate sertifikat apresiasi (JPG) secara instan menggunakan **PHP GD Library** dengan nama donatur yang dinamis.
* **Transparansi Dana:** Halaman laporan publik yang menampilkan total donasi masuk vs dana tersalurkan.
* **Edukasi Lingkungan:** Artikel dan wawasan hijau untuk meningkatkan kesadaran pengguna.
* **Pilihan Pembayaran:** Simulasi pembayaran via Transfer Bank, E-Wallet, dan QRIS.

### 🛡️ Untuk Admin (Backend Dashboard)
* **Verifikasi Donasi:** Validasi bukti transfer donatur dengan fitur *modal popup*.
* **Manajemen Artikel (CMS):** Fitur *Create, Read, Update, Delete* untuk konten edukasi.
* **Laporan Keuangan:** Monitoring arus kas masuk dan keluar.
* **Kelola Data:** Manajemen data donatur dan inventaris jenis pohon.

## 🛠️ Teknologi yang Digunakan

* **Backend:** PHP Native (Procedural & OOP basic)
* **Database:** MySQL / MariaDB
* **Frontend:** HTML5, CSS3, Bootstrap 5
* **Library Tambahan:**
    * **PHP GD Library:** Untuk manipulasi gambar sertifikat.
    * **Font Awesome 6:** Untuk ikon antarmuka.
    * **Google Fonts:** Poppins & Inter.

## 📸 Tangkapan Layar (Screenshots)

| Halaman Utama | Halaman Donasi |
|:---:|:---:|
| ![Home Page](assets/screenshots/home-preview.png) | ![Donasi](assets/screenshots/donasi-preview.png) |

| Sertifikat Otomatis | Dashboard Admin |
|:---:|:---:|
| ![Sertifikat](assets/screenshots/sertifikat-preview.jpg) | ![Admin](assets/screenshots/admin-preview.png) |

*(Catatan: Gambar di atas adalah preview, silakan jalankan aplikasi untuk tampilan penuh)*

## 🚀 Cara Instalasi (Localhost)

Ikuti langkah-langkah ini untuk menjalankan proyek di komputer Anda:

1.  **Clone Repositori**
    ```bash
    git clone [https://github.com/username-anda/donoxygen.git](https://github.com/username-anda/donoxygen.git)
    ```
    Atau download sebagai ZIP dan ekstrak.

2.  **Persiapan XAMPP**
    * Pastikan **XAMPP** sudah terinstal.
    * Pindahkan folder proyek ke dalam direktori `C:\xampp\htdocs\donoxygen`.
    * Nyalakan module **Apache** dan **MySQL** di XAMPP Control Panel.
    * **PENTING:** Pastikan extension GD aktif di PHP. Buka `php.ini`, cari `;extension=gd` dan hapus tanda titik koma (`;`). Restart Apache.

3.  **Konfigurasi Database**
    * Buka browser dan akses `http://localhost/phpmyadmin`.
    * Buat database baru dengan nama `db_donasi_oksigen`.
    * Import file `db_donasi_oksigen.sql` yang ada di dalam folder proyek.

4.  **Konfigurasi Koneksi**
    * Cek file `koneksi.php`. Pastikan username dan password database sesuai (Default XAMPP: user `root`, password kosong).

5.  **Jalankan Aplikasi**
    * Buka browser dan akses: `http://localhost/donoxygen/dashboard.php`

## 🔑 Akun Demo

Gunakan akun berikut untuk masuk ke halaman Admin:

* **Email:** `admin@donasioksigen.com`
* **Password:** `admin123` *(Atau sesuaikan dengan data di database)*

## 📂 Struktur Folder
