<?php

session_start();
include 'koneksi.php';
// Jika pengguna sudah login (sudah memiliki sesi), cegah mereka untuk mendaftar lagi
//  langsung di arahin ke halaman dashboard.
if (isset($_SESSION['user_role'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // membersihkan spasi berlebih di awal/akhir input
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $nim = trim($_POST['nim']);
    $email = trim($_POST['email']);
    $prodi = trim($_POST['prodi']);
    $angkatan = trim($_POST['angkatan']);
    $no_hp = trim($_POST['no_hp']);


    
    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($nim) ||empty($email) ||empty($prodi) || empty($angkatan) || empty($no_hp)) {
        $_SESSION['register_error'] = "Semua kolom wajib diisi!";
        header("Location: register.php");
        exit();
    }

    //Pengecekan Ketersediaan Username
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

 
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $role = 'peserta'; 
    
    // Memasukkan data akun baru ke dalam tabel 'users'
    $insert_stmt = mysqli_prepare($koneksi, "INSERT INTO users (nim, username, nama_lengkap, email, password, role, prodi, angkatan, no_hp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($insert_stmt, "sssssssss", $nim, $username, $nama_lengkap, $email, $hashed_password, $role, $prodi, $angkatan, $no_hp);
    
    if (mysqli_stmt_execute($insert_stmt)) {
        $_SESSION['register_success'] = "Akun berhasil dibuat! Silakan masuk.";
    } else {
        $_SESSION['register_error'] = "Terjadi kesalahan saat mendaftar. Coba lagi.";
    }
    mysqli_stmt_close($insert_stmt);
}

header("Location: register.php");
exit();
?>
