<?php
session_start();
include 'koneksi.php';

// Check authentication
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['user_role'];
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Redirect Admin away if they shouldn't view participant event finder
if ($role !== 'peserta') {
    header("Location: dashboard.php");
    exit();
}

// Fetch user's registered event IDs to mark them
$reg_ids = [];
$reg_ids_stmt = mysqli_prepare($koneksi, "SELECT event_id FROM registrations WHERE user_id = ?");
mysqli_stmt_bind_param($reg_ids_stmt, "i", $user_id);
mysqli_stmt_execute($reg_ids_stmt);
$reg_res = mysqli_stmt_get_result($reg_ids_stmt);
while ($reg_row = mysqli_fetch_row($reg_res)) {
    $reg_ids[] = $reg_row[0];
}
mysqli_stmt_close($reg_ids_stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Event - UniVent</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Top Navigation Header -->
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
    
    <!-- Sidebar -->
    <aside id="sidebar">
      <div class="menu-group">
        <span class="menu-title">Menu Utama</span>
        <ul class="menu-items">
          <li>
            <a href="dashboard.php" class="menu-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
              Dashboard
            </a>
          </li>
          <li>
            <a href="event.php" class="menu-link active">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
              Cari Event
            </a>
          </li>
          <li>
            <a href="#" class="menu-link" onclick="showFeatureAlert('Event Saya')">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
              Event Saya
            </a>
          </li>
          <li>
            <a href="#" class="menu-link" onclick="showFeatureAlert('Sertifikat')">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
              Sertifikat
            </a>
          </li>
        </ul>
      </div>

      <!-- SHARED SYSTEM MENU -->
      <div class="menu-group" style="margin-top: auto;">
        <span class="menu-title">Sistem</span>
        <ul class="menu-items">
          <li>
            <a href="#" class="menu-link" onclick="showFeatureAlert('Pengaturan')">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
              Pengaturan
            </a>
          </li>
        </ul>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main>
      <section class="view-section active">
        <div class="view-header">
          <h2 class="view-title">Eksplorasi Event</h2>
          <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">Temukan dan ikuti berbagai event universitas menarik di bawah ini.</p>
        </div>

        <div class="event-grid">
          <?php 
          // Fetch approved events from database
          $events_res = mysqli_query($koneksi, "SELECT * FROM events WHERE status = 'Approved'");
          $has_events = false;
          while ($event = mysqli_fetch_assoc($events_res)):
              $has_events = true;
              $is_registered = in_array($event['id'], $reg_ids);
          ?>
            <div class="event-card">
              <img class="event-img" src="<?php echo htmlspecialchars($event['poster']); ?>" alt="Poster <?php echo htmlspecialchars($event['name']); ?>">
              <div class="event-card-content">
                <span class="event-card-tag"><?php echo htmlspecialchars($event['category']); ?></span>
                <h3 class="event-card-title"><?php echo htmlspecialchars($event['name']); ?></h3>
                <p class="event-card-desc"><?php echo htmlspecialchars($event['desc']); ?></p>
                
                <div class="event-card-meta">
                  <div class="event-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span><?php echo htmlspecialchars($event['date']); ?></span>
                  </div>
                  <div class="event-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                  </div>
                  <div class="event-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    <span>Kuota: <strong><?php echo htmlspecialchars($event['quota']); ?></strong></span>
                  </div>
                  <div class="event-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    <span style="color: var(--accent-blue); font-weight: 700;"><?php echo htmlspecialchars($event['price']); ?></span>
                  </div>
                </div>

                <div style="display: flex; gap: 0.5rem; align-items: center; width: 100%;">
                  <a href="event_detail.php?id=<?php echo $event['id']; ?>" class="event-card-btn" style="flex: 1;">
                    Lihat Detail &rarr;
                  </a>
                  <?php if ($is_registered): ?>
                    <span style="font-size: 0.75rem; color: var(--color-success); font-weight: 700; background: rgba(46, 213, 115, 0.1); padding: 0.5rem 0.8rem; border-radius: 8px; border: 1px solid rgba(46, 213, 115, 0.2);">Terdaftar</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php 
          endwhile;

          if (!$has_events):
          ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 1rem; color: var(--text-muted);">
              <h3>Belum ada event yang disetujui oleh admin.</h3>
              <p style="margin-top: 0.5rem;">Coba login sebagai Admin dan setujui beberapa event terlebih dahulu.</p>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </main>

  </div>

  <!-- Custom Alert Toast notification -->
  <div id="toast" class="toast">
    <span id="toast-message"></span>
  </div>

  <script>
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');

    function showToast(message, type = 'info') {
      toastMessage.textContent = message;
      toast.className = 'toast show';
      if (type === 'success') {
        toast.classList.add('toast-success');
      }
      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }

    function showFeatureAlert(featureName) {
      showToast(`Fitur "${featureName}" adalah mockup untuk purwarupa ini.`, 'info');
    }
  </script>
</body>
</html>
