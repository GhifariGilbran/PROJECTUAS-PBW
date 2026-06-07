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

// Handle Delete User
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['toast_msg'] = "Pengguna berhasil dihapus!";
        $_SESSION['toast_type'] = "success";
    }
    mysqli_stmt_close($stmt);
    header("Location: kelola_pengguna.php");
    exit();
}

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
  <title>Kelola Pengguna - UniVent</title>
  <link rel="stylesheet" href="style.css">
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
              <a href="kelola_pengguna.php" class="menu-link active">
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
        <div class="view-header">
          <h2 class="view-title">Kelola Pengguna</h2>
        </div>

        <form method="GET" action="kelola_pengguna.php" style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem;">
          <?php 
          $pilihanakun = $_GET['pilihanakun'] ?? ''; 
          $search = $_GET['search'] ?? ''; 
          ?>

          <select class="input-text" name="pilihanakun" id="pilihanakun" onchange="this.form.submit()" style="max-width: 200px; margin: 0;">
              <option value="" <?= $pilihanakun == '' ? 'selected' : '' ?>>Semua Role</option>
              <option value="admin" <?= $pilihanakun == 'admin' ? 'selected' : '' ?>>Admin</option>
              <option value="panitia" <?= $pilihanakun == 'panitia' ? 'selected' : '' ?>>Panitia</option>
              <option value="peserta" <?= $pilihanakun == 'peserta' ? 'selected' : '' ?>>Peserta</option>
          </select>

          <input type="text" name="search" class="input-text" placeholder="Cari username atau nama..." value="<?= htmlspecialchars($search) ?>" style="max-width: 300px; margin: 0;">

          <button type="submit" class="btn-submit" style="padding: 0.6rem 1.5rem; margin: 0; width: auto;">Cari</button>
          
          <?php if (!empty($pilihanakun) || !empty($search)): ?>
            <a href="kelola_pengguna.php" class="btn-sm btn-reject" style="text-decoration: none; padding: 0.6rem 1rem; line-height: 1.5; display: inline-flex; align-items: center;">Reset</a>
          <?php endif; ?>
        </form>

        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>No</th>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Role</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Tangkap data filter & keyword pencarian
              $pilihanakun = $_GET['pilihanakun'] ?? '';
              $search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, trim($_GET['search'])) : '';

              // Susun query dasar SQL
              $query_sql = "SELECT * FROM users WHERE 1=1";

              // Gabungkan filter Role jika dipilih
              if ($pilihanakun != '') {
                  $query_sql .= " AND role = '$pilihanakun'";
              }

              // Gabungkan filter Kata Kunci jika diisi
              if ($search_keyword != '') {
                  $query_sql .= " AND (username LIKE '%$search_keyword%' OR nama_lengkap LIKE '%$search_keyword%')";
              }

              // Urutkan data berdasarkan ID terbesar
              $query_sql .= " ORDER BY id DESC";

              // Eksekusi query gabungan database
              $users_res = mysqli_query($koneksi, $query_sql);
              
              $no = 1;
              if(mysqli_num_rows($users_res) > 0) {
                  while($u = mysqli_fetch_assoc($users_res)):
              ?>
              <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($u['username']); ?></td>
                <td><?php echo htmlspecialchars($u['nama_lengkap'] ?? '-'); ?></td>
                <td><span class="status-badge" style="color:var(--accent-blue)"><?php echo htmlspecialchars(ucfirst($u['role'])); ?></span></td>
                <td class="actions-cell">
                  <?php if($u['role'] !== 'admin'){ ?>
                    <a href="kelola_pengguna.php?action=delete&id=<?php echo $u['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');" class="btn-sm btn-reject" style="text-decoration:none;">Hapus</a>
                  <?php } ?>

                    <a href="edit_pengguna.php?id=<?php echo $u['id']; ?>" class="btn-sm btn-approve" style="text-decoration:none;">Edit</a>
                </td>
              </tr>
              <?php 
                  endwhile;
              } else {
              ?>
              <tr>
                <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 2rem;">Data pengguna tidak ditemukan.</td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
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
    function showFeatureAlert(featureName) {
      alert('Fitur "' + featureName + '" adalah mockup.');
    }
  </script>
</body>
</html>