<?php
session_start();
include 'koneksi.php';

// Cek login
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'panitia')) {
    header("Location: dashboard.php");
    exit();
}

// Pastikan data dikirim dengan POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: kategori.php");
    exit();
}

// Ambil data dari form
$id = (int)$_POST['id'];
$nama = trim($_POST['name']);
$deskripsi = trim($_POST['deskripsi']);

// Validasi
if (empty($nama)) {
    echo "<script>
            alert('Nama kategori tidak boleh kosong!');
            history.back();
          </script>";
    exit();
}

// Amankan input
$nama = mysqli_real_escape_string($koneksi, $nama);
$deskripsi = mysqli_real_escape_string($koneksi, $deskripsi);

// Update data
$query = mysqli_query($koneksi, "
    UPDATE categories
    SET
        nama = '$nama',
        deskripsi = '$deskripsi'
    WHERE id = $id
");

if ($query) {
    
    $_SESSION['toast_msg'] = "Data berhasil diperbarui!";
    $_SESSION['toast_type'] = "success";
    header("Location: kategori.php");
    exit();
} else {
    echo "Gagal memperbarui data: " . $query;
}