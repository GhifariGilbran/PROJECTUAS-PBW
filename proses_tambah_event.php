<?php
/**
 * File: proses_tambah_event.php
 * Deskripsi: File backend ini bertanggung jawab secara eksklusif untuk memproses penambahan event baru 
 *            yang dikirimkan oleh Admin. File ini akan menerima data form dari tambah_event.php, 
 *            memasukkannya ke database dengan status bawaan 'Approved', lalu mengarahkan Admin 
 *            kembali ke halaman kelola event dengan pesan sukses.
 */

session_start();
include 'koneksi.php';

// BLOK 1: Proteksi Halaman Khusus Admin
// Memastikan hanya pengguna yang sudah login DAN memiliki role 'admin' yang bisa mengakses proses ini.
// Jika peserta biasa atau orang belum login mencoba mengakses file ini, tendang mereka ke dashboard.
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// BLOK 2: Pemrosesan Data Event Baru
// Proses berjalan saat Admin mengirimkan form (POST request).
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Mengambil semua data yang diinputkan Admin dari form.
    $name = $_POST['name'];
    $panitia = $_POST['panitia'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    // Kuota dikonversi menjadi integer (angka bulat) agar aman.
    $quota = (int)$_POST['quota'];
    $price = $_POST['price'];
    $desc = $_POST['desc'];
    $poster = $_POST['poster'];
    
    // Menetapkan status bawaan 'Approved' (Disetujui) karena event ini dibuat langsung oleh Admin.
    $status = 'Approved'; 

    // BLOK 2A: Simpan ke Database
    // Menggunakan prepared statement untuk menghindari injeksi SQL dari karakter aneh yang mungkin terinput.
    // Query INSERT memasukkan 10 kolom data sekaligus.
    $stmt = mysqli_prepare($koneksi, "INSERT INTO events (name, panitia, category, date, location, quota, price, `desc`, poster, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Binding parameter: "sssssissss" berarti (String, String, String, String, String, Integer, String, String, String, String)
    mysqli_stmt_bind_param($stmt, "sssssissss", $name, $panitia, $category, $date, $location, $quota, $price, $desc, $poster, $status);
    
    // Eksekusi penyimpanan dan pengecekan hasilnya
    if (mysqli_stmt_execute($stmt)) {
        // Jika berhasil tersimpan, siapkan pesan notifikasi toast warna hijau (success)
        $_SESSION['toast_msg'] = "Event berhasil ditambahkan!";
        $_SESSION['toast_type'] = "success";
        mysqli_stmt_close($stmt);
        
        // Arahkan Admin kembali ke halaman kelola event agar bisa melihat list terupdate
        header("Location: kelola_event.php");
        exit();
    } else {
        // Jika gagal tersimpan (misalnya kesalahan koneksi/struktur), kembalikan pesan error ke halaman form
        $_SESSION['tambah_event_error'] = "Terjadi kesalahan saat menyimpan event.";
    }
    mysqli_stmt_close($stmt);
}

// Jika terjadi error (atau file diakses langsung tanpa POST), kembalikan admin ke halaman form tambah event
header("Location: tambah_event.php");
exit();
?>
