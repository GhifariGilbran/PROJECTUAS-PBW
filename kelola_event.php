<?php
session_start();
include 'koneksi.php';

// Check authentication
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$role = $_SESSION['user_role'];
$username = $_SESSION['username'];

// Fetch Toast Notification
$toast_msg = isset($_SESSION['toast_msg']) ? $_SESSION['toast_msg'] : "";
$toast_type = isset($_SESSION['toast_type']) ? $_SESSION['toast_type'] : "info";
unset($_SESSION['toast_msg'], $_SESSION['toast_type']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Event - UniVent</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <header>
    <div class="logo-container">
      <h1 class="logo-title">Uni<span>Vent</span></h1>
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
              <a href="kelola_event.php" class="menu-link active">
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
    </aside>

    <main>
        <?php
        $stats = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total, SUM(status='Pending') as pending, SUM(status='approve') as approve FROM events"));
        ?>
        <div class="stats-grid" style="display: flex; gap: 1rem; margin-bottom: 2rem; color: black;">
            <div class="stat-card" style="color: white;"><h3>Total: <?php echo (int)$stats['total']; ?></h3></div>
            <div class="stat-card" style="color: white;"><h3>Pending: <?php echo (int)$stats['pending']; ?></h3></div>
            <div class="stat-card" style="color: white;"><h3>Disetujui: <?php echo (int)$stats['approve']; ?></h3></div>
        </div>

        <div class="view-header" style="display:flex; justify-content:space-between; align-items:center;">
          <h2 class="view-title">Kelola Event</h2>
          <a href="tambah_event.php" class="btn-submit">+ Tambah Event</a>
        </div>

        <?php
          $kategori_res = mysqli_query($koneksi, "SELECT * FROM categories ORDER BY nama ASC");
          $kategori_selected = $_GET['kategori'] ?? '';
          $search = $_GET['search'] ?? '';
        ?>

        <form method="GET" action="kelola_event.php" style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem;">
          <?php 
          $pilihanakun = $_GET['kategori'] ?? ''; 
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
            <a href="kelola_event.php" class="btn-sm btn-reject" style="text-decoration: none; padding: 0.6rem 1rem; line-height: 1.5;">Reset</a>
          <?php endif; ?>
        </form>

        <div class="table-container">
          <table>
            <thead>
              <tr><th>No</th><th>Nama Event</th><th>Kategori</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
              <?php
              // Menangkap keyword pencarian dan mengamankannya dari SQL Injection
              $search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, trim($_GET['search'])) : '';

              // Query dasar
              $query_sql = "SELECT * FROM events WHERE 1=1";
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
                  <?php if($row['status'] === 'p  ending'): {?>
                    <a href="proses_persetujuan_event.php?action=reject&id=<?php echo $row['id']; ?>" class="btn-sm btn-reject" style="display:inline-flex; align-items:center; text-decoration:none;">Tolak</a>
                    <a href="proses_persetujuan_event.php?action=approve&id=<?php echo $row['id']; ?>" class="btn-sm btn-approve" style="display:inline-flex; align-items:center; text-decoration:none;">Setujui</a>                  
                 <?php }endif; ?>
                                     <a href="event_detail.php?id=<?php echo $row['id']; ?>" class="btn-detail" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; height: 32px; padding: 0 1rem;">Detail-></a>

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
          </table>
        </div>
    </main>
  </div>

  <div id="toast" class="toast <?php echo !empty($toast_msg) ? 'show' : ''; ?>">
    <span><?php echo htmlspecialchars($toast_msg); ?></span>
  </div>
  <script>
   
  </script>
</body>
</html>