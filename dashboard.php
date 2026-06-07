<?php
session_start();
include 'koneksi.php';

// Redirect to login if session doesn't exist
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['user_role'] ?? '';
$username = $_SESSION['username'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

// Handle Admin Action (Approve / Reject)
if ($role === 'admin' && isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    $status = ($action === 'approve') ? 'approve' : (($action === 'reject') ? 'reject' : null);    
    
    if ($status) {
        // Fetch event name first for toast message
        $name_stmt = mysqli_prepare($koneksi, "SELECT name FROM events WHERE id = ?");
        mysqli_stmt_bind_param($name_stmt, "i", $id);
        mysqli_stmt_execute($name_stmt);
        $name_res = mysqli_stmt_get_result($name_stmt);
        $event_name = "";
        if ($name_row = mysqli_fetch_assoc($name_res)) {
            $event_name = $name_row['name'];
        }
        mysqli_stmt_close($name_stmt);

        // Update status in DB
        $stmt = mysqli_prepare($koneksi, "UPDATE events SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $status, $id);
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['toast_msg'] = "Event '" . $event_name . "' " . ($status === 'approve' ? 'berhasil disetujui!' : 'telah ditolak.');
            $_SESSION['toast_type'] = ($status === 'approve') ? 'success' : 'info';
        }
        mysqli_stmt_close($stmt);
    }
    header("Location: dashboard.php");
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard UniVent</title>
  <link rel="stylesheet" href="style.css?v=1.1">
  <style>
    /* CSS Tambahan Khusus Dashboard Panitia - Sisipan Grid Layout */
    .panitia-grid-layout {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        margin-top: 1.5rem;
    }
    .quick-action-container {
        background-color: var(--bg-card);
        border: 1px solid #2d2d34;
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .quick-action-card {
        background-color: #2b2b30;
        border: 1px solid #3d3d45;
        border-radius: 8px;
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-decoration: none;
        color: #fff;
        transition: background-color 0.2s;
    }
    .quick-action-card:hover {
        background-color: #38383f;
    }
    .quick-action-text h4 {
        margin: 0 0 0.25rem 0;
        font-size: 0.95rem;
        font-weight: 600;
    }
    .quick-action-text p {
        margin: 0;
        font-size: 0.8rem;
        color: var(--text-muted);
    }
    .arrow-icon {
        font-weight: bold;
        font-size: 1.1rem;
    }
    @media (max-width: 992px) {
        .panitia-grid-layout {
            grid-template-columns: 1fr;
        }
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
              <a href="dashboard.php" class="menu-link active">
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
              <a href="dashboard.php" class="menu-link active">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                Dashboard
              </a>
            </li>
            <li>
              <a href="buat_event.php" class="menu-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Buat Event
              </a>
            </li>
            <li>
              <a href="event_saya.php" class="menu-link">
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
          </ul>
        </div>

      <?php else: ?>
        <div class="menu-group">
          <span class="menu-title">Menu Utama</span>
          <ul class="menu-items">
            <li>
              <a href="dashboard.php" class="menu-link active">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                Dashboard
              </a>
            </li>
            <li>
              <a href="event.php" class="menu-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Cari Event
              </a>
            </li>
            <li>
              <a href="my_events.php" class="menu-link">
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
      <?php endif; ?>

    </aside>

    <main>

      <?php if ($role === 'admin'): ?>
        <?php
        // Fetch Admin Statistics from database
        $total_res = mysqli_query($koneksi, "SELECT COUNT(*) as count FROM events");
        $total_events = mysqli_fetch_assoc($total_res)['count'];

        $peserta_res = mysqli_query($koneksi, "SELECT COUNT(*) as count FROM users WHERE role = 'peserta'");
        $total_peserta = mysqli_fetch_assoc($peserta_res)['count'];

        $pending_res = mysqli_query($koneksi, "SELECT COUNT(*) as count FROM events WHERE status = 'Pending'");
        $pending_count = mysqli_fetch_assoc($pending_res)['count'];
        ?>
        <section id="admin-dashboard-view" class="view-section active">
          <div class="view-header">
            <h2 class="view-title">Dashboard Admin</h2>
          </div>

          <div class="stats-grid admin-stats-grid">
            
            <div class="stat-card">
              <span class="stat-label">Total Event</span>
              <span class="stat-value"><?php echo $total_events; ?></span>
              <span class="stat-sublabel">Terdaftar di database</span>
            </div>
            
            <div class="stat-card">
              <span class="stat-label">Total Peserta</span>
              <span class="stat-value"><?php echo $total_peserta; ?></span>
              <span class="stat-sublabel">Peserta terdaftar</span>
            </div>
            
            <div class="stat-card">
              <span class="stat-label">Menunggu Persetujuan</span>
              <span class="stat-value pending"><?php echo $pending_count; ?></span>
              <span class="stat-sublabel pending">Perlu ditinjau</span>
            </div>
            
            <div class="stat-card">
              <span class="stat-label">Distribusi Event</span>
              <ul class="distribution-list">
                <?php
                $dist_res = mysqli_query($koneksi, "SELECT u.prodi, COUNT(e.id) AS jumlah_event
                                                    FROM events e
                                                    JOIN users u ON e.panitia_id = u.id
                                                    GROUP BY u.prodi");
                while ($dist_row = mysqli_fetch_assoc($dist_res)):
                ?>
                <li class="distribution-item">
                  <span><?php echo htmlspecialchars($dist_row['prodi']); ?></span>
                  <span class="count"><?php echo $dist_row['jumlah_event']; ?></span>
                </li>
                <?php endwhile; ?>
              </ul>
            </div>

          </div>

          <div class="section-banner">Event Menunggu Persetujuan</div>
          <div class="table-container">
            <table>
              <thead>
                <tr>
                  <th>Nama Event</th>
                  <th>Kategori</th>
                  <th>Kuota</th>
                  <th>Tanggal Dibuat</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                // 1. QUERY YANG BENAR: Gabungkan events dan users untuk mencari prodi, khusus status pending
                $query_pending = "SELECT events.*, users.prodi 
                                  FROM events 
                                  LEFT JOIN users ON events.panitia_id = users.id 
                                  WHERE events.status = 'pending'";

                $pending_events_res = mysqli_query($koneksi, $query_pending);
                $has_pending = false;

                // 2. MULAI PERULANGAN DATA
                while ($event = mysqli_fetch_assoc($pending_events_res)):
                    $has_pending = true;
                    
                    // Format nama prodi agar rapi langsung di dalam loop PHP
                    $prodi_tampil = !empty($event['prodi']) ? ucwords(str_replace('_', ' ', $event['prodi'])) : 'Umum / Tanpa Prodi';
                    
                    // Format tanggal cadangan jika kolom tgl_mulai kosong
                    $tanggal_tampil = !empty($event['tgl_mulai']) ? $event['tgl_mulai'] : $event['created_at'];
                ?>
                  <tr>
                    <td class="event-name-cell"><?php echo htmlspecialchars($event['name']); ?></td>
                    <td><?php echo htmlspecialchars($event['category']); ?></td>
                    <td><?php echo htmlspecialchars($event['quota']); ?></td>
                    <td><?php echo htmlspecialchars($event['created_at']); ?></td>
                    <td><span class="status-badge pending">Pending</span></td>
                    <td class="actions-cell">
                      <a href="dashboard.php?action=reject&id=<?php echo $event['id']; ?>" class="btn-sm btn-reject" style="display:inline-flex; align-items:center; text-decoration:none;">Tolak</a>
                      <a href="dashboard.php?action=approve&id=<?php echo $event['id']; ?>" class="btn-sm btn-approve" style="display:inline-flex; align-items:center; text-decoration:none;">Setujui</a>
                      
                      <button class="btn-detail" onclick="viewEventDetails(
                          `<?php echo htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8'); ?>`, 
                          `<?php echo $prodi_tampil; ?>`, 
                          `<?php echo $event['quota']; ?>`, 
                          `<?php echo $tanggal_tampil; ?>`, 
                          `<?php echo $event['status']; ?>`, 
                          `<?php echo htmlspecialchars($event['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8'); ?>`
                      )">Detail &rarr;</button>
                    </td>
                  </tr>
                <?php 
                endwhile; 
                
                if (!$has_pending):
                ?>
                  <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 2rem;">Tidak ada event yang menunggu persetujuan.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

      <?php elseif ($role === 'panitia'): ?>
        <?php
        // Query metrik data dinamis khusus milik panitia yang login
        $total_created_res = mysqli_query($koneksi, "SELECT COUNT(*) as count FROM events WHERE panitia_id = $user_id");
        $total_created_events = mysqli_fetch_assoc($total_created_res)['count'] ?? 0;

        $total_part_res = mysqli_query($koneksi, "SELECT COUNT(r.id) as count FROM registration r 
                                                  JOIN events e ON r.event_id = e.id 
                                                  WHERE e.panitia_id = $user_id");
        $total_my_peserta = mysqli_fetch_assoc($total_part_res)['count'] ?? 0;

        $panitia_pending_res = mysqli_query($koneksi, "SELECT COUNT(*) as count FROM events WHERE panitia_id = $user_id AND status = 'Pending'");
        $panitia_pending_count = mysqli_fetch_assoc($panitia_pending_res)['count'] ?? 0;
        ?>
        <section id="panitia-dashboard-view" class="view-section active">
          <div class="view-header">
            <h2 class="view-title">Dashboard Panitia</h2>
          </div>

          <div class="stats-grid">
            <div class="stat-card">
              <span class="stat-label">Total Event Dibuat</span>
              <span class="stat-value"><?php echo $total_created_events; ?></span>
              <span class="stat-sublabel">Dari Semua Event</span>
            </div>
            
            <div class="stat-card">
              <span class="stat-label">Total Peserta</span>
              <span class="stat-value" style="color: #5ce1e6;"><?php echo $total_my_peserta; ?></span>
              <span class="stat-sublabel">Dari Semua Event</span>
            </div>
            
            <div class="stat-card">
              <span class="stat-label">Menunggu Persetujuan</span>
              <span class="stat-value pending"><?php echo $panitia_pending_count; ?></span>
              <span class="stat-sublabel pending" style="color: #ffaf94;">Sedang Di Tinjau Admin</span>
            </div>
          </div>

          <div class="panitia-grid-layout">
            
            <div>
              <div class="section-banner" style="background-color: rgba(92, 225, 230, 0.1); color: #5ce1e6; padding: 1rem 1.5rem; font-weight: 700; border-radius: 12px 12px 0 0; border: 1px solid #2d2d34; border-bottom: none;">
                Event Yang Anda Buat
              </div>
              <div class="table-container" style="border-radius: 0 0 12px 12px;">
                <table>
                  <thead>
                    <tr>
                      <th>Nama Event</th>
                      <th>Tanggal Dibuat</th>
                      <th>Kuota</th>
                      <th>Status</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $my_evt_query = mysqli_query($koneksi, "SELECT * FROM events WHERE panitia_id = $user_id ORDER BY id DESC");
                    $has_evt = false;
                    while ($row_evt = mysqli_fetch_assoc($my_evt_query)):
                        $has_evt = true;
                        $status_class = (strtolower($row_evt['status']) === 'pending') ? 'pending' : ((strtolower($row_evt['status']) === 'approved' || strtolower($row_evt['status']) === 'approve' || strtolower($row_evt['status']) === 'selesai') ? 'approved' : 'reject');
                    ?>
                      <tr>
                        <td class="event-name-cell"><?php echo htmlspecialchars($row_evt['name']); ?></td>
                        <td><?php echo date('d - m - Y', strtotime($row_evt['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($row_evt['quota']); ?></td>
                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($row_evt['status']); ?></span></td>
                        <td>
                          <button class="btn-detail" onclick="viewEventDetails(
                            `<?php echo htmlspecialchars($row_evt['name'], ENT_QUOTES, 'UTF-8'); ?>`, 
                            'Anda (Panitia)', 
                            `<?php echo $row_evt['quota']; ?>`, 
                            `<?php echo $row_evt['created_at']; ?>`, 
                            `<?php echo $row_evt['status']; ?>`, 
                            `<?php echo htmlspecialchars($row_evt['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8'); ?>`
                          )">Detail &rarr;</button>
                        </td>
                      </tr>
                    <?php 
                    endwhile; 
                    if (!$has_evt):
                    ?>
                      <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">Anda belum pernah membuat event.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <div>
              <div class="section-banner" style="background-color: rgba(92, 225, 230, 0.1); color: #5ce1e6; padding: 1rem 1.5rem; font-weight: 700; border-radius: 12px 12px 0 0; border: 1px solid #2d2d34; border-bottom: none;">
                Aksi Cepat
              </div>
              <div class="quick-action-container" style="border-radius: 0 0 12px 12px; border-top: none;">
                <a href="buat_event.php" class="quick-action-card">
                    <div class="quick-action-text">
                        <h4>Buat Event Baru</h4>
                        <p>Buat Event Untuk di Tinjau Admin</p>
                    </div>
                    <div class="arrow-icon">&rarr;</div>
                </a>
                <a href="sertifikat.php" class="quick-action-card">
                    <div class="quick-action-text">
                        <h4>Upload Sertifikat</h4>
                        <p>Pilih Event dan Upload Sertifikat</p>
                    </div>
                    <div class="arrow-icon">&rarr;</div>
                </a>
              </div>
            </div>

          </div>
        </section>

      <?php else: ?>
        <?php
        // Fetch Participant Statistics from database
        $app_res = mysqli_query($koneksi, "SELECT COUNT(*) as count FROM events WHERE status = 'Approved'");
        $total_approved = mysqli_fetch_assoc($app_res)['count'];

        $my_reg_stmt = mysqli_prepare($koneksi, "SELECT COUNT(*) as count FROM registration WHERE id = ?");
        mysqli_stmt_bind_param($my_reg_stmt, "i", $user_id);
        mysqli_stmt_execute($my_reg_stmt);
        $my_reg_res = mysqli_stmt_get_result($my_reg_stmt);
        $my_reg_count = mysqli_fetch_assoc($my_reg_res)['count'];
        mysqli_stmt_close($my_reg_stmt);

        // Fetch user's registered event IDs to mark them in view
        $reg_ids = [];
        $reg_ids_stmt = mysqli_prepare($koneksi, "SELECT event_id FROM registration WHERE id = ?");
        mysqli_stmt_bind_param($reg_ids_stmt, "i", $user_id);
        mysqli_stmt_execute($reg_ids_stmt);
        $reg_ids_res = mysqli_stmt_get_result($reg_ids_stmt);
        while ($reg_row = mysqli_fetch_row($reg_ids_res)) {
            $reg_ids[] = $reg_row[0];
        }
        mysqli_stmt_close($reg_ids_stmt);
        ?>
        <section id="peserta-dashboard-view" class="view-section active">
          <div class="view-header">
            <h2 class="view-title">Dashboard Peserta</h2>
          </div>

          <div class="stats-grid">
            
            <div class="stat-card">
              <span class="stat-label">Total Event Tersedia</span>
              <span class="stat-value"><?php echo $total_approved; ?></span>
              <span class="stat-sublabel">Event dapat diikuti</span>
            </div>
            
            <div class="stat-card">
              <span class="stat-label">Event Diikuti</span>
              <span class="stat-value" style="color: var(--color-success);"><?php echo $my_reg_count; ?></span>
              <span class="stat-sublabel">Telah terdaftar</span>
            </div>
            
            <div class="stat-card">
              <span class="stat-label">Sertifikat Saya</span>
              <span class="stat-value" style="color: var(--color-pending);">0</span>
              <span class="stat-sublabel">Belum tersedia</span>
            </div>

          </div>

          <div class="section-banner" style="background-color: rgba(111, 208, 246, 0.1); color: var(--accent-blue); padding: 1rem 1.5rem; font-weight: 700; border-radius: 12px 12px 0 0; border: 1px solid #2d2d34; border-bottom: none;">
            Event Saya (Terdaftar)
          </div>
          <div class="table-container" style="border-radius: 0 0 12px 12px; margin-bottom: 3rem;">
            <table>
              <thead>
                <tr>
                  <th>Nama Event</th>
                  <th>Kategori</th>
                  <th>Tanggal Event</th>
                  <th>Penyelenggara</th>
                  <th>Tanggal Daftar</th>
                  <th>Status Kehadiran</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Fetch participant's registered events
                $my_events_stmt = mysqli_prepare($koneksi, "
                    SELECT e.*, r.waktu_daftar, r.status
                    FROM registration r
                    JOIN events e ON r.event_id = e.id
                    WHERE r.peserta_id = ?
                    ORDER BY r.id DESC
                ");
                mysqli_stmt_bind_param($my_events_stmt, "i", $user_id);
                mysqli_stmt_execute($my_events_stmt);
                $my_events_res = mysqli_stmt_get_result($my_events_stmt);
                
                $has_registered = false;
                while ($ev = mysqli_fetch_assoc($my_events_res)):
                    $has_registered = true;
                    $presence_text = ($ev['presence_status'] === 'Hadir') ? 'Hadir' : 'Belum Hadir';
                    $presence_style = ($ev['presence_status'] === 'Hadir') 
                        ? 'color: var(--color-success); font-weight: 700;' 
                        : 'color: var(--color-pending); font-weight: 700;';
                ?>
                  <tr>
                    <td class="event-name-cell"><?php echo htmlspecialchars($ev['name']); ?></td>
                    <td><?php echo htmlspecialchars($ev['category']); ?></td>
                    <td><?php echo htmlspecialchars($ev['date']); ?></td>
                    <td><?php echo htmlspecialchars($ev['panitia']); ?></td>
                    <td><?php echo date('d M Y', strtotime($ev['registration_date'])); ?></td>
                    <td><span style="<?php echo $presence_style; ?>"><?php echo $presence_text; ?></span></td>
                    <td class="actions-cell">
                      <a href="event_detail.php?id=<?php echo $ev['id']; ?>" class="btn-sm btn-approve" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; height: 32px; padding: 0 1rem;">Lihat Detail</a>
                    </td>
                  </tr>
                <?php 
                endwhile;
                mysqli_stmt_close($my_events_stmt);
                
                if (!$has_registered):
                ?>
                  <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem 1.5rem;">
                      Anda belum mendaftar di event apapun. <br><br>
                      <a href="event.php" class="btn-submit" style="text-decoration: none;">Cari & Daftar Event Sekarang</a>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>
      <?php endif; ?>

    </main>

  </div>

  <div id="toast" class="toast <?php echo !empty($toast_msg) ? 'show' : ''; ?> <?php echo ($toast_type === 'success') ? 'toast-success' : ''; ?>">
    <span id="toast-message"><?php echo htmlspecialchars($toast_msg); ?></span>
  </div>

  <dialog id="details-modal" style="background-color: var(--bg-card); color: var(--text-main); border: 1px solid #2d2d34; border-radius: 12px; padding: 2rem; max-width: 500px; width: 90%; margin: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.5); outline: none;">
    <h3 id="modal-title" style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--accent-blue);">Nama Event</h3>
    <div style="margin-bottom: 1rem; font-size: 0.8rem; color: var(--text-muted);">
      <p>Panitia: <span id="modal-panitia" style="color: var(--text-main); font-weight: 600;"></span></p>
      <p>Tanggal: <span id="modal-date" style="color: var(--text-main); font-weight: 600;"></span></p>
      <p>Kuota: <span id="modal-quota" style="color: var(--text-main); font-weight: 600;"></span></p>
      <p>Status: <span id="modal-status" style="font-weight: 700;"></span></p>
    </div>
    <div style="border-top: 1px solid #2d2d34; padding-top: 1rem; margin-bottom: 1.5rem;">
      <h4 style="font-size: 0.9rem; font-weight: 700; margin-bottom: 0.5rem;">Deskripsi:</h4>
      <p id="modal-desc" style="font-size: 0.85rem; color: #e2e2e5; line-height: 1.5;"></p>
    </div>
    <div style="display: flex; justify-content: flex-end;">
      <button onclick="document.getElementById('details-modal').close()" style="background-color: #27272c; color: var(--text-main); border: 1px solid #3d3d45; border-radius: 6px; padding: 0.5rem 1.25rem; cursor: pointer; font-size: 0.85rem; font-weight: 700; transition: background-color var(--transition-speed);" onmouseover="this.style.backgroundColor='#32323a'" onmouseout="this.style.backgroundColor='#27272c'">Tutup</button>
    </div>
  </dialog>

  <script>
    // Elements
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');
    const detailsModal = document.getElementById('details-modal');
    
    // Modal fields
    const modalTitle = document.getElementById('modal-title');
    const modalPanitia = document.getElementById('modal-panitia');
    const modalDate = document.getElementById('modal-date');
    const modalQuota = document.getElementById('modal-quota');
    const modalStatus = document.getElementById('modal-status');
    const modalDesc = document.getElementById('modal-desc');

    // Auto-hide toast if shown
    if (toast.classList.contains('show')) {
      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }

    // Show custom toast alert
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

    // View Details Modal (called from table rows)
    function viewEventDetails(name, panitia_id, quota, tgl_mulai, status, deskripsi) {
    // Amankan data jika ada parameter yang bernilai undefined atau null
    name = name || 'Nama Event Tidak Tersedia';
    panitia_id = panitia_id || 'Tidak Ada Data Panitia';
    quota = quota || '0';
    tgl_mulai = tgl_mulai || 'Tanggal Belum Diatur';
    status = status ? status.toLowerCase() : 'pending'; // Ubah ke huruf kecil untuk validasi
    deskripsi = deskripsi || 'Tidak ada deskripsi untuk event ini.';

    // Tulis teks ke dalam DOM Element Modal
    modalTitle.textContent = name;
    modalPanitia.textContent = panitia_id;
    modalQuota.textContent = quota;
    modalDate.textContent = tgl_mulai;
    modalDesc.textContent = deskripsi;
    
    // Reset class style pada elemen badge status
    modalStatus.className = ''; 

    // Penkondisian status berdasarkan nilai dari database
    if (status === 'pending') {
      modalStatus.classList.add('status-badge', 'pending');
      modalStatus.textContent = 'PENDING';
    } else if (status === 'approve' || status === 'approved' || status === 'selesai') {
      modalStatus.classList.add('status-badge', 'approved');
      modalStatus.textContent = 'DISETUJUI';
    } else if (status === 'reject' || status === 'rejected') {
      modalStatus.classList.add('status-badge', 'reject');
      modalStatus.textContent = 'DITOLAK';
    } else {
      // Cadangan jika status bernilai lain
      modalStatus.classList.add('status-badge', 'pending');
      modalStatus.textContent = status.toUpperCase();
    }

      detailsModal.showModal();
    }
  </script>
</body>
</html>