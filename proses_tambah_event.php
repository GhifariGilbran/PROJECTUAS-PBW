<?php
session_start();
include 'koneksi.php';

// Proteksi Halaman Khusus Admin & Panitia
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'panitia')) {
    header("Location: dashboard.php");
    exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $panitia = $_POST['panitia'];
    $category = $_POST['category'];
    $tglmulai = $_POST['tglmulai'];
    $tglselesai = $_POST['tglselesai'];
    $location = $_POST['location'];
    $quota = (int)$_POST['quota'];
    $price = $_POST['price'];
    $desc = $_POST['desc'];

    // --- PROSES UPLOAD GAMBAR BARU (PERBAIKAN) ---
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
        $fileTmpPath = $_FILES['poster']['tmp_name'];
        $fileName = $_FILES['poster']['name'];
        
        // Membuat nama file unik agar tidak bentrok (Contoh: 17123456_poster.jpg)
        $customFileName = time() . '_' . $fileName; 
        
        // Tentukan folder tujuan (Pastikan Anda sudah membuat folder bernama 'uploads' di direktori project)
        $uploadFileDir = 'uploads/';
        $dest_path = $uploadFileDir . $customFileName;

        // Pindahkan file asli ke folder 'uploads'
        if(move_uploaded_file($fileTmpPath, $dest_path)) {
            // Jika berhasil pindah folder, nama file unik ini yang disimpan ke database
            $posterDatabase = $customFileName; 
        } else {
            $_SESSION['tambah_event_error'] = "Gagal mengunggah gambar ke folder server. Cek izin folder uploads Anda.";
            header("Location: tambah_event.php");
            exit();
        }
    } else {
        $_SESSION['tambah_event_error'] = "Gambar poster wajib diunggah.";
        header("Location: tambah_event.php");
        exit();
    }
    // ---------------------------------------------

    // Query INSERT (Menyimpan nama file poster saja ke database)
    $stmt = mysqli_prepare($koneksi, "INSERT INTO events (name, panitia_id, category_id, tgl_mulai, tgl_selesai, lokasi, quota, harga, deskripsi, poster) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    mysqli_stmt_bind_param($stmt, "siisssiiss", $name, $panitia, $category, $tglmulai, $tglselesai, $location, $quota, $price, $desc, $posterDatabase);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['toast_msg'] = "Event berhasil ditambahkan!";
        $_SESSION['toast_type'] = "success";
        mysqli_stmt_close($stmt);
        header("Location: kelola_event.php");
        exit();
    } else {
        $_SESSION['tambah_event_error'] = "Terjadi kesalahan saat menyimpan data ke database.";
    }
    mysqli_stmt_close($stmt);
}

header("Location: tambah_event.php");
exit();
?>