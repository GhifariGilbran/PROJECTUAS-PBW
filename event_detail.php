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

// Ensure id parameter is set
if (!isset($_GET['id'])) {
    header("Location: event.php");
    exit();
}

$event_id = (int)$_GET['id'];

// Find the event in the database
$stmt = mysqli_prepare($koneksi, "SELECT * FROM events WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

// Redirect if event not found
if (!$event) {
    header("Location: event.php");
    exit();
}

// Check if already registered in database
$reg_stmt = mysqli_prepare($koneksi, "SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
mysqli_stmt_bind_param($reg_stmt, "ii", $user_id, $event_id);
mysqli_stmt_execute($reg_stmt);
$reg_res = mysqli_stmt_get_result($reg_stmt);
$is_registered = (mysqli_num_rows($reg_res) > 0);
mysqli_stmt_close($reg_stmt);

// Handle Event Registration Post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_event'])) {
    if (!$is_registered && $event['quota'] > 0) {
        // Begin Transaction for reliability
        mysqli_begin_transaction($koneksi);
        
        try {
            // 1. Insert registration record
            $ins_stmt = mysqli_prepare($koneksi, "INSERT INTO registrations (user_id, event_id) VALUES (?, ?)");
            mysqli_stmt_bind_param($ins_stmt, "ii", $user_id, $event_id);
            mysqli_stmt_execute($ins_stmt);
            mysqli_stmt_close($ins_stmt);
            
            // 2. Decrement quota in events table
            $upd_stmt = mysqli_prepare($koneksi, "UPDATE events SET quota = quota - 1 WHERE id = ? AND quota > 0");
            mysqli_stmt_bind_param($upd_stmt, "i", $event_id);
            mysqli_stmt_execute($upd_stmt);
            mysqli_stmt_close($upd_stmt);
            
            // Commit transaction
            mysqli_commit($koneksi);
            
            $_SESSION['toast_msg'] = "Pendaftaran Berhasil! Anda terdaftar pada event '" . $event['name'] . "'";
            $_SESSION['toast_type'] = 'success';
        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($koneksi);
            $_SESSION['toast_msg'] = "Pendaftaran gagal! Silakan coba lagi.";
            $_SESSION['toast_type'] = 'info';
        }
        
        header("Location: event_detail.php?id=" . $event_id);
        exit();
    }
}

// Fetch Toast Notification
$toast_msg = "";
$toast_type = "";
if (isset($_SESSION['toast_msg'])) {
    $toast_msg = $_SESSION['toast_msg'];
    $toast_type = isset($_SESSION['toast_type']) ? $_SESSION['toast_type'] : 'info';
    unset($_SESSION['toast_msg']);
    unset($_SESSION['toast_type']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Event - UniVent</title>
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
      <a href="event.php" class="btn-back-link">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Kembali ke Daftar Event
      </a>

      <div class="detail-layout">
        
        <!-- Sidebar Detail (Image & Info Box) -->
        <div class="detail-sidebar">
          <div class="detail-poster">
            <img src="<?php echo htmlspecialchars($event['poster']); ?>" alt="Poster <?php echo htmlspecialchars($event['name']); ?>">
          </div>

          <div class="detail-meta-box">
            <div class="detail-meta-title">Detail Informasi</div>
            <div class="detail-meta-list">
              <div class="detail-meta-row">
                <span class="detail-meta-label">Tanggal</span>
                <span class="detail-meta-val"><?php echo htmlspecialchars($event['date']); ?></span>
              </div>
              <div class="detail-meta-row">
                <span class="detail-meta-label">Lokasi</span>
                <span class="detail-meta-val"><?php echo htmlspecialchars($event['location']); ?></span>
              </div>
              <div class="detail-meta-row">
                <span class="detail-meta-label">Kategori</span>
                <span class="detail-meta-val"><?php echo htmlspecialchars($event['category']); ?></span>
              </div>
              <div class="detail-meta-row">
                <span class="detail-meta-label">Kuota Sisa</span>
                <span class="detail-meta-val"><?php echo htmlspecialchars($event['quota']); ?> kursi</span>
              </div>
              <div class="detail-meta-row">
                <span class="detail-meta-label">Investasi</span>
                <span class="detail-meta-val price"><?php echo htmlspecialchars($event['price']); ?></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Detail (Description & Register Button) -->
        <div class="detail-main">
          <h2 class="detail-title"><?php echo htmlspecialchars($event['name']); ?></h2>
          <div class="detail-organizer">
            Diselenggarakan oleh: <span><?php echo htmlspecialchars($event['panitia']); ?></span>
          </div>

          <div class="detail-section">
            <h3 class="detail-section-title">Deskripsi Event</h3>
            <p class="detail-desc-text"><?php echo htmlspecialchars($event['desc']); ?></p>
          </div>

          <div class="detail-section">
            <h3 class="detail-section-title">Fasilitas & Benefit</h3>
            <p class="detail-desc-text" style="margin-bottom: 0.5rem;">• E-Sertifikat Resmi Universitas</p>
            <p class="detail-desc-text" style="margin-bottom: 0.5rem;">• Materi Pelatihan & Akses Resource Code</p>
            <p class="detail-desc-text">• Konsumsi & Networking session</p>
          </div>

          <div class="detail-action-bar">
            <div class="detail-price-display">
              <span class="detail-price-label">Biaya Pendaftaran</span>
              <span class="detail-price-val"><?php echo htmlspecialchars($event['price']); ?></span>
            </div>

            <?php if ($is_registered): ?>
              <div class="badge-status-reg">Anda Sudah Terdaftar</div>
            <?php elseif ($event['quota'] <= 0): ?>
              <div class="badge-status-closed">Kuota Penuh</div>
            <?php else: ?>
              <form action="" method="POST" style="margin: 0;">
                <input type="hidden" name="register_event" value="1">
                <button type="submit" class="btn-register-event">Daftar Sekarang</button>
              </form>
            <?php endif; ?>
          </div>

        </div>

      </div>
    </main>

  </div>

  <!-- Custom Alert Toast notification -->
  <div id="toast" class="toast <?php echo !empty($toast_msg) ? 'show' : ''; ?> <?php echo ($toast_type === 'success') ? 'toast-success' : ''; ?>">
    <span id="toast-message"><?php echo htmlspecialchars($toast_msg); ?></span>
  </div>

  <script>
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');

    if (toast.classList.contains('show')) {
      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }

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
