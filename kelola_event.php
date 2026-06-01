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

// Handle Delete Event
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM events WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['toast_msg'] = "Event berhasil dihapus!";
        $_SESSION['toast_type'] = "success";
    }
    mysqli_stmt_close($stmt);
    header("Location: kelola_event.php");
    exit();
}

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
            <li><a href="dashboard.php" class="menu-link"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg> Dashboard</a></li>
            <li><a href="kelola_event.php" class="menu-link active"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect></svg> Kelola Event</a></li>
            <li><a href="kelola_pengguna.php" class="menu-link"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path></svg> Kelola Pengguna</a></li>
          </ul>
        </div>
    </aside>

    <main>
        <?php
        $stats = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total, SUM(status='Pending') as pending, SUM(status='Approved') as approved FROM events"));
        ?>
        <div class="stats-grid" style="display: flex; gap: 1rem; margin-bottom: 2rem;">
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; flex: 1;"><h3>Total: <?php echo (int)$stats['total']; ?></h3></div>
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; flex: 1;"><h3>Pending: <?php echo (int)$stats['pending']; ?></h3></div>
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; flex: 1;"><h3>Disetujui: <?php echo (int)$stats['approved']; ?></h3></div>
        </div>

        <div class="view-header" style="display:flex; justify-content:space-between; align-items:center;">
          <h2 class="view-title">Kelola Event</h2>
          <a href="tambah_event.php" class="btn-submit">+ Tambah Event</a>
        </div>

        <div class="table-container">
          <table>
            <thead>
              <tr><th>No</th><th>Nama Event</th><th>Kategori</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
              <?php
              $res = mysqli_query($koneksi, "SELECT * FROM events ORDER BY id DESC");
              $no = 1;
              while($row = mysqli_fetch_assoc($res)):
              ?>
              <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['category']); ?></td>
                <td><span class="status-badge <?php echo strtolower($row['status']); ?>"><?php echo $row['status']; ?></span></td>
                <td>
                  <a href="kelola_event.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('Yakin hapus?');" class="btn-sm btn-reject">Hapus</a>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
    </main>
  </div>

  <div id="toast" class="toast <?php echo !empty($toast_msg) ? 'show' : ''; ?>">
    <span><?php echo htmlspecialchars($toast_msg); ?></span>
  </div>
</body>
</html>