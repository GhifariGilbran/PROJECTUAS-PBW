<?php

session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'panitia') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$event_id = (int)$_POST['event_id'];
$link_drive = mysqli_real_escape_string($koneksi,$_POST['link_drive']);

if ($event_id <= 0 || empty($link_drive)) {
    die("Data tidak lengkap");
}

$cek_event = mysqli_query($koneksi,"
    SELECT *
    FROM events
    WHERE id = $event_id
    AND panitia_id = $user_id
");

if(mysqli_num_rows($cek_event) == 0){
    die("Event tidak ditemukan");
}

$registrasi = mysqli_query($koneksi,"
    SELECT id
    FROM registration
    WHERE event_id = $event_id
    AND status = 'hadir'
");

$total = 0;

while($row = mysqli_fetch_assoc($registrasi))
{
    $registration_id = $row['id'];

    $cek = mysqli_query($koneksi,"
        SELECT id
        FROM certificate
        WHERE registration_id = $registration_id
    ");

    if(mysqli_num_rows($cek) == 0)
    {
        $kode = "CERT-".strtoupper(substr(md5(uniqid()),0,10));

        mysqli_query($koneksi,"
            INSERT INTO certificate
            (
                registration_id,
                kode_sertifikat,
                file_path,
                issued_at
            )
            VALUES
            (
                $registration_id,
                '$kode',
                '$link_drive',
                NOW()
            )
        ");

        $total++;
    }
}

echo "
<script>
alert('Berhasil upload sertifikat kepada $total orang yang hadir.');
window.location='sertifikat.php';
</script>
";