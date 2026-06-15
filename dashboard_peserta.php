<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$role     = $_SESSION['user_role'] ?? '';
$username = $_SESSION['username'] ?? '';
$user_id  = $_SESSION['user_id']  ?? 0;

if ($role !== 'peserta') {
    header("Location: dashboard.php");
    exit();
}

// DATA PROFIL PESERTA
$profil_stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($profil_stmt, "i", $user_id);
mysqli_stmt_execute($profil_stmt);
$profil = mysqli_fetch_assoc(mysqli_stmt_get_result($profil_stmt));
mysqli_stmt_close($profil_stmt);

// STATISTIK 
$total_event_res = mysqli_query($koneksi, "SELECT COUNT(*) as count FROM events WHERE status = 'approve'");
$total_event     = mysqli_fetch_assoc($total_event_res)['count'] ?? 0;

$my_reg_stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) as count FROM registration WHERE peserta_id = ?");
mysqli_stmt_bind_param($my_reg_stmt, "i", $user_id);
mysqli_stmt_execute($my_reg_stmt);
$my_reg_count = mysqli_fetch_assoc(mysqli_stmt_get_result($my_reg_stmt))['count'] ?? 0;
mysqli_stmt_close($my_reg_stmt);

$hadir_stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) as count FROM registration WHERE peserta_id = ? AND status = 'hadir'");
mysqli_stmt_bind_param($hadir_stmt, "i", $user_id);
mysqli_stmt_execute($hadir_stmt);
$hadir_count = mysqli_fetch_assoc(mysqli_stmt_get_result($hadir_stmt))['count'] ?? 0;
mysqli_stmt_close($hadir_stmt);

$sertif_stmt = mysqli_prepare($koneksi, "
    SELECT COUNT(*) as count FROM certificate c
    JOIN registration r ON c.registration_id = r.id
    WHERE r.peserta_id = ?
");
mysqli_stmt_bind_param($sertif_stmt, "i", $user_id);
mysqli_stmt_execute($sertif_stmt);
$sertif_count = mysqli_fetch_assoc(mysqli_stmt_get_result($sertif_stmt))['count'] ?? 0;
mysqli_stmt_close($sertif_stmt);

// ── EVENT MENDATANG 
$upcoming_stmt = mysqli_prepare($koneksi, "
    SELECT e.id, e.name, e.tgl_mulai, e.lokasi
    FROM events e
    WHERE e.status = 'approve' AND e.tgl_mulai >= NOW()
    ORDER BY e.tgl_mulai ASC
    LIMIT 5
");
mysqli_stmt_execute($upcoming_stmt);
$upcoming_res = mysqli_stmt_get_result($upcoming_stmt);

// ── DAFTAR EVENT PESERTA 
$my_events_stmt = mysqli_prepare($koneksi, "
    SELECT
        e.id, e.name, e.tgl_mulai, e.tgl_selesai, e.lokasi,
        r.id AS reg_id, r.waktu_daftar, r.status AS reg_status, r.kode_unik
    FROM registration r
    JOIN events e ON r.event_id = e.id
    WHERE r.peserta_id = ?
    ORDER BY r.waktu_daftar DESC
");
mysqli_stmt_bind_param($my_events_stmt, "i", $user_id);
mysqli_stmt_execute($my_events_stmt);
$my_events_res = mysqli_stmt_get_result($my_events_stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Peserta - UniVent</title>
  <link rel="stylesheet" href="style.css?v=1.1">
  <link rel="stylesheet" href="dashboard_peserta.css">
</head>
<body>

  <header>
    <div class="logo-container">
      <h1 class="logo-title">Uni<span>Vent</span></h1>
      <span class="logo-subtitle">University Event</span>
    </div>
    <div class="header-right">
      <div class="user-profile-meta">
        <span class="user-info-role"><?php echo htmlspecialchars(ucfirst($role)); ?></span>
      </div>
      <a href="logout.php" class="logout-btn-header">Keluar</a>
    </div>
  </header>

  <div class="app-container">

   <aside id="sidebar">
  <div class="menu-group">
    <span class="menu-title">Menu Utama</span>
    <ul class="menu-items">
      <li>
        <a href="dashboard_peserta.php" class="menu-link active">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
          Dashboard
        </a>
      </li>
      <li>
        <a href="event.php" class="menu-link">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          Cari Event
        </a>
      </li>
      <li>
        <a href="my_events_peserta.php" class="menu-link">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
          Event Saya
        </a>
      </li>
      <li>
        <a href="sertifikat_peserta.php" class="menu-link">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
          Sertifikat
        </a>
      </li>
    </ul>

    <span class="menu-title" style="margin-top:1rem;">Manajemen Pembayaran</span>
    <ul class="menu-items">
      <li>
        <a href="pembayaran_peserta.php" class="menu-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="6" y1="15" x2="10" y2="15"/></svg>          
          Pembayaran Event
        </a>
      </li>
    </ul>

    <span class="menu-title" style="margin-top:1rem;">Pengaturan</span>
    <ul class="menu-items">
      <li>
        <a href="profil_peserta.php" class="menu-link">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
          Profil
        </a>
      </li>
    </ul>
  </div>
</aside>

    <main>
      <div class="view-header">
        <h2 class="view-title">Dashboard Peserta</h2>
      </div>

      <!-- KARTU SAMBUTAN -->
      <div class="welcome-card">
        <div class="welcome-left">
          <h2>Halo, <?php echo htmlspecialchars($profil['nama_lengkap'] ?? $username); ?>..</h2>
          <p>Selamat datang kembali, silahkan jelajahi event untuk anda ikuti</p>
        </div>
        <?php if (!empty($profil['nim'])): ?>
        <div class="nim-box">
          <span class="nim-label">NIM</span>
          <span class="nim-value"><?php echo htmlspecialchars($profil['nim']); ?></span>
        </div>
        <?php endif; ?>
      </div>

      <!-- STAT CARDS -->
      <div class="stats-grid">
        <div class="stat-card">
          <span class="stat-label">Total Event Tersedia</span>
          <span class="stat-value"><?php echo $total_event; ?></span>
          <span class="stat-sublabel">Dapat Di Ikuti &nbsp;<a href="event.php" class="stat-link">Lihat →</a></span>
        </div>
        <div class="stat-card">
          <span class="stat-label">Event diikuti</span>
          <span class="stat-value blue"><?php echo $my_reg_count; ?></span>
          <span class="stat-sublabel">Dari Semua Event di Ikuti</span>
        </div>
        <div class="stat-card">
          <span class="stat-label">Kehadiran</span>
          <span class="stat-value green"><?php echo $hadir_count; ?></span>
          <span class="stat-sublabel">Dari <?php echo $my_reg_count; ?> Event Diikuti</span>
        </div>
        <div class="stat-card">
          <span class="stat-label">Sertifikat Diterima</span>
          <span class="stat-value yellow"><?php echo $sertif_count; ?></span>
          <span class="stat-sublabel">Tersedia</span>
        </div>
      </div>

      <!-- DUA KOLOM: Event Mendatang | Event Saya -->
      <div class="dashboard-body">

        <!-- Event Mendatang -->
        <div class="panel upcoming-panel">
          <div class="panel-title">Event Mendatang</div>
          <?php
          $has_upcoming = false;
          while ($up = mysqli_fetch_assoc($upcoming_res)):
            $has_upcoming = true;
            $day = date('d', strtotime($up['tgl_mulai']));
            $mon = date('M', strtotime($up['tgl_mulai']));
          ?>
            <a href="event_detail.php?id=<?php echo $up['id']; ?>" class="upcoming-item">
              <div class="upcoming-date-badge">
                <span class="day"><?php echo $day; ?></span>
                <span class="mon"><?php echo $mon; ?></span>
              </div>
              <div class="upcoming-item-info">
                <span class="ev-name"><?php echo htmlspecialchars($up['name']); ?></span>
                <span class="ev-loc"><?php echo htmlspecialchars($up['lokasi'] ?? '-'); ?></span>
              </div>
            </a>
          <?php endwhile; mysqli_stmt_close($upcoming_stmt); ?>
          <?php if (!$has_upcoming): ?>
            <div class="upcoming-empty">Belum ada event mendatang.</div>
          <?php endif; ?>
        </div>

        <!-- Event Saya (Terdaftar) -->
        <div class="panel my-events-panel">
          <div class="panel-title blue-title">Event Saya (Terdaftar)</div>
          <div class="table-scroll">
            <table>
              <thead>
                <tr>
                  <th>Nama Event</th>
                  <th>Lokasi</th>
                  <th>Tgl Selesai</th>
                  <th>Tgl Daftar</th>
                  <th>Kode Unik</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $has_event = false;
                while ($ev = mysqli_fetch_assoc($my_events_res)):
                  $has_event   = true;
                  $tgl_selesai = !empty($ev['tgl_selesai'])  ? date('d - m - Y', strtotime($ev['tgl_selesai']))  : '-';
                  $tgl_daftar  = !empty($ev['waktu_daftar']) ? date('d - m - Y', strtotime($ev['waktu_daftar'])) : '-';
                  $reg_status  = $ev['reg_status'] ?? 'terdaftar';
                  $badge_label = match($reg_status) {
                      'hadir'       => 'Hadir',
                      'tidak_hadir' => 'Tidak Hadir',
                      'pending_payment' => 'Belum Bayar',
                      default       => 'Terdaftar'
                  };
                ?>
                  <tr>
                    <td><?php echo htmlspecialchars($ev['name']); ?></td>
                    <td><?php echo htmlspecialchars($ev['lokasi'] ?? '-'); ?></td>
                    <td><?php echo $tgl_selesai; ?></td>
                    <td><?php echo $tgl_daftar; ?></td>
                    <td><span class="kode-unik"><?php echo htmlspecialchars($ev['kode_unik']); ?></span></td>
                    <td><span class="reg-badge <?php echo htmlspecialchars($reg_status); ?>"><?php echo $badge_label; ?></span></td>
                    <td>
                      <a href="event_detail.php?id=<?php echo $ev['id']; ?>" class="btn-edit-sm">Detail</a>
                    </td>
                  </tr>
                <?php
                endwhile;
                mysqli_stmt_close($my_events_stmt);
                if (!$has_event):
                ?>
                  <tr>
                    <td colspan="7" class="empty-row">
                      Belum ada event yang diikuti.
                      <a href="event.php" class="stat-link" style="margin-left:0.5rem;">Cari Event →</a>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>

</body>
</html>