
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

// Ambil event yang belum lunas (pending_payment atau payment ditolak)
$pay_stmt = mysqli_prepare($koneksi, "
    SELECT 
        r.id AS registration_id,
        r.status AS reg_status,
        r.waktu_daftar,
        e.name AS event_name,
        e.tgl_mulai,
        e.lokasi,
        e.poster,
        p.nominal,
        p.status_pembayaran,
        p.bukti_pembayaran
    FROM registration r
    JOIN events e ON r.event_id = e.id
    JOIN payment p ON p.registration_id = r.id
    WHERE r.peserta_id = ?
      AND r.status = 'pending_payment'
    ORDER BY r.waktu_daftar DESC
");
mysqli_stmt_bind_param($pay_stmt, "i", $user_id);
mysqli_stmt_execute($pay_stmt);
$pay_result = mysqli_stmt_get_result($pay_stmt);
$payments = mysqli_fetch_all($pay_result, MYSQLI_ASSOC);
mysqli_stmt_close($pay_stmt);

$toast_msg = "";
$toast_type = "";
if (isset($_SESSION['toast_msg'])) {
    $toast_msg = $_SESSION['toast_msg'];
    $toast_type = $_SESSION['toast_type'] ?? 'info';
    unset($_SESSION['toast_msg']);
    unset($_SESSION['toast_type']);
}

?>
<!DOCTYPE html>
<html lang="id">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Sertifikat Saya - UniVent</title>
      <link rel="stylesheet" href="style.css?v=1.1">
      <link rel="stylesheet" href="sertifikat_peserta.css">
      <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

  </head>
  <body>

    <header>
      <div class="logo-container">
        <h1 class="logo-title">Uni<span>Vent</span></h1>
        <span class="logo-subtitle">University Event</span>
      </div>
      <div class="header-right">
        <div class="user-profile-meta">
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
              <a href="sertifikat_peserta.php" class="menu-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                Sertifikat
              </a>
            </li>
          </ul>

          <span class="menu-title" style="margin-top:1rem;">Manajemen Pembayaran</span>
          <ul class="menu-items">
            <li>
              <a href="pembayaran_peserta.php" class="menu-link active">
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
            <div class="view-header" style="flex-direction: column; align-items: flex-start; gap: 0.25rem; margin-bottom: 1.5rem;">
              <h2 class="view-title">Pembayaran</h2>
              <p style="color: var(--text-muted); font-size: 0.9rem;">Upload bukti pembayaran anda untuk mengikuti event di bawah ini</p>
            </div>


            <?php if (empty($payments)): ?>
          <div style="text-align:center; padding: 3rem 1rem; color: var(--text-muted);">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom:1rem; opacity:0.4;"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="6" y1="15" x2="10" y2="15"/></svg>
              <p style="font-size:0.95rem;">Tidak ada tagihan pembayaran</p>
          </div>

          <?php else: ?>
          <div style="display: flex; flex-direction: column; gap: 1rem;">
              <?php foreach ($payments as $item): ?>
              <div style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1.25rem;">
                  
                  <!-- Poster -->
                  <div style="width:56px; height:56px; border-radius:8px; overflow:hidden; flex-shrink:0; background:var(--bg-secondary); display:flex; align-items:center; justify-content:center;">
                  <?php if (!empty($item['poster'])): ?>
                      <img src="uploads/<?php echo htmlspecialchars($item['poster']); ?>" style="width:100%; height:100%; object-fit:cover;">
                  <?php else: ?>
                      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.4;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  <?php endif; ?>
                  </div>

                  <!-- Info Event -->
                  <div style="flex:1; min-width:0;">
                  <div style="font-weight:500; font-size:0.95rem; margin-bottom:0.25rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                      <?php echo htmlspecialchars($item['event_name']); ?>
                  </div>
                  <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:0.4rem;">
                      <?php echo date('d M Y', strtotime($item['tgl_mulai'])); ?>
                      <?php if (!empty($item['lokasi'])): ?>
                      &bull; <?php echo htmlspecialchars($item['lokasi']); ?>
                      <?php endif; ?>
                  </div>
                  <div style="font-size:0.85rem; font-weight:500;">
                      Rp <?php echo number_format($item['nominal'], 0, ',', '.'); ?>
                  </div>
                  </div>

                  <!-- Status + Tombol -->
                  <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.5rem; flex-shrink:0;">
                  <?php if ($item['status_pembayaran'] === 'ditolak'): ?>
                      <span style="font-size:0.75rem; background:#ff4d4d22; color:#ff4d4d; padding:0.2rem 0.6rem; border-radius:20px;">Ditolak</span>
                  <?php else: ?>
                      <span style="font-size:0.75rem; background:#f5a62322; color:#f5a623; padding:0.2rem 0.6rem; border-radius:20px;">Menunggu Pembayaran</span>
                  <?php endif; ?>

                  <?php if (empty($item['bukti_pembayaran']) || $item['status_pembayaran'] === 'ditolak'): ?>
                      <a href="upload_bukti.php?registration_id=<?php echo $item['registration_id']; ?>" 
                      style="font-size:0.8rem; background:var(--accent-blue); color:#fff; padding:0.4rem 0.9rem; border-radius:8px; text-decoration:none; color: black;">
                      <?php echo $item['status_pembayaran'] === 'ditolak' ? 'Upload Ulang' : 'Upload Bukti'; ?>
                      </a>
                  <?php else: ?>
                      <span style="font-size:0.75rem; color:var(--text-muted);">Menunggu konfirmasi</span>
                  <?php endif; ?>
                  </div>

              </div>
              <?php endforeach; ?>
          </div>
          <?php endif; ?>
      
      </main>
    </div>

    <div id="toast" class="toast <?php echo !empty($toast_msg) ? 'show' : ''; ?> <?php echo ($toast_type === 'success') ? 'toast-success' : ''; ?>">
        <span id="toast-message"><?php echo htmlspecialchars($toast_msg); ?></span>
    </div>

    <script>
        const toast = document.getElementById('toast');
        if (toast.classList.contains('show')) {
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
    </script>

  </body>
</html>