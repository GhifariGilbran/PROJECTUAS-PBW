<?php

session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: event.php");
    exit();
}

$user_id  = (int) $_SESSION['user_id'];
$event_id = (int) ($_POST['event_id'] ?? 0);

if ($event_id === 0) {
    $_SESSION['toast_msg']  = "Event tidak valid.";
    $_SESSION['toast_type'] = "info";
    header("Location: event.php");
    exit();
}

// Cek file diupload
if (!isset($_FILES['bukti_hadir']) || $_FILES['bukti_hadir']['error'] !== 0) {
    $_SESSION['toast_msg']  = "File bukti kehadiran wajib diupload.";
    $_SESSION['toast_type'] = "info";
    header("Location: event_detail.php?id=" . $event_id);
    exit();
}

// Buat folder jika belum ada
$uploadFileDir = 'uploads/bukti_hadir/';
if (!is_dir($uploadFileDir)) {
    mkdir($uploadFileDir, 0755, true);
}

$fileTmpPath    = $_FILES['bukti_hadir']['tmp_name'];
$fileName       = basename($_FILES['bukti_hadir']['name']);
$customFileName = time() . '_' . $fileName;
$dest_path      = $uploadFileDir . $customFileName;

// Validasi tipe file
$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
if (!in_array($_FILES['bukti_hadir']['type'], $allowedTypes)) {
    $_SESSION['toast_msg']  = "Tipe file tidak didukung. Gunakan JPG, PNG, atau PDF.";
    $_SESSION['toast_type'] = "info";
    header("Location: event_detail.php?id=" . $event_id);
    exit();
}

if (!move_uploaded_file($fileTmpPath, $dest_path)) {
    $_SESSION['toast_msg']  = "Gagal mengunggah file. Periksa izin folder uploads/bukti_hadir/.";
    $_SESSION['toast_type'] = "info";
    header("Location: event_detail.php?id=" . $event_id);
    exit();
}

// Simpan ke database: update baris registration milik peserta ini untuk event ini
$stmt = mysqli_prepare($koneksi, "UPDATE registration SET bukti_hadir = ?, waktu_hadir = NOW(), status = 'hadir' WHERE peserta_id = ? AND event_id = ?");
mysqli_stmt_bind_param($stmt, "sii", $customFileName, $user_id, $event_id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    $_SESSION['toast_msg']  = "Upload bukti kehadiran berhasil!";
    $_SESSION['toast_type'] = "success";
} else {
    // Tidak ada baris registration yang cocok -> hapus file yang sudah terupload
    unlink($dest_path);
    $_SESSION['toast_msg']  = "Gagal menyimpan. Pastikan kamu sudah terdaftar pada event ini.";
    $_SESSION['toast_type'] = "info";
}

mysqli_stmt_close($stmt);
header("Location: event_detail.php?id=" . $event_id);
exit();