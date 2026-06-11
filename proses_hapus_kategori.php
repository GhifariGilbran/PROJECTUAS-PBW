<?php
session_start();
include 'koneksi.php';

// Check authentication
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}


// $id = $_GET['id'];
// echo (int)$_GET['id'];

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['toast_msg'] = "Kategori berhasil dihapus!";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_msg'] = "Gagal menghapus kategori.";
        $_SESSION['toast_type'] = "danger";
    }
    mysqli_stmt_close($stmt);
}

header("Location: kategori.php");
exit();
