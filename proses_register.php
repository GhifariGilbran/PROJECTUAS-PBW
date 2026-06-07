<?php
/**
 * File: proses_register.php
 * Deskripsi: File ini bertugas sebagai pemroses (controller) di balik layar untuk pendaftaran akun baru.
 *            Tugas utamanya adalah menerima data dari form register, memvalidasi kelengkapan data, 
 *            memastikan username belum terpakai, dan mengamankan password sebelum menyimpannya ke database.
 *            File ini TIDAK menampilkan tampilan apapun, melainkan langsung mengarahkan pengguna kembali 
 *            ke halaman register dengan pesan sukses atau error.
 */

session_start();
include 'koneksi.php'; // Menghubungkan ke database untuk keperluan pengecekan dan penyimpanan data

// BLOK 1: Pengecekan Status Login
// Jika pengguna sudah login (sudah memiliki sesi), kita cegah mereka untuk mendaftar lagi
// dan langsung kita arahkan ke halaman dashboard.
if (isset($_SESSION['user_role'])) {
    header("Location: dashboard.php");
    exit();
}

// BLOK 2: Pemrosesan Data Pendaftaran
// Blok ini akan dieksekusi hanya jika pengguna mengirim data melalui metode POST (menekan tombol submit form).
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil input dari form dan membersihkan spasi berlebih di awal/akhir input
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nim = trim($_POST['nim']);
    $email = trim($_POST['email']);
    $prodi = trim($_POST['prodi']);
    $angkatan = trim($_POST['angkatan']);
    $no_hp = trim($_POST['no_hp']);


    
    // BLOK 2A: Validasi Input Kosong
    // Pastikan pengguna tidak mengirimkan form yang kosong. Jika ada yang kosong, kembalikan error.
    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($nim) ||empty($email) ||empty($prodi) || empty($angkatan) || empty($no_hp)) {
        $_SESSION['register_error'] = "Semua kolom wajib diisi!";
        header("Location: register.php");
        exit();
    }

    // BLOK 2B: Pengecekan Ketersediaan Username
    // Kita harus memastikan tidak ada username yang ganda di database.
    // Menggunakan prepared statement untuk mencegah SQL Injection.
    $check_stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($check_stmt, "s", $username);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
    
    // Jika hasil query menemukan > 0 baris, berarti username sudah dipakai orang lain.
    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $_SESSION['register_error'] = "Username sudah digunakan, silakan pilih yang lain.";
        mysqli_stmt_close($check_stmt);
        header("Location: register.php");
        exit();
    }
    mysqli_stmt_close($check_stmt); // Tutup statement pengecekan

    // BLOK 2C: Penyimpanan Data Akun Baru
    // Mengenkripsi password menggunakan password_hash (bcrypt) agar aman jika database bocor.
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Setiap pendaftaran mandiri otomatis akan dijadikan role 'peserta'.
    $role = 'peserta'; 
    
    // Memasukkan data akun baru ke dalam tabel 'users'
    $insert_stmt = mysqli_prepare($koneksi, "INSERT INTO users (nim, username, nama_lengkap, email, password, role, prodi, angkatan, no_hp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($insert_stmt, "sssssssss", $nim, $username, $nama_lengkap, $email, $hashed_password, $role, $prodi, $angkatan, $no_hp);
    
    // Eksekusi penyimpanan. Jika berhasil, buat pesan sukses. Jika gagal, buat pesan error.
    if (mysqli_stmt_execute($insert_stmt)) {
        $_SESSION['register_success'] = "Akun berhasil dibuat! Silakan masuk.";
    } else {
        $_SESSION['register_error'] = "Terjadi kesalahan saat mendaftar. Coba lagi.";
    }
    mysqli_stmt_close($insert_stmt);
}

// Langkah terakhir: Kembalikan pengguna ke halaman register agar mereka bisa melihat pesan error/sukses.
header("Location: register.php");
exit();
?>
