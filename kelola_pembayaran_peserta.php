<?php
session_start();
include 'koneksi.php';

// Redirect to login if session doesn't exist
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'panitia')) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['user_role'] ?? '';
$username = $_SESSION['username'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

// Handle Action (Acc / Tolak)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $payment_id = (int)$_GET['id'];

    if ($action === 'acc' || $action === 'tolak') {
        // Ambil data payment + registration + event untuk validasi & toast
        $check_query = "SELECT p.id, p.registration_id, r.event_id, e.name AS event_name, e.panitia_id, u.username AS peserta_name
                         FROM payment p
                         JOIN registration r ON p.registration_id = r.id
                         JOIN events e ON r.event_id = e.id
                         JOIN users u ON r.peserta_id = u.id
                         WHERE p.id = ?";
        $check_stmt = mysqli_prepare($koneksi, $check_query);
        mysqli_stmt_bind_param($check_stmt, "i", $payment_id);
        mysqli_stmt_execute($check_stmt);
        $check_res = mysqli_stmt_get_result($check_stmt);
        $payment_data = mysqli_fetch_assoc($check_res);
        mysqli_stmt_close($check_stmt);

        if ($payment_data) {
            // Validasi: panitia hanya boleh acc payment dari event miliknya sendiri
            $authorized = ($role === 'admin') || ($role === 'panitia' && $payment_data['panitia_id'] == $user_id);

            if ($authorized) {
                if ($action === 'acc') {
                    // Update payment jadi lunas
                    $update_payment = mysqli_prepare($koneksi, "UPDATE payment SET status_pembayaran = 'lunas' WHERE id = ?");
                    mysqli_stmt_bind_param($update_payment, "i", $payment_id);
                    mysqli_stmt_execute($update_payment);
                    mysqli_stmt_close($update_payment);

                    // Update registration jadi terdaftar
                    $update_reg = mysqli_prepare($koneksi, "UPDATE registration SET status = 'terdaftar' WHERE id = ?");
                    mysqli_stmt_bind_param($update_reg, "i", $payment_data['registration_id']);
                    mysqli_stmt_execute($update_reg);
                    mysqli_stmt_close($update_reg);

                    $_SESSION['toast_msg'] = "Pembayaran " . htmlspecialchars($payment_data['peserta_name']) . " untuk event '" . htmlspecialchars($payment_data['event_name']) . "' berhasil disetujui!";
                    $_SESSION['toast_type'] = 'success';

                } elseif ($action === 'tolak') {
                    // Update payment jadi ditolak
                    $update_payment = mysqli_prepare($koneksi, "UPDATE payment SET status_pembayaran = 'ditolak' WHERE id = ?");
                    mysqli_stmt_bind_param($update_payment, "i", $payment_id);
                    mysqli_stmt_execute($update_payment);
                    mysqli_stmt_close($update_payment);

                    // Registration status tetap 'pending_payment', tidak diubah

                    $_SESSION['toast_msg'] = "Pembayaran " . htmlspecialchars($payment_data['peserta_name']) . " untuk event '" . htmlspecialchars($payment_data['event_name']) . "' ditolak.";
                    $_SESSION['toast_type'] = 'info';
                }
            } else {
                $_SESSION['toast_msg'] = "Anda tidak memiliki akses untuk memverifikasi pembayaran ini.";
                $_SESSION['toast_type'] = 'info';
            }
        }
    }

    header("Location: kelola_pembayaran.php");
    exit();
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

// Fetch pending payments
if ($role === 'admin') {
    $query = "SELECT p.*, r.peserta_id, r.event_id, r.waktu_daftar,
                     e.name AS event_name, e.panitia_id,
                     u.username AS peserta_name
              FROM payment p
              JOIN registration r ON p.registration_id = r.id
              JOIN events e ON r.event_id = e.id
              JOIN users u ON r.peserta_id = u.id
              WHERE p.status_pembayaran = 'pending'
              ORDER BY p.created_at DESC";
    $payments_res = mysqli_query($koneksi, $query);
} else {
    // panitia: hanya event miliknya
    $query = "SELECT p.*, r.peserta_id, r.event_id, r.waktu_daftar,
                     e.name AS event_name, e.panitia_id,
                     u.username AS peserta_name
              FROM payment p
              JOIN registration r ON p.registration_id = r.id
              JOIN events e ON r.event_id = e.id
              JOIN users u ON r.peserta_id = u.id
              WHERE p.status_pembayaran = 'pending' AND e.panitia_id = ?
              ORDER BY p.created_at DESC";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $payments_res = mysqli_stmt_get_result($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pembayaran - UniVent</title>
  <link rel="stylesheet" href="style.css?v=1.1">
  <style>
    .bukti-link {
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      color: var(--accent-blue);
      text-decoration: none;
      font-weight: 600;
      font-size: 0.85rem;
    }
    .bukti-link:hover {
      text-decoration: underline;
    }
    .no-bukti {
      color: var(--text-muted);
      font-size: 0.85rem;
      font-style: italic;
    }
    .nominal-cell {
      font-weight: 700;
      color: #5ce1e6;
    }
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
        <span class="user-info-text"><?php echo htmlspecialchars($username ?? ''); ?></span>
        <span class="user-info-role"><?php echo htmlspecialchars($role); ?></span>
      </div>

      <a href="logout.php" class="logout-btn-header">Keluar</a>
    </div>
  </header>

  <div class="app-container">

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
              <a href="kelola_pembayaran.php" class="menu-link active">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                Kelola Pembayaran
              </a>
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
              <a href="kategori.php" class="menu-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                Kategori
              </a>
            </li>
          </ul>

          <span class="menu-title" style="margin-top: 1rem;">Laporan</span>
          <ul class="menu-items">
            <li>
              <a href="statistik.php" class="menu-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                Statistik
              </a>
            </li>
          </ul>
        </div>

      <?php elseif ($role === 'panitia'): ?>
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
              <a href="kelola_pembayaran.php" class="menu-link active">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                Kelola Pembayaran
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
          </ul>
        </div>
      <?php endif; ?>

    </aside>

    <main>
      <section class="view-section active">
        <div class="view-header">
          <h2 class="view-title">Kelola Pembayaran</h2>
        </div>

        <div class="section-banner">Menunggu Verifikasi Pembayaran</div>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>Peserta</th>
                <th>Event</th>
                <th>Nominal</th>
                <th>Bukti Pembayaran</th>
                <th>Tanggal Bayar</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $has_payment = false;
              while ($pay = mysqli_fetch_assoc($payments_res)):
                  $has_payment = true;
              ?>
                <tr>
                  <td><?php echo htmlspecialchars($pay['peserta_name']); ?></td>
                  <td class="event-name-cell"><?php echo htmlspecialchars($pay['event_name']); ?></td>
                  <td class="nominal-cell">Rp <?php echo number_format($pay['nominal'], 0, ',', '.'); ?></td>
                  <td>
                    <?php if (!empty($pay['bukti_pembayaran'])): ?>
                      <a href="uploads/<?php echo htmlspecialchars($pay['bukti_pembayaran']); ?>" target="_blank" class="bukti-link">Lihat Bukti &rarr;</a>
                    <?php else: ?>
                      <span class="no-bukti">Tidak ada bukti</span>
                    <?php endif; ?>
                  </td>
                  <td><?php echo date('d M Y, H:i', strtotime($pay['created_at'])); ?></td>
                  <td class="actions-cell">
                    <a href="kelola_pembayaran.php?action=tolak&id=<?php echo $pay['id']; ?>" class="btn-sm btn-reject" style="display:inline-flex; align-items:center; text-decoration:none;" onclick="return confirm('Tolak pembayaran ini?')">Tolak</a>
                    <a href="kelola_pembayaran.php?action=acc&id=<?php echo $pay['id']; ?>" class="btn-sm btn-approve" style="display:inline-flex; align-items:center; text-decoration:none;" onclick="return confirm('Setujui pembayaran ini?')">Acc</a>
                  </td>
                </tr>
              <?php
              endwhile;

              if (!$has_payment):
              ?>
                <tr>
                  <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">Tidak ada pembayaran yang menunggu verifikasi.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>

  </div>

  <div id="toast" class="toast <?php echo !empty($toast_msg) ? 'show' : ''; ?> <?php echo ($toast_type === 'success') ? 'toast-success' : ''; ?>">
    <span id="toast-message"><?php echo htmlspecialchars($toast_msg); ?></span>
  </div>

  <script>
    const toast = document.getElementById('toast');
    if (toast.classList.contains('show')) {
      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }
  </script>
</body>
</html>