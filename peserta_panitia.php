<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'panitia') {
    header("Location: login.php");
    exit();
}

$role         = $_SESSION['user_role'];
$username     = $_SESSION['username'] ?? 'Panitia';
$user_id      = $_SESSION['user_id'];

$search_query = "";
$peserta_list = [];
$event_nama   = "";
$message      = "";
$message_type = "";

// Handle update status kehadiran
if (isset($_POST['update_status'])) {
    $reg_id         = (int) $_POST['reg_id'];
    $status_baru    = mysqli_real_escape_string($koneksi, $_POST['status_baru']);
    $allowed_status = ['terdaftar', 'hadir', 'tidak_hadir'];

    if (in_array($status_baru, $allowed_status)) {
        $cek = mysqli_query($koneksi, "
            SELECT r.id FROM registration r
            JOIN events e ON r.event_id = e.id
            WHERE r.id = $reg_id AND e.panitia_id = $user_id
            LIMIT 1
        ");
        if (mysqli_num_rows($cek) > 0) {
            $waktu_hadir_sql = ($status_baru === 'hadir') ? ", waktu_hadir = NOW()" : ", waktu_hadir = NULL";
            mysqli_query($koneksi, "UPDATE registration SET status = '$status_baru' $waktu_hadir_sql WHERE id = $reg_id");
            $message      = "Status peserta berhasil diperbarui.";
            $message_type = "success";
        } else {
            $message      = "Aksi tidak diizinkan.";
            $message_type = "error";
        }
    }
    $search_query = $_POST['search_event'] ?? "";
}

// Handle pencarian event
if (isset($_POST['cari_event']) || !empty($search_query)) {
    if (isset($_POST['search_event'])) {
        $search_query = mysqli_real_escape_string($koneksi, $_POST['search_event']);
    }

    if (!empty($search_query)) {
        $res_event = mysqli_query($koneksi, "
            SELECT id, name FROM events
            WHERE name LIKE '%$search_query%' AND panitia_id = $user_id
            LIMIT 1
        ");

        if (mysqli_num_rows($res_event) > 0) {
            $event_row  = mysqli_fetch_assoc($res_event);
            $event_id   = $event_row['id'];
            $event_nama = $event_row['name'];

            $res_peserta = mysqli_query($koneksi, "
                SELECT
                    r.id            AS reg_id,
                    u.nama_lengkap,
                    u.nim,
                    u.prodi,
                    r.waktu_daftar,
                    r.waktu_hadir,
                    r.status
                FROM registration r
                JOIN users u ON r.peserta_id = u.id
                WHERE r.event_id = $event_id
                ORDER BY r.waktu_daftar ASC
            ");

            while ($row = mysqli_fetch_assoc($res_peserta)) {
                $peserta_list[] = $row;
            }

            if (empty($peserta_list)) {
                $message      = "Belum ada peserta yang mendaftar untuk event ini.";
                $message_type = "info";
            }
        } else {
            $message      = "Event tidak ditemukan atau bukan milik Anda.";
            $message_type = "error";
        }
    }
}

function fmt_date($dt) {
    if (empty($dt) || $dt === '0000-00-00 00:00:00') return '-';
    return date('d - m - Y H:i', strtotime($dt));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Peserta - UniVent</title>
  <link rel="stylesheet" href="style.css?v=1.2">
  <link rel="stylesheet" href="peserta_panitia.css?v=1.0">
</head>
<body>

  <header>
    <div class="logo-container">
      <h1 class="logo-title">Uni<span>Vent</span></h1>
      <span class="logo-subtitle">University Event</span>
    </div>
    <div class="header-right">
      <div class="user-profile-meta">
        <span class="user-info-role"><?php echo htmlspecialchars($username); ?></span>
      </div>
      <a href="logout.php" class="logout-btn-header">Keluar</a>
    </div>
  </header>

  <div class="app-container">

    <aside id="sidebar">
      <div class="menu-group">
        <span class="menu-title">MENU UTAMA</span>
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
            <a href="event_saya.php" class="menu-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
              Event Saya
            </a>
          </li>
          <li>
            <a href="peserta_panitia.php" class="menu-link active">
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
    </aside>

    <main>
      <section class="view-section active">
        <div class="certificate-container">

          <h2 class="page-header-title">Kelola Peserta</h2>
          <p class="page-header-subtitle">Lihat Daftar dan Kehadiran Peserta per Event</p>

          <?php if (!empty($message)): ?>
            <div class="pp-alert pp-alert--<?php echo $message_type; ?>">
              <?php echo htmlspecialchars($message); ?>
            </div>
          <?php endif; ?>

          <!-- Form Pencarian -->
          <form action="peserta_panitia.php" method="POST">
            <label class="form-group-label" for="search_event">Cari Event Berdasarkan Nama</label>
            <div class="search-flex-row">
              <div class="search-input-wrapper">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="11" cy="11" r="8"></circle>
                  <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input
                  type="text"
                  name="search_event"
                  id="search_event"
                  class="input-custom"
                  placeholder="Seminar AI & Machine Learning"
                  value="<?php echo htmlspecialchars($search_query); ?>"
                  required>
              </div>
              <button type="submit" name="cari_event" class="btn-cyan">Cari</button>
            </div>
          </form>

          <!-- Hasil Pencarian -->
          <?php if (!empty($peserta_list)): ?>

            <div class="pp-event-label-wrap">
              <span class="pp-event-label">
                Semua Peserta Dalam Event : <?php echo htmlspecialchars($event_nama); ?>
              </span>
            </div>

            <!-- Ringkasan -->
            <?php
              $total     = count($peserta_list);
              $jml_hadir = count(array_filter($peserta_list, fn($p) => $p['status'] === 'hadir'));
              $jml_blm   = count(array_filter($peserta_list, fn($p) => $p['status'] === 'terdaftar'));
              $jml_tidak = count(array_filter($peserta_list, fn($p) => $p['status'] === 'tidak_hadir'));
            ?>
            <div class="pp-summary-row">
              <div class="pp-summary-card">
                <div class="pp-summary-num"><?php echo $total; ?></div>
                <div class="pp-summary-label">Total Peserta</div>
              </div>
              <div class="pp-summary-card">
                <div class="pp-summary-num pp-summary-num--hadir"><?php echo $jml_hadir; ?></div>
                <div class="pp-summary-label">Hadir</div>
              </div>
              <div class="pp-summary-card">
                <div class="pp-summary-num pp-summary-num--terdaftar"><?php echo $jml_blm; ?></div>
                <div class="pp-summary-label">Terdaftar</div>
              </div>
              <div class="pp-summary-card">
                <div class="pp-summary-num pp-summary-num--tidak"><?php echo $jml_tidak; ?></div>
                <div class="pp-summary-label">Tidak Hadir</div>
              </div>
            </div>

            <!-- Tabel Peserta -->
            <div class="pp-table-wrapper">
              <table class="pp-table">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Nama Peserta</th>
                    <th>NIM</th>
                    <th>Program Studi</th>
                    <th>Waktu Daftar</th>
                    <th>Waktu Hadir</th>
                    <th>Status</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($peserta_list as $i => $p): ?>
                  <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><?php echo htmlspecialchars($p['nama_lengkap']); ?></td>
                    <td><?php echo htmlspecialchars($p['nim'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $p['prodi'] ?? '-'))); ?></td>
                    <td><?php echo fmt_date($p['waktu_daftar']); ?></td>
                    <td><?php echo fmt_date($p['waktu_hadir']); ?></td>
                    <td>
                      <?php
                        $label_map = [
                          'hadir'        => 'Hadir',
                          'terdaftar'    => 'Belum Hadir',
                          'tidak_hadir'  => 'Tidak Hadir',
                        ];
                        $label = $label_map[$p['status']] ?? ucfirst($p['status']);
                      ?>
                      <span class="pp-badge pp-badge--<?php echo $p['status']; ?>"><?php echo $label; ?></span>
                    </td>
                    <td>
                      <form action="peserta_panitia.php" method="POST" class="pp-aksi-form">
                        <input type="hidden" name="reg_id" value="<?php echo $p['reg_id']; ?>">
                        <input type="hidden" name="search_event" value="<?php echo htmlspecialchars($search_query); ?>">
                        <select name="status_baru" class="pp-select">
                          <option value="terdaftar"   <?php echo ($p['status'] === 'terdaftar')   ? 'selected' : ''; ?>>Terdaftar</option>
                          <option value="hadir"       <?php echo ($p['status'] === 'hadir')       ? 'selected' : ''; ?>>Hadir</option>
                          <option value="tidak_hadir" <?php echo ($p['status'] === 'tidak_hadir') ? 'selected' : ''; ?>>Tidak Hadir</option>
                        </select>
                        <button type="submit" name="update_status" class="pp-btn-update">Simpan</button>
                      </form>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

          <?php endif; ?>

        </div>
      </section>
    </main>

  </div>

</body>
</html>