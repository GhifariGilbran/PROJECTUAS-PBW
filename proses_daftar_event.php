<?php
session_start();
include 'koneksi.php';

// Check authentication
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    $event_id = (int)$_POST['event_id'];

    // Find the event in the database
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM events WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $event_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$event) {
        header("Location: event.php");
        exit();
    }

    // Check if already registered
    $reg_stmt = mysqli_prepare($koneksi, "SELECT id FROM registrations WHERE user_id = ? AND event_id = ?");
    mysqli_stmt_bind_param($reg_stmt, "ii", $user_id, $event_id);
    mysqli_stmt_execute($reg_stmt);
    mysqli_stmt_store_result($reg_stmt);
    $is_registered = (mysqli_stmt_num_rows($reg_stmt) > 0);
    mysqli_stmt_close($reg_stmt);

    if (!$is_registered && $event['quota'] > 0) {
        // Begin Transaction for reliability
        mysqli_begin_transaction($koneksi);
        
        try {
            // 1. Insert registration record
            $ins_stmt = mysqli_prepare($koneksi, "INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($ins_stmt, "ii", $user_id, $event_id);
            mysqli_stmt_execute($ins_stmt);
            mysqli_stmt_close($ins_stmt);
            
            // 2. Decrement quota in events table
            $upd_stmt = mysqli_prepare($koneksi, "UPDATE events SET quota = quota - 1 WHERE id = ? AND quota > 0");
            mysqli_stmt_bind_param($upd_stmt, "i", $event_id);
            mysqli_stmt_execute($upd_stmt);
            mysqli_stmt_close($upd_stmt);
            
            // Commit transaction
            mysqli_commit($koneksi);
            
            $_SESSION['toast_msg'] = "Pendaftaran Berhasil! Anda terdaftar pada event '" . $event['name'] . "'";
            $_SESSION['toast_type'] = 'success';
        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($koneksi);
            $_SESSION['toast_msg'] = "Pendaftaran gagal! Silakan coba lagi.";
            $_SESSION['toast_type'] = 'info';
        }
    } else {
        $_SESSION['toast_msg'] = "Anda sudah terdaftar atau kuota sudah penuh.";
        $_SESSION['toast_type'] = 'info';
    }
    
    header("Location: event_detail.php?id=" . $event_id);
    exit();
}

header("Location: event.php");
exit();
?>
