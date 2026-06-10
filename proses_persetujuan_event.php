<?php
session_start();
include 'koneksi.php';

// Check authentication & role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    $status = ($action === 'approve') ? 'approve' : (($action === 'reject') ? 'reject' : null);
    
    if ($status) {
        // Fetch event name first for toast message
        $name_stmt = mysqli_prepare($koneksi, "SELECT name FROM events WHERE id = ?");
        mysqli_stmt_bind_param($name_stmt, "i", $id);
        mysqli_stmt_execute($name_stmt);
        $name_res = mysqli_stmt_get_result($name_stmt);
        $event_name = "";
        if ($name_row = mysqli_fetch_assoc($name_res)) {
            $event_name = $name_row['name'];
        }
        mysqli_stmt_close($name_stmt);

        // Update status in DB
        $stmt = mysqli_prepare($koneksi, "UPDATE events SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['toast_msg'] = "Event '" . $event_name . "' " . ($status === 'approve' ? 'berhasil disetujui!' : 'telah ditolak.');
            $_SESSION['toast_type'] = ($status === 'Approved') ? 'success' : 'info';
        }
        mysqli_stmt_close($stmt);
    }
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>
