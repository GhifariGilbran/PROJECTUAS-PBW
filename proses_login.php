<?php
/**
 * File: proses_login.php
 * Deskripsi: File ini berfungsi khusus untuk memproses logika autentikasi login pengguna.
 *            File ini TIDAK menampilkan UI apa pun. Jika login berhasil, pengguna akan 
 *            diarahkan ke halaman dashboard. Jika gagal, error akan disimpan ke dalam 
 *            session dan pengguna dikembalikan ke halaman login utama (index.php).
 */

session_start();
include 'koneksi.php'; // Menghubungkan ke database agar bisa mengecek tabel 'users'

// BLOK 1: Proteksi Keamanan Ganda
// Jika pengguna ternyata sudah login (session role terdeteksi), 
// langsung alihkan ke dashboard agar tidak memproses login ulang.
if (isset($_SESSION['user_role'])) {
    header("Location: dashboard.php");
    exit();
}

// BLOK 2: Memproses Form POST
// Kode di dalam blok ini hanya dijalankan apabila ada request POST yang masuk (pengguna menekan tombol Masuk).
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil dan membersihkan input dari spasi di awal/akhir menggunakan trim()
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi input kosong: memastikan pengguna mengisi kedua kolom formulir
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Username dan Password wajib diisi!";
        header("Location: index.php");
        exit();
    }

    // Menyiapkan Prepared Statement untuk mencegah celah SQL Injection
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    // Mengecek apakah username tersebut terdaftar di database
    if ($row = mysqli_fetch_assoc($result)) {
        // Mendukung verifikasi password teks biasa (plain text) ATAU terenkripsi (hashed)
        // guna menjaga kompatibilitas data akun demo/admin bawaan.
        if ($password === $row['password'] || password_verify($password, $row['password'])) {
            
            // Login Sukses: Menyimpan informasi penting pengguna ke dalam Session PHP
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['nama_lengkap'];
            $_SESSION['user_role'] = $row['role']; // Menentukan hak akses (admin atau peserta)
            
            // Alihkan pengguna ke dashboard utama
            header("Location: dashboard.php");
            exit();
        } else {
            // Password salah: simpan pesan kesalahan ke session
            $_SESSION['login_error'] = "Password yang Anda masukkan salah!";
        }
    } else {
        // Username tidak ditemukan di database
        $_SESSION['login_error'] = "Username tidak ditemukan!";
    }
    mysqli_stmt_close($stmt);
}

// Jika proses gagal atau diakses secara ilegal (tanpa POST), kembalikan ke index.php
header("Location: login.php");
exit();
?>
