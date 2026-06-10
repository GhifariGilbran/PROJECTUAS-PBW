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
            <a href="sertifikat.php" class="menu-link active">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
              Sertifikat
            </a>
          </li>
        </ul>
      </div>
    </aside>

    <main>
      <section class="view-section active">
          <div class="view-header" style="flex-direction: column; align-items: flex-start; gap: 0.25rem; margin-bottom: 1.5rem;">
            <h2 class="view-title">Upload Sertifikat</h2>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Pilih event terlebih dahulu, untuk memberikan sertifikat kepada peserta</p>
          </div>

          <form method="GET" action="sertifikat.php" style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem;">
            <?php 
            $pilihanakun = $_GET['kategori'] ?? ''; 
            $search = $_GET['search'] ?? ''; 
            ?>

            <?php
            $kategori_res = mysqli_query($koneksi, "SELECT * FROM categories ORDER BY nama ASC");
            $kategori_selected = $_GET['kategori'] ?? '';
            $search = $_GET['search'] ?? '';
          ?>

            
            <select name="kategori" class="input-text" onchange="this.form.submit()" style="max-width: 300px;">
                <option value="">Semua Kategori</option>

                <?php while($kat = mysqli_fetch_assoc($kategori_res)): ?>
                    <option value="<?= $kat['id']; ?>"
                        <?= ($kategori_selected == $kat['id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($kat['nama']); ?>
                    </option>
                <?php endwhile; ?>

            </select>

        
            <?php $search = $_GET['search'] ?? ''; ?>
            <input type="text" name="search" class="input-text" placeholder="Cari nama event..." value="<?= htmlspecialchars($search) ?>" style="margin: 0;">
            <button type="submit" class="btn-submit" style="padding: 0.6rem 1.5rem; margin: 0; width: auto;">Cari</button>
            

            <?php if (!empty($search) || !empty($kategori_selected)): ?>
              <a href="sertifikat.php" class="btn-sm btn-reject" style="text-decoration: none; padding: 0.6rem 1rem; line-height: 1.5;">Reset</a>
            <?php endif; ?>
          </form>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Semua event dibawah ini telah usai dan anda hanya bisa mengupload sertifikat pada event yang telah usai.</p> <br>

          <div class="table-container">
            <table>
              <thead>              
                <tr>
                  <th>No</th>
                  <th>Nama Event</th>
                  <th>Kategori</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>

                <tbody>

                <?php
                  // Menangkap keyword pencarian dan mengamankannya dari SQL Injection
                  $search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, trim($_GET['search'])) : '';

                  // Query dasar
                  $query_sql = "SELECT * FROM events WHERE panitia_id = $user_id AND status = 'selesai'";
                  $kategori_selected = $_GET['kategori'] ?? '';

                  // Jika input search diisi, tambahkan kondisi filter LIKE
                  if ($search_keyword != '') {
                      $query_sql .= " AND name LIKE '%$search_keyword%'";
                  }

                  if ($kategori_selected != '') {
                      $query_sql .= " AND category_id = '$kategori_selected'";
                  }

                  $query_sql .= " ORDER BY id DESC";
                  $res = mysqli_query($koneksi, $query_sql);
                  $no = 1;

                  if (mysqli_num_rows($res) > 0) {
                      while($row = mysqli_fetch_assoc($res)):
                      $kategori_id = $row['category_id'];
                      
                      $kategori_query = mysqli_query($koneksi, "SELECT * FROM categories WHERE id = $kategori_id");
                      $kategori = mysqli_fetch_assoc($kategori_query);
                      
                      // Mengamankan data kategori jika ada id kategori yang tidak valid/dihapus
                      $nama_kategori = $kategori ? $kategori['nama'] : 'Tanpa Kategori';
                  ?>
                      <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($nama_kategori); ?></td>
                    <td><span class="status-badge <?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                    <td>
                        <!-- <a href="proses_hapus_event.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin hapus?');" class="btn-sm btn-reject">Hapus</a> -->
                        <a href="upload_sertifikat.php?id=<?php echo $row['id']; ?>" class="btn-detail" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; height: 32px; padding: 0 1rem;">Upload Sertifikat -></a>
                        
                    </td>
                  </tr>
                  <?php 
                      endwhile;
                  } else {      

                  ?>
                  <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">Event tidak ditemukan.</td>
                  </tr>
                  <?php } ?>

                </tbody>
              </thead>
            </table>
          </div>

        </section>
    </main>

  </div>

</body>
</html>