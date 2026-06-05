<?php
session_start();
include 'koneksi.php';

// Check authentication
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['toast_msg'] = "Pengguna berhasil dihapus!";
        $_SESSION['toast_type'] = "success";
    } else {
        $_SESSION['toast_msg'] = "Gagal menghapus pengguna.";
        $_SESSION['toast_type'] = "danger";
    }
    mysqli_stmt_close($stmt);
}

header("Location: kelola_pengguna.php");
exit();
?>
