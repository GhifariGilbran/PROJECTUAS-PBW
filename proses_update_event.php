<?php
session_start();
include 'koneksi.php';

// Pastikan request datang dari form POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_event.php");
    exit();
}

$id_event  = $_POST['id'];
$nama = $_POST['nama'];
$mulai     = $_POST['mulai'];
$selesai   = $_POST['selesai'];
$lokasi    = $_POST['lokasi'];
$quota     = $_POST['quota'];
$harga     = $_POST['harga'];
$status    = $_POST['status'];
$deskripsi = $_POST['deskripsi'];

$query = mysqli_query($koneksi, "
    UPDATE events SET
        name = '$nama',
        tgl_mulai = '$mulai',
        tgl_selesai = '$selesai',
        lokasi = '$lokasi',
        quota = '$quota',
        harga = '$harga',
        status = '$status',
        deskripsi = '$deskripsi'
    WHERE id = '$id_event'
");



if ($query) {
    
    $_SESSION['toast_msg'] = "Data berhasil diperbarui!";
    $_SESSION['toast_type'] = "success";
    header("Location: kelola_event.php");
    exit();
} else {
    echo "Gagal memperbarui data: " . $query;
}

mysqli_close($koneksi);