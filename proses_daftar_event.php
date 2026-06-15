<?php
session_start();
include 'koneksi.php';

// cek autentikasi
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    $event_id = (int)$_POST['event_id'];

    // mencari event ke database
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

    // mengecek apakah peserta sudah terdaftar atau belum
    $reg_stmt = mysqli_prepare($koneksi, "SELECT id FROM registration WHERE peserta_id = ? AND event_id = ?");
    mysqli_stmt_bind_param($reg_stmt, "ii", $user_id, $event_id);
    mysqli_stmt_execute($reg_stmt);
    mysqli_stmt_store_result($reg_stmt);
    $is_registered = (mysqli_stmt_num_rows($reg_stmt) > 0);
    mysqli_stmt_close($reg_stmt);

    if (!$is_registered && $event['quota'] > 0) {

        mysqli_begin_transaction($koneksi);

        try {
            $kode_unik = strtoupper(uniqid('EVT-'));

            $ins_stmt = mysqli_prepare($koneksi, "INSERT INTO registration (peserta_id, event_id, kode_unik) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($ins_stmt, "iis", $user_id, $event_id, $kode_unik);
            mysqli_stmt_execute($ins_stmt);
            mysqli_stmt_close($ins_stmt);

            $upd_stmt = mysqli_prepare($koneksi, "UPDATE events SET quota = quota - 1 WHERE id = ? AND quota > 0");
            mysqli_stmt_bind_param($upd_stmt, "i", $event_id);
            mysqli_stmt_execute($upd_stmt);
            mysqli_stmt_close($upd_stmt);

            mysqli_commit($koneksi);

            $nama_event = $event['name'];
            $_SESSION['toast_msg'] = "Pendaftaran Berhasil! Anda terdaftar pada event " . $nama_event;
            $_SESSION['toast_type'] = 'success';

        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            echo "DEBUG ERROR: " . $e->getMessage();
            exit();
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