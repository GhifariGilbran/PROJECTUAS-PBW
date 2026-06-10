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
    $stmt = mysqli_prepare($koneksi, "UPDATE events SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['toast_msg'] = "Event '" . $event_name . "' " . ($status === 'approve' ? 'berhasil disetujui!' : 'telah ditolak.');
            $_SESSION['toast_type'] = ($status === 'approve') ? 'success' : 'info';
        }
}

header("Location: kelola_event.php");
exit();
?>
