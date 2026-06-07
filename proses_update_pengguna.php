<?php
session_start();
include 'koneksi.php';

// Pastikan request datang dari form POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_pengguna.php");
    exit();
}

$id   = (int)$_POST['id'];
$role = $_POST['role'] ?? '';

$username = $_POST['username'];
$password = $_POST['password'];

$pake_password_baru = !empty($password);


if ($role === 'admin') {
    // Admin Hanya update username (+ password jika diisi)
    if ($pake_password_baru) {
        $query = "UPDATE users SET username = ?, password = ? WHERE id = ?";
        $stmt  = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $username, $password, $id);
    } else {
        $query = "UPDATE users SET username = ? WHERE id = ?";
        $stmt  = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "si", $username, $id);
    }

} elseif ($role === 'panitia') {
    // Panitia : Update username, prodi (+ password jika diisi)
    $prodi = $_POST['prodi'] ?? null;

    if ($pake_password_baru) {
        $query = "UPDATE users SET username = ?, password = ?, prodi = ? WHERE id = ?";
        $stmt  = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $username, $password, $prodi, $id);
    } else {
        $query = "UPDATE users SET username = ?, prodi = ? WHERE id = ?";
        $stmt  = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssi", $username, $prodi, $id);
    }

} elseif ($role === 'peserta') {
    // Peserta bisa update NIM, username, nama_lengkap, email, prodi, angkatan, no_hp (+ password jika diisi)
    $nim          = $_POST['nim'] ?? null;
    $nama_lengkap = $_POST['nama_lengkap'] ?? null;
    $email        = $_POST['email'] ?? null;
    $prodi        = $_POST['prodi'] ?? null;
    $angkatan     = $_POST['angkatan'] ?? null;
    $no_hp        = $_POST['no_hp'] ?? null;

    if ($pake_password_baru) {
        $query = "UPDATE users SET nim = ?, username = ?, nama_lengkap = ?, email = ?, password = ?, prodi = ?, angkatan = ?, no_hp = ? WHERE id = ?";
        $stmt  = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssssssssi", $nim, $username, $nama_lengkap, $email, $password, $prodi, $angkatan, $no_hp, $id);
    } else {
        $query = "UPDATE users SET nim = ?, username = ?, nama_lengkap = ?, email = ?, prodi = ?, angkatan = ?, no_hp = ? WHERE id = ?";
        $stmt  = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "sssssssi", $nim, $username, $nama_lengkap, $email, $prodi, $angkatan, $no_hp, $id);
    }
} else {
    die("Role tidak dikenali!");
}

// proses database
if (!$stmt) {
    die("Query SQL Gagal disiapkan: " . mysqli_error($koneksi));
}

if (mysqli_stmt_execute($stmt)) {
    // Jika admin mengupdate akunnya sendiri, sinkronkan session nama terbarunya
    if ($id === $_SESSION['user_id']) {
        $_SESSION['username'] = $username;
    }
    
    $_SESSION['toast_msg'] = "Data " . ucfirst($role) . " berhasil diperbarui!";
    $_SESSION['toast_type'] = "success";
    header("Location: kelola_pengguna.php");
    exit();
} else {
    echo "Gagal memperbarui data: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
