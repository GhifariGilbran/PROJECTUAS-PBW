<?php
include 'koneksi.php';
session_start();

$nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
$deskripsi = $_POST['deskripsi'];

$cek_nama = mysqli_query($koneksi, "SELECT * FROM categories WHERE nama = '$nama'");

if(mysqli_num_rows($cek_nama) > 0){
    $_SESSION['toast_msg'] = 'KATEGORI SUDAH ADA, TIDAK BISA MENAMBAHKAN !';
    $_SESSION['toast_type'] = 'error';

    header('Location: tambah_kategori.php');
    exit();
}

$query = mysqli_prepare($koneksi, "INSERT INTO categories (nama, deskripsi) VALUES (?, ?)");
mysqli_stmt_bind_param($query, "ss" , $nama, $deskripsi);
mysqli_stmt_execute($query);

    $_SESSION['toast_msg'] = 'Berhasil membuat kategori';
    $_SESSION['toast_type'] = 'success';

    header('Location: kategori.php');
    exit();