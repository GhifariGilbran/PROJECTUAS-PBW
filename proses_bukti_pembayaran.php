<?php
session_start();
include("koneksi.php");

if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: pembayaran_peserta.php");
    exit();
}

$id_regis = intval($_POST['id_reg'] ?? 0);

if ($id_regis === 0) {
    $_SESSION['toast_msg']  = "ID registrasi tidak valid.";
    $_SESSION['toast_type'] = "info";
    header("Location: upload_bukti.php");
    exit();
}

// Cek file diupload
if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== 0) {
    $_SESSION['toast_msg']  = "File bukti pembayaran wajib diupload.";
    $_SESSION['toast_type'] = "info";
    header("Location: upload_bukti.php");
    exit();
}

// Buat folder jika belum ada
$uploadFileDir = 'uploads/bukti_bayar/';
if (!is_dir($uploadFileDir)) {
    mkdir($uploadFileDir, 0755, true);
}

$fileTmpPath    = $_FILES['bukti']['tmp_name'];
$fileName       = basename($_FILES['bukti']['name']);
$customFileName = time() . '_' . $fileName;
$dest_path      = $uploadFileDir . $customFileName;

// Validasi tipe file
$allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
if (!in_array($_FILES['bukti']['type'], $allowedTypes)) {
    $_SESSION['toast_msg']  = "Tipe file tidak didukung. Gunakan JPG, PNG, atau PDF.";
    $_SESSION['toast_type'] = "info";
    header("Location: upload_bukti.php");
    exit();
}

if (!move_uploaded_file($fileTmpPath, $dest_path)) {
    $_SESSION['toast_msg']  = "Gagal mengunggah file. Periksa izin folder uploads/bukti_bayar/.";
    $_SESSION['toast_type'] = "info";
    header("Location: upload_bukti.php");
    exit();
}

// Simpan ke database
$stmt = mysqli_prepare($koneksi, "UPDATE payment SET bukti_pembayaran = ? WHERE registration_id = ?");
mysqli_stmt_bind_param($stmt, "si", $customFileName, $id_regis);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['toast_msg']  = "Upload berhasil!";
    $_SESSION['toast_type'] = "success";
} else {
    $_SESSION['toast_msg']  = "Gagal menyimpan ke database.";
    $_SESSION['toast_type'] = "info";
}

mysqli_stmt_close($stmt);
header("Location: pembayaran_peserta.php");
exit();