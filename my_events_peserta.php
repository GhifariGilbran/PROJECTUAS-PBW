<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$role     = $_SESSION['user_role'];
$username = $_SESSION['username'];
$user_id  = $_SESSION['user_id'];

if ($role !== 'peserta') {
    header("Location: dashboard.php");
    exit();
}

// ── FILTER ───────────────────────────────────────────────────────────────────
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$status_f = isset($_GET['status'])   ? trim($_GET['status'])   : '';

// ── QUERY ────────────────────────────────────────────────────────────────────
$query = "
    SELECT
        e.id, e.name, e.deskripsi, e.tgl_mulai, e.tgl_selesai,
        e.lokasi, e.quota, e.harga,
        r.id          AS reg_id,
        r.waktu_daftar,
        r.status      AS reg_status,
        r.kode_unik
    FROM registration r
    JOIN events e ON r.event_id = e.id
    WHERE r.peserta_id = ?
";
$params = [$user_id];
$types  = "i";

if ($search !== '') {
    $query .= " AND (e.name LIKE ? OR e.deskripsi LIKE ? OR e.lokasi LIKE ?)";
    $s = "%$search%";
    $params[] = $s; $params[] = $s; $params[] = $s;
    $types .= "sss";
}

if ($status_f !== '') {
    $query .= " AND r.status = ?";
    $params[] = $status_f;
    $types .= "s";
}

$query .= " ORDER BY r.waktu_daftar DESC";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$events_res = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Event Saya - UniVent</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .badge-presence {
      display: inline-block;
      padding: 0.25rem 0.6rem;
      border-radius: 8px;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
    }
    .badge-presence.hadir       { background: rgba(46,213,115,0.1); color: var(--color-success); border: 1px solid rgba(46,213,115,0.2); }
    .badge-presence.tidak_hadir { background: rgba(255,100,100,0.1); color: #ff6464; border: 1px solid rgba(255,100,100,0.2); }
    .badge-presence.terdaftar   { background: rgba(111,208,246,0.1); color: #6fd0f6; border: 1px solid rgba(111,208,246,0.2); }
  </style>
</head>
<body>

  <header>
    <div class="logo-container">
      <h1 class="logo-title">Uni<span>Vent</span></h1>
      <span class="logo-subtitle">University Event</span>
    </div>
    <div class="header-right">
      <div class="user-profile-meta">
        <span class="user-info-text"><?php echo htmlspecialchars($username); ?></span>
        <span class="user-info-role"><?php echo htmlspecialchars($role); ?></span>
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
            <a href="dashboard_peserta.php" class="menu-link">
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
            <a href="event_saya_panitia.php" class="menu-link active">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
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
      <section class="view-section active">
        <div class="view-header" style="flex-direction:column;align-items:flex-start;gap:0.5rem;">
          <h2 class="view-title">Event Saya</h2>
          <p style="color:var(--text-muted);font-size:0.9rem;">Daftar event yang telah Anda ikuti dan status kehadiran Anda.</p>
        </div>

        <!-- FILTER -->
        <form action="event_saya_panitia.php" method="GET" class="search-filter-wrapper">
          <div class="search-input-group">
            <svg class="search-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" name="search" class="search-control" placeholder="Cari nama event, lokasi..." value="<?php echo htmlspecialchars($search); ?>">
          </div>
          <div class="filters-row">
            <div class="filter-group">
              <label class="filter-label">Status Kehadiran</label>
              <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="terdaftar"   <?php echo $status_f === 'terdaftar'   ? 'selected' : ''; ?>>Terdaftar</option>
                <option value="hadir"       <?php echo $status_f === 'hadir'       ? 'selected' : ''; ?>>Hadir</option>
                <option value="tidak_hadir" <?php echo $status_f === 'tidak_hadir' ? 'selected' : ''; ?>>Tidak Hadir</option>
              </select>
            </div>
            <div class="filter-group" style="margin-left:auto;align-self:flex-end;flex-direction:row;gap:0.75rem;align-items:center;">
              <button type="submit" class="btn-submit" style="padding:0 1.5rem;height:40px;border-radius:8px;">Cari</button>
              <?php if ($search !== '' || $status_f !== ''): ?>
                <a href="event_saya_panitia.php" style="color:var(--color-danger);font-size:0.85rem;font-weight:700;text-decoration:none;">Reset</a>
              <?php endif; ?>
            </div>
          </div>
        </form>

        <!-- KARTU EVENT -->
        <div class="event-grid">
          <?php
          $has_events = false;
          while ($event = mysqli_fetch_assoc($events_res)):
              $has_events  = true;
              $tgl_mulai   = !empty($event['tgl_mulai'])    ? date('d M Y', strtotime($event['tgl_mulai']))    : '-';
              $tgl_selesai = !empty($event['tgl_selesai'])  ? date('d M Y', strtotime($event['tgl_selesai']))  : '-';
              $tgl_daftar  = !empty($event['waktu_daftar']) ? date('d M Y', strtotime($event['waktu_daftar'])) : '-';
              $reg_status  = $event['reg_status'] ?? 'terdaftar';
              $harga_label = (!empty($event['harga']) && $event['harga'] > 0)
                             ? 'Rp ' . number_format($event['harga'], 0, ',', '.')
                             : 'Gratis';
          ?>
            <div class="event-card">
              <div class="event-img-wrapper" style="display:flex;align-items:center;justify-content:center;background:#1a1a1e;height:160px;position:relative;border-bottom:1px solid #2d2d34;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--accent-blue);opacity:0.6;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
              </div>
              <div class="event-card-content">
                <h3 class="event-card-title"><?php echo htmlspecialchars($event['name']); ?></h3>
                <p class="event-card-desc"><?php echo htmlspecialchars($event['deskripsi'] ?? '-'); ?></p>

                <div class="event-card-meta">
                  <div class="event-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span><?php echo $tgl_mulai; ?> – <?php echo $tgl_selesai; ?></span>
                  </div>
                  <div class="event-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span><?php echo htmlspecialchars($event['lokasi'] ?? '-'); ?></span>
                  </div>
                  <div class="event-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    <span>Daftar: <strong><?php echo $tgl_daftar; ?></strong></span>
                  </div>
                  <div class="event-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    <span style="color:var(--accent-blue);font-weight:700;"><?php echo $harga_label; ?></span>
                  </div>
                </div>

                <div class="event-card-footer" style="justify-content:space-between;align-items:center;display:flex;">
                  <a href="event_detail.php?id=<?php echo $event['id']; ?>" class="event-card-btn" style="text-decoration:none;">
                    Lihat Detail &rarr;
                  </a>
                  <span class="badge-presence <?php echo htmlspecialchars($reg_status); ?>">
                    <?php echo match($reg_status) {
                        'hadir'       => 'Hadir',
                        'tidak_hadir' => 'Tidak Hadir',
                        default       => 'Terdaftar',
                    }; ?>
                  </span>
                </div>
              </div>
            </div>
          <?php
          endwhile;
          if (!$has_events):
          ?>
            <div class="empty-state" style="grid-column:1/-1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:4rem 2rem;background:var(--bg-card);border:1px solid #2d2d34;border-radius:12px;text-align:center;gap:1rem;">
              <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-muted);"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
              <h3 style="font-size:1.25rem;font-weight:700;color:var(--text-main);">Tidak ada event ditemukan</h3>
              <p style="color:var(--text-muted);font-size:0.9rem;max-width:400px;margin-bottom:0.5rem;">Anda belum mendaftar di event apapun atau tidak ada kecocokan filter.</p>
              <a href="event.php" class="btn-submit" style="text-decoration:none;">Cari &amp; Daftar Event</a>
            </div>
          <?php endif; ?>
        </div>

      </section>
    </main>
  </div>

  <div id="toast" class="toast"><span id="toast-message"></span></div>
  <script>
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    function showToast(message, type = 'info') {
      toastMessage.textContent = message;
      toast.className = 'toast show';
      if (type === 'success') toast.classList.add('toast-success');
      setTimeout(() => toast.classList.remove('show'), 3000);
    }
  </script>
</body>
</html>