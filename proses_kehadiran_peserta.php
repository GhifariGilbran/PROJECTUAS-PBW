<?php
session_start();
include 'koneksi.php';

// Check authentication & role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$selected_event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

if (isset($_GET['action']) && $_GET['action'] === 'toggle_presence' && isset($_GET['reg_id'])) {
    $reg_id = (int)$_GET['reg_id'];
    
    // Check current status
    $curr_stmt = mysqli_prepare($koneksi, "SELECT presence_status FROM registrations WHERE id = ?");
    mysqli_stmt_bind_param($curr_stmt, "i", $reg_id);
    mysqli_stmt_execute($curr_stmt);
    mysqli_stmt_bind_result($curr_stmt, $curr_status);
    mysqli_stmt_fetch($curr_stmt);
    mysqli_stmt_close($curr_stmt);
    
    $new_status = ($curr_status === 'Hadir') ? 'Belum Hadir' : 'Hadir';
    // Format: 10 - 24 - 2026 10.30
    $new_time = ($new_status === 'Hadir') ? date('m - d - Y H.i') : '-';
    
    $upd_stmt = mysqli_prepare($koneksi, "UPDATE registrations SET presence_status = ?, presence_time = ? WHERE id = ?");
    mysqli_stmt_bind_param($upd_stmt, "ssi", $new_status, $new_time, $reg_id);
    mysqli_stmt_execute($upd_stmt);
    mysqli_stmt_close($upd_stmt);
}

header("Location: peserta.php?event_id=" . $selected_event_id);
exit();
?>
