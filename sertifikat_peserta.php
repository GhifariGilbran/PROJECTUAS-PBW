
<?php
session_start();
include 'koneksi.php';

// Redirect ke login jika belum login
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$role     = $_SESSION['user_role'] ?? '';
$username = $_SESSION['username'] ?? '';
$user_id  = $_SESSION['user_id']  ?? 0;

// Hanya peserta yang boleh akses
if ($role !== 'peserta') {
    header("Location: dashboard.php");
    exit();
}

// ── AMBIL SEMUA SERTIFIKAT MILIK PESERTA ─────────────────────────────────────
// Join: sertifikat → registration → events
// Sertifikat terhubung ke registration lewat registration_id
$sertif_stmt = mysqli_prepare($koneksi, "
    SELECT
        c.id             AS sertif_id,
        c.kode_sertifikat,
        c.file_path,
        c.issued_at,
        e.id             AS event_id,
        e.name           AS event_name,
        e.tgl_mulai,
        e.tgl_selesai,
        e.lokasi,
        r.status         AS reg_status,
        r.kode_unik
    FROM certificate c
    JOIN registration r ON c.registration_id = r.id
    JOIN events e       ON r.event_id = e.id
    WHERE r.peserta_id = ?
    ORDER BY c.issued_at DESC
");
mysqli_stmt_bind_param($sertif_stmt, "i", $user_id);
mysqli_stmt_execute($sertif_stmt);
$sertif_res = mysqli_stmt_get_result($sertif_stmt);

$sertifikats = [];
while ($row = mysqli_fetch_assoc($sertif_res)) {
    $sertifikats[] = $row;
}
mysqli_stmt_close($sertif_stmt);

$total_sertif = count($sertifikats);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sertifikat Saya - UniVent</title>
  <link rel="stylesheet" href="style.css?v=1.1">
  <link rel="stylesheet" href="sertifikat_peserta.css">
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
            <a href="my_events_peserta.php" class="menu-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
              Event Saya
            </a>
          </li>
          <li>
            <a href="sertifikat_peserta.php" class="menu-link active">
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

        <div class="view-header">
          <h2 class="view-title">Sertifikat Saya</h2>
        </div>

        <!-- STAT -->
        <div class="stats-grid" style="margin-bottom:2rem;">
          <div class="stat-card">
            <span class="stat-label">Total Sertifikat</span>
            <span class="stat-value" style="color:#f6c96f;"><?php echo $total_sertif; ?></span>
            <span class="stat-sublabel">Sertifikat diterima</span>
          </div>
        </div>

        <!-- DAFTAR SERTIFIKAT -->
        <?php if ($total_sertif > 0): ?>

          <div class="section-header-peserta">
            Daftar Sertifikat
          </div>
          <div class="table-container" style="border-radius:0 0 12px 12px; margin-bottom:3rem;">
            <table>
              <thead>
                <tr>
                  <th>Nama Event</th>
                  <th>Lokasi</th>
                  <th>Tanggal Event</th>
                  <th>Kode Sertifikat</th>
                  <th>Tanggal Terbit</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($sertifikats as $s):
                  $tgl_event  = !empty($s['tgl_mulai']) ? date('d M Y', strtotime($s['tgl_mulai'])) : '-';
                  $tgl_terbit = !empty($s['issued_at']) ? date('d M Y', strtotime($s['issued_at'])) : '-';
                ?>
                  <tr>
                    <td class="event-name-cell"><?php echo htmlspecialchars($s['event_name']); ?></td>
                    <td><?php echo htmlspecialchars($s['lokasi'] ?? '-'); ?></td>
                    <td class="date-cell"><?php echo $tgl_event; ?></td>
                    <td><span class="kode-unik"><?php echo htmlspecialchars($s['kode_sertifikat']); ?></span></td>
                    <td class="date-cell"><?php echo $tgl_terbit; ?></td>
                    <td class="actions-cell">
                      <a href="<?php echo htmlspecialchars($s['file_path']); ?>"
                         target="_blank"
                         class="btn-sm btn-approve"
                         style="text-decoration:none;display:inline-flex;align-items:center;justify-content:center;height:32px;padding:0 1rem;">
                        Download
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

        <?php else: ?>

          <!-- EMPTY STATE -->
          <div class="sertif-empty-wrapper">
            <div class="sertif-empty-box">
              <div class="sertif-empty-icon">🎓</div>
              <h3>Belum Ada Sertifikat</h3>
              <p>Sertifikat akan muncul di sini setelah panitia mengunggahnya untuk event yang Anda hadiri.</p>
              <a href="event.php" class="btn-submit" style="text-decoration:none;display:inline-block;margin-top:1rem;">
                Cari Event Sekarang
              </a>
            </div>
          </div>

        <?php endif; ?>

      </section>
    </main>
  </div>

</body>
</html>