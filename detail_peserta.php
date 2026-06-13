<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'panitia') {
    header("Location: dashboard.php");
    exit();
}

$role     = $_SESSION['user_role'];
$username = $_SESSION['username'];
$user_id  = $_SESSION['user_id'] ?? 0;

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$event_id) {
    header("Location: peserta.php");
    exit();
}

$status_filter = $_GET['status'] ?? '';

// Ambil info event + kategori (sekaligus panitia_id untuk validasi akses)
$q_event = mysqli_query($koneksi, "
    SELECT e.name, e.status, e.quota, e.panitia_id, c.nama AS kategori
    FROM events e
    LEFT JOIN categories c ON e.category_id = c.id
    WHERE e.id = $event_id
");
$event = mysqli_fetch_assoc($q_event);
if (!$event) {
    header("Location: peserta.php");
    exit();
}

// Validasi akses panitia: hanya boleh kelola event miliknya sendiri
$authorized = ($role === 'admin') || ($role === 'panitia' && $event['panitia_id'] == $user_id);

// Handle Action (Acc / Tolak Pembayaran)
if (isset($_GET['action']) && isset($_GET['reg_id']) && $authorized) {
    $action = $_GET['action'];
    $reg_id = (int)$_GET['reg_id'];

    if ($action === 'acc_payment' || $action === 'tolak_payment') {
        // Ambil payment terkait registration ini
        $p_stmt = mysqli_prepare($koneksi, "SELECT id FROM payment WHERE registration_id = ? ORDER BY id DESC LIMIT 1");
        mysqli_stmt_bind_param($p_stmt, "i", $reg_id);
        mysqli_stmt_execute($p_stmt);
        $p_res = mysqli_stmt_get_result($p_stmt);
        $payment_row = mysqli_fetch_assoc($p_res);
        mysqli_stmt_close($p_stmt);

        if ($payment_row) {
            $payment_id = $payment_row['id'];

            if ($action === 'acc_payment') {
                // Update payment jadi lunas
                $up1 = mysqli_prepare($koneksi, "UPDATE payment SET status_pembayaran = 'lunas' WHERE id = ?");
                mysqli_stmt_bind_param($up1, "i", $payment_id);
                mysqli_stmt_execute($up1);
                mysqli_stmt_close($up1);

                // Update registration jadi terdaftar
                $up2 = mysqli_prepare($koneksi, "UPDATE registration SET status = 'terdaftar' WHERE id = ?");
                mysqli_stmt_bind_param($up2, "i", $reg_id);
                mysqli_stmt_execute($up2);
                mysqli_stmt_close($up2);

                $_SESSION['toast_msg'] = "Pembayaran berhasil disetujui.";
                $_SESSION['toast_type'] = 'success';

            } elseif ($action === 'tolak_payment') {
                // Update payment jadi ditolak (status registration tetap pending_payment)
                $up1 = mysqli_prepare($koneksi, "UPDATE payment SET status_pembayaran = 'ditolak' WHERE id = ?");
                mysqli_stmt_bind_param($up1, "i", $payment_id);
                mysqli_stmt_execute($up1);
                mysqli_stmt_close($up1);

                $_SESSION['toast_msg'] = "Pembayaran ditolak.";
                $_SESSION['toast_type'] = 'info';
            }
        } else {
            $_SESSION['toast_msg'] = "Data pembayaran tidak ditemukan.";
            $_SESSION['toast_type'] = 'info';
        }
    }

    // Redirect kembali ke halaman ini dengan filter status yang sama
    $redirect_url = "detail_peserta.php?id=" . $event_id;
    if ($status_filter != '') {
        $redirect_url .= "&status=" . urlencode($status_filter);
    }
    header("Location: " . $redirect_url);
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

$query_peserta = "
    SELECT r.id AS reg_id, u.nama_lengkap, u.nim, u.prodi, u.no_hp, u.angkatan,
           r.waktu_daftar, r.waktu_hadir, r.status, r.kode_unik,
           p.id AS payment_id, p.status_pembayaran, p.nominal, p.bukti_pembayaran
    FROM registration r
    JOIN users u ON r.peserta_id = u.id
    LEFT JOIN payment p ON p.registration_id = r.id
    WHERE r.event_id = $event_id
";

if ($status_filter != '') {
    $status_filter = mysqli_real_escape_string($koneksi, $status_filter);
    $query_peserta .= " AND r.status = '$status_filter'";
}

$query_peserta .= " ORDER BY r.waktu_daftar ASC";

$q_peserta = mysqli_query($koneksi, $query_peserta);

$total_semua   = mysqli_num_rows($q_peserta);

// Hitung yang hadir
$q_hadir = mysqli_query($koneksi, "
    SELECT COUNT(*) as jumlah FROM registration 
    WHERE event_id = $event_id AND waktu_hadir IS NOT NULL
");
$total_hadir = mysqli_fetch_assoc($q_hadir)['jumlah'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Peserta - UniVent</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .bukti-link {
      display: inline-flex;
      align-items: center;
      gap: 0.3rem;
      color: var(--accent-blue);
      text-decoration: none;
      font-weight: 600;
      font-size: 0.8rem;
    }
    .bukti-link:hover {
      text-decoration: underline;
    }
    .no-bukti {
      color: var(--text-muted);
      font-size: 0.8rem;
      font-style: italic;
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
        <span class="user-info-text"><?php echo htmlspecialchars($username); ?></span>
        <span class="user-info-role"><?php echo htmlspecialchars($role); ?></span>
      </div>
      <a href="logout.php" class="logout-btn-header">Keluar</a>
    </div>
  </header>

  <div class="app-container">

    <aside id="sidebar">
      <?php if($role === 'admin'): ?>
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
            <a href="kelola_pengguna.php" class="menu-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
              Kelola Pengguna
            </a>
          </li>
          <li>
            <a href="peserta.php" class="menu-link active">
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
              <a href="peserta_panitia.php" class="menu-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                Peserta
              </a>
            </li>
            <li>
              <a href="peserta_panitia.php" class="menu-link" style="color: aqua; margin-left: 20%;">
                | Peserta
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

        <!-- Header + tombol kembali -->
         <?php if($role === 'admin'): ?>
        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
          <a href="peserta.php" style="color: var(--text-muted); text-decoration: none; display: flex; align-items: center; gap: 0.4rem; font-size: 0.85rem;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Kembali
          </a>
        </div>
        <?php endif; ?>

        <div class="view-header" style="flex-direction: column; align-items: flex-start; gap: 0.25rem; margin-bottom: 1.5rem;">
          <h2 class="view-title"><?php echo htmlspecialchars($event['name']); ?></h2>
          <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
            <span class="status-badge <?php echo strtolower($event['status']); ?>"><?php echo htmlspecialchars($event['status']); ?></span>
            <span style="color: var(--text-muted); font-size: 0.85rem;">Kategori: <strong style="color: var(--text-main);"><?php echo htmlspecialchars($event['kategori'] ?? 'Tanpa Kategori'); ?></strong></span>
            <span style="color: var(--text-muted); font-size: 0.85rem;">Kuota: <strong style="color: var(--text-main);"><?php echo htmlspecialchars($event['quota']); ?></strong></span>
          </div>
        </div>

        <!-- Stat ringkas -->
        <div class="stats-grid" style="margin-bottom: 1.5rem; grid-template-columns: repeat(3, 1fr);">
          <div class="stat-card">
            <span class="stat-label">Total Pendaftar</span>
            <span class="stat-value"><?php echo $total_semua; ?></span>
            <span class="stat-sublabel">Dari semua status</span>
          </div>
          <div class="stat-card">
            <span class="stat-label">Sudah Hadir</span>
            <span class="stat-value" style="color: var(--color-success);"><?php echo $total_hadir; ?></span>
            <span class="stat-sublabel">waktu_hadir terisi</span>
          </div>
          <div class="stat-card">
            <span class="stat-label">Belum Hadir</span>
            <span class="stat-value" style="color: var(--color-pending);"><?php echo $total_semua - $total_hadir; ?></span>
            <span class="stat-sublabel">waktu_hadir kosong</span>
          </div>
        </div>
          <form method="GET" style="margin-bottom: 1rem; display:flex; gap:10px; align-items:center;">
    
              <input type="hidden" name="id" value="<?= $event_id ?>">

              <select name="status" class="input-text" onchange="this.form.submit()" style="max-width:220px;">
                  <option value="">Semua Status</option>

                  <option value="hadir"
                      <?= ($status_filter == 'hadir') ? 'selected' : '' ?>>
                      Hadir
                  </option>

                  <option value="tidak_hadir"
                      <?= ($status_filter == 'tidak_hadir') ? 'selected' : '' ?>>
                      Tidak Hadir
                  </option>

                  <option value="terdaftar"
                      <?= ($status_filter == 'terdaftar') ? 'selected' : '' ?>>
                      Terdaftar
                  </option>

                  <option value="pending_payment"
                      <?= ($status_filter == 'pending_payment') ? 'selected' : '' ?>>
                      Pending Payment
                  </option>
              </select>

              <?php if($status_filter != ''): ?>
                  <a href="detail_peserta.php?id=<?= $event_id ?>"
                    class="btn-sm btn-reject"
                    style="text-decoration:none;">
                    Reset
                  </a>
              <?php endif; ?>

          </form>
        <!-- Tabel peserta -->
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Lengkap</th>
                <th>NIM</th>
                <th>Prodi</th>
                <th>Angkatan</th>
                <th>No. HP</th>
                <th>Kode Unik</th>
                <th>Waktu Daftar</th>
                <th>Waktu Hadir</th>
                <th>Status</th>
                <th>Pembayaran</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($total_semua > 0):
                $no = 1;
                while ($row = mysqli_fetch_assoc($q_peserta)): ?>
              <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['nama_lengkap'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['nim'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['prodi'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['angkatan'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['no_hp'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($row['kode_unik'] ?? '-'); ?></td>
                <td><?php echo $row['waktu_daftar'] ? date('d/m/Y H:i', strtotime($row['waktu_daftar'])) : '-'; ?></td>
                <td>
                  <?php if ($row['waktu_hadir']): ?>
                    <span style="color: var(--color-success); font-weight: 700;">
                      <?php echo date('d/m/Y H:i', strtotime($row['waktu_hadir'])); ?>
                    </span>
                  <?php else: ?>
                    <span style="color: var(--color-pending);">Belum Hadir</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="status-badge <?php echo strtolower($row['status'] ?? ''); ?>">
                    <?php echo ucfirst($row['status'] ?? '-'); ?>
                  </span>
                </td>
                <td>
                  <?php if ($row['payment_id']): ?>
                    <div style="display:flex; flex-direction:column; gap:0.25rem;">
                      <span class="status-badge <?php echo strtolower($row['status_pembayaran'] ?? ''); ?>">
                        <?php echo ucfirst($row['status_pembayaran'] ?? '-'); ?>
                      </span>
                      <span style="font-size:0.8rem; color: var(--text-muted);">
                        Rp <?php echo number_format($row['nominal'], 0, ',', '.'); ?>
                      </span>
                      <?php if (!empty($row['bukti_pembayaran'])): ?>
                        <a href="uploads/bukti_bayar/<?php echo rawurlencode($row['bukti_pembayaran']); ?>" target="_blank" class="bukti-link">Lihat Bukti &rarr;</a>
                      <?php else: ?>
                        <span class="no-bukti">Tidak ada bukti</span>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span class="no-bukti">-</span>
                  <?php endif; ?>
                </td>
                <td class="actions-cell">
                  <?php if ($authorized && $row['payment_id'] && $row['status_pembayaran'] === 'pending'): ?>
                    <a href="detail_peserta.php?id=<?php echo $event_id; ?>&action=tolak_payment&reg_id=<?php echo $row['reg_id']; ?><?php echo $status_filter != '' ? '&status='.urlencode($status_filter) : ''; ?>"
                       class="btn-sm btn-reject" style="display:inline-flex; align-items:center; text-decoration:none;"
                       onclick="return confirm('Tolak pembayaran peserta ini?')">Tolak</a>
                    <a href="detail_peserta.php?id=<?php echo $event_id; ?>&action=acc_payment&reg_id=<?php echo $row['reg_id']; ?><?php echo $status_filter != '' ? '&status='.urlencode($status_filter) : ''; ?>"
                       class="btn-sm btn-approve" style="display:inline-flex; align-items:center; text-decoration:none;"
                       onclick="return confirm('Setujui pembayaran peserta ini?')">Acc</a>
                  <?php else: ?>
                    <span style="color: var(--text-muted); font-size: 0.8rem;">-</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endwhile;
              else: ?>
              <tr>
                <td colspan="12" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                  Belum ada peserta yang mendaftar di event ini.
                </td>
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