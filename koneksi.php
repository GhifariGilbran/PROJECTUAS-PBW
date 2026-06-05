<?php
// 1. Konfigurasi Parameter Database
$host     = "localhost";     // Nama host (tetap localhost jika di komputer sendiri/XAMPP)
$user     = "root";          // Username database bawaan XAMPP adalah root
$password = "";              // Password database bawaan XAMPP kosong/tanpa password
$database = "univent";       // Nama database yang telah Anda buat di phpMyAdmin

// 2. Membuat Koneksi ke MySQL
$koneksi = mysqli_connect($host, $user, $password, $database);

// 3. Memeriksa Apakah Koneksi Berhasil atau Gagal
if (!$koneksi) {
    // Jika gagal, hentikan program dan tampilkan pesan error
    die("Koneksi ke database 'univent' gagal: " . mysqli_connect_error());
}

// 4. Mengatur Charset ke utf8mb4 (Agar mendukung pembacaan karakter simbol/link poster)
mysqli_set_charset($koneksi, "utf8mb4");

