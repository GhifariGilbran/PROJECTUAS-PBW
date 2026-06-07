<?php
session_start();
include 'koneksi.php';

// Check authentication
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'panitia') {
    header("Location: dashboard.php");
    exit();
}

$role = $_SESSION['user_role'];
$username = $_SESSION['username'];
$id = $_SESSION['user_id'];

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
        <span class="user-info-text"><?php echo htmlspecialchars($username); ?></span>
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
              <a href="tambah_event.php" class="menu-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Buat Event
              </a>
            </li>
            <li>
              <a href="event_saya.php" class="menu-link active">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                Event Saya
              </a>
            </li>
            <li>
              <a href="peserta.php" class="menu-link">
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
        <?php
        $stats = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total, SUM(status='Pending') as pending, SUM(status='Approved') as approved FROM events"));
        ?>


        <div class="view-header" style="display:flex; justify-content:space-between; align-items:center;">
          <div>
            <h2 class="view-title">Event yang anda buat</h2>
            
          </div>
            <a href="tambah_event.php" class="btn-submit">+ Tambah Event</a>
        </div>

        

        <form method="GET" action="kelola_event.php" style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem;">
          <?php $search = $_GET['search'] ?? ''; ?>
          <input type="text" name="search" class="input-text" placeholder="Cari nama event..." value="<?= htmlspecialchars($search) ?>" style="max-width: 300px; margin: 0;">
          <button type="submit" class="btn-submit" style="padding: 0.6rem 1.5rem; margin: 0; width: auto;">Cari</button>
          
          <?php if (!empty($search)): ?>
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
              $query_sql = "SELECT * FROM events WHERE panitia_id = $id";

              // Jika input search diisi, tambahkan kondisi filter LIKE
              if ($search_keyword != '') {
                  $query_sql .= " AND name LIKE '%$search_keyword%'";
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
                  <a href="proses_hapus_event.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin hapus?');" class="btn-sm btn-reject">Hapus</a>
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
    function showFeatureAlert(featureName) {
      alert('Fitur "' + featureName + '" adalah mockup.');
    }
  </script>
</body>
</html>