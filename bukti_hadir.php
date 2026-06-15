<?php
session_start();
include 'koneksi.php';

// Check autentikasi login
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['user_role'];
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// memastikan id nya, kalau gaada id nya di balikin ke
if (!isset($_GET['id'])) {
    header("Location: event.php");
    exit();
}

$event_id = (int)$_GET['id'];

// cari event di database
$stmt = mysqli_prepare($koneksi, "SELECT * FROM events WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $event_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$event = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

// kalo event nya gaada balikin ke event.php
if (!$event) {
    header("Location: event.php");
    exit();
}

// Check apakah sudah ada di database
$reg_stmt = mysqli_prepare($koneksi, "SELECT * FROM registration WHERE peserta_id = ? AND event_id = ?");
mysqli_stmt_bind_param($reg_stmt, "ii", $user_id, $event_id);
mysqli_stmt_execute($reg_stmt);
$reg_res = mysqli_stmt_get_result($reg_stmt);
$is_registered = (mysqli_num_rows($reg_res) > 0);
mysqli_stmt_close($reg_stmt);

// Handle Event Registration Post
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_event'])) {
    if (!$is_registered && $event['quota'] > 0) {
        mysqli_begin_transaction($koneksi);
        
        try {
            // Generate kode unik
            $kode_unik = strtoupper(uniqid('EVT-'));

            // Insert registration record
            $ins_stmt = mysqli_prepare($koneksi, "INSERT INTO registration (peserta_id, event_id, kode_unik) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($ins_stmt, "iis", $user_id, $event_id, $kode_unik);
            mysqli_stmt_execute($ins_stmt);
            mysqli_stmt_close($ins_stmt);
            
            // Decrement quota
            $upd_stmt = mysqli_prepare($koneksi, "UPDATE events SET quota = quota - 1 WHERE id = ? AND quota > 0");
            mysqli_stmt_bind_param($upd_stmt, "i", $event_id);
            mysqli_stmt_execute($upd_stmt);
            mysqli_stmt_close($upd_stmt);
            
            mysqli_commit($koneksi);
            
            $_SESSION['toast_msg'] = "Pendaftaran Berhasil! Anda terdaftar pada event '" . $event['name'] . "'";
            $_SESSION['toast_type'] = 'success';
        } catch (Exception $e) {
            mysqli_rollback($koneksi);
            $_SESSION['toast_msg'] = "Pendaftaran gagal! Silakan coba lagi.";
            $_SESSION['toast_type'] = 'info';
        }
        
        header("Location: event_detail.php?id=" . $event_id);
        exit();
    }
}

// Notifikasi
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
      <?php if ($role === 'admin'): ?>
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
              <a href="kelola_event.php" class="menu-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Kelola Event
              </a>
            </li>
            <li>
              <p class="menu-link" style="color:aqua; margin-left: 20%;">
                | Detail Event
              </p>
            </li>
            <li>
              <a href="kelola_pengguna.php" class="menu-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Kelola Pengguna
              </a>
            </li>
            <li>
              <a href="peserta.php" class="menu-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                Peserta
              </a>
            </li>
            <li>
              <a href="#" class="menu-link" onclick="showFeatureAlert('Kategori')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                Kategori
              </a>
            </li>
          </ul>
          <span class="menu-title" style="margin-top: 1rem;">Laporan</span>
          <ul class="menu-items">
            <li>
              <a href="#" class="menu-link" onclick="showFeatureAlert('Statistik')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                Statistik
              </a>
            </li>
          </ul>
        </div>

      <?php else: ?>
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
      <?php endif; ?>
    </aside>

    <!-- Area Main konten -->
    <main>

        
        <form action="proses_hadir.php" method="post" enctype="multipart/form-data">
            
           <input type="hidden" name="event_id" value="<?= $event_id ?>">

            <label for="bukti_hadir">Upload Bukti Kehadiran (Foto/Scan Kartu Peserta atau QR Code):</label>
            <br>
            <br>

            <input type="file" name="bukti_hadir" required class="input-text" required>

            <br>
            <br>

            <button type="submit" name="submit_hadir" class="btn-submit">Submit Bukti Kehadiran</button>
        </form>
   

    </main>

  </div>

  <!-- Notification -->
  <div id="toast" class="toast <?php echo !empty($toast_msg) ? 'show' : ''; ?> <?php echo ($toast_type === 'success') ? 'toast-success' : ''; ?>">
    <span id="toast-message"><?php echo htmlspecialchars($toast_msg); ?></span>
  </div>

  <script>
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');

    if (toast.classList.contains('show')) {
      setTimeout(() => toast.classList.remove('show'), 3000);
    }

    function showToast(message, type = 'info') {
      toastMessage.textContent = message;
      toast.className = 'toast show';
      if (type === 'success') toast.classList.add('toast-success');
      setTimeout(() => toast.classList.remove('show'), 3000);
    }

    function showFeatureAlert(featureName) {
      showToast(`Fitur "${featureName}" adalah mockup untuk purwarupa ini.`, 'info');
    }
  </script>
</body>
</html>