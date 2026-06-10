<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'panitia') {
    header("Location: login.php");
    exit();
}

$role         = $_SESSION['user_role'];
$username     = $_SESSION['username'] ?? 'Panitia';
$user_id      = $_SESSION['user_id'];

$id_event = $_GET['id'];

$search_query = "";
$peserta_list = [];
$event_nama   = "";
$message      = "";
$message_type = "";

// Handle update status kehadiran
if (isset($_POST['update_status'])) {
    $reg_id         = (int) $_POST['reg_id'];
    $status_baru    = mysqli_real_escape_string($koneksi, $_POST['status_baru']);
    $allowed_status = ['terdaftar', 'hadir', 'tidak_hadir'];

    if (in_array($status_baru, $allowed_status)) {
        $cek = mysqli_query($koneksi, "
            SELECT r.id FROM registration r
            JOIN events e ON r.event_id = e.id
            WHERE r.id = $reg_id AND e.panitia_id = $user_id
            LIMIT 1
        ");
        if (mysqli_num_rows($cek) > 0) {
            $waktu_hadir_sql = ($status_baru === 'hadir') ? ", waktu_hadir = NOW()" : ", waktu_hadir = NULL";
            mysqli_query($koneksi, "UPDATE registration SET status = '$status_baru' $waktu_hadir_sql WHERE id = $reg_id");
            $message      = "Status peserta berhasil diperbarui.";
            $message_type = "success";
        } else {
            $message      = "Aksi tidak diizinkan.";
            $message_type = "error";
        }
    }
    $search_query = $_POST['search_event'] ?? "";
}

// Handle pencarian event
if (isset($_POST['cari_event']) || !empty($search_query)) {
    if (isset($_POST['search_event'])) {
        $search_query = mysqli_real_escape_string($koneksi, $_POST['search_event']);
    }

    if (!empty($search_query)) {
        $res_event = mysqli_query($koneksi, "
            SELECT id, name FROM events
            WHERE name LIKE '%$search_query%' AND panitia_id = $user_id
            LIMIT 1
        ");

        if (mysqli_num_rows($res_event) > 0) {
            $event_row  = mysqli_fetch_assoc($res_event);
            $event_id   = $event_row['id'];
            $event_nama = $event_row['name'];

            $res_peserta = mysqli_query($koneksi, "
                SELECT
                    r.id            AS reg_id,
                    u.nama_lengkap,
                    u.nim,
                    u.prodi,
                    r.waktu_daftar,
                    r.waktu_hadir,
                    r.status
                FROM registration r
                JOIN users u ON r.peserta_id = u.id
                WHERE r.event_id = $event_id
                ORDER BY r.waktu_daftar ASC
            ");

            while ($row = mysqli_fetch_assoc($res_peserta)) {
                $peserta_list[] = $row;
            }

            if (empty($peserta_list)) {
                $message      = "Belum ada peserta yang mendaftar untuk event ini.";
                $message_type = "info";
            }
        } else {
            $message      = "Event tidak ditemukan atau bukan milik Anda.";
            $message_type = "error";
        }
    }
}

function fmt_date($dt) {
    if (empty($dt) || $dt === '0000-00-00 00:00:00') return '-';
    return date('d - m - Y H:i', strtotime($dt));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Peserta - UniVent</title>
  <link rel="stylesheet" href="style.css?v=1.2">
  <link rel="stylesheet" href="peserta_panitia.css?v=1.0">
</head>
<body>

  <header>
    <div class="logo-container">
      <h1 class="logo-title">Uni<span>Vent</span></h1>
      <span class="logo-subtitle">University Event</span>
    </div>
    <div class="header-right">
      <div class="user-profile-meta">
        <span class="user-info-role"><?php echo htmlspecialchars($username); ?></span>
      </div>
      <a href="logout.php" class="logout-btn-header">Keluar</a>
    </div>
  </header>

  <div class="app-container">

    <aside id="sidebar">
      <div class="menu-group">
        <span class="menu-title">MENU UTAMA</span>
        <ul class="menu-items">
          <li>
            <a href="dashboard.php" class="menu-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
              Dashboard
            </a>
          </li>
          <li>
            <a href="tambah_event.php" class="menu-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
              Buat Event
            </a>
          </li>
          <li>
            <a href="event_saya_panitia.php" class="menu-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
              Event Saya
            </a>
          </li>
          <li>
            <a href="peserta_panitia.php" class="menu-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
              Peserta
            </a>
          </li>
          <li>
            <a href="sertifikat.php" class="menu-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
              Sertifikat
            </a>
          </li>
          <li>
            <a href="sertifikat.php" class="menu-link" style="color: aqua; margin-left: 20%;">
              | Upload Sertifikat
            </a>
          </li>
        </ul>
      </div>
    </aside>

    <main>
      <section class="view-section active">
          <div class="view-header" style="flex-direction: column; align-items: flex-start; gap: 0.25rem; margin-bottom: 1.5rem;">
            <h2 class="view-title">Upload Sertifikat</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Uplaod sertifikat untuk peserta</p>
          </div>
            
        <p style="color: aqua;">Perlu Diperhatikan !</p>
    
        <ul style="list-style: none; color: var(--text-muted);">
            <li>1. Pastikan anda membuat sertifikat hanya untuk peserta yang hadir. <a href="peserta_panitia.php" style="color: aqua;">Lihat Kehadiran Peserta</a></li>
            <li>2. Upload semua sertifikat ke dalam folder Google Drive.</li>
            <li>3. Pastikan folder Google Drive yang anda buat bersifat publik (Tidak di private).</li>
            <li>4. Salin link folder Google Drive anda, lalu sisipkan link tersebut kesini :</li>
        </ul>
        <br>
        <?php

            $data = null;

            if(isset($_GET['id']) && $_GET['id'] != ''){

                $event_id = (int)$_GET['id'];

                $query = mysqli_query($koneksi,"
                    SELECT c.file_path
                    FROM certificate c
                    JOIN registration r ON c.registration_id = r.id
                    WHERE r.event_id = $event_id
                    LIMIT 1
                ");

                if(mysqli_num_rows($query) > 0){
                    $data = mysqli_fetch_assoc($query);
                }
            }

        ?>
        <form action="proses_upload_sertifikat.php" method="post">
             <input type="hidden" name="event_id" value="<?= $id_event ?>">
            <div class="form-row-2">
                <input type="text" name="link_drive" class="input-text" value="<?= htmlspecialchars($data['file_path'] ?? '') ?>" required >
                <div>
                    <button type="submit" class="btn-submit" style="padding: 0.6rem 1.5rem; margin: 0; width: auto;">Kirim</button>
                </div>
            </div>

        </form>
        </section>
    </main>

  </div>

</body>
</html>