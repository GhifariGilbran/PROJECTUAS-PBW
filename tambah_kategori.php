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
  <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

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

            <li>
              <a href="kategori.php" class="menu-link" style="color:aqua; margin-left: 20%;">
                | Tambah Kategori
              </a>
            </li>
          </ul>

          <span class="menu-title" style="margin-top: 1rem;">Laporan</span>
          <ul class="menu-items">
            <li>
              <a href="statistik.php" class="menu-link" >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                Statistik
              </a>
            </li>
          </ul>
        </div>
    </aside>

    <main>
       
        <a href="Kategori.php" class="btn-back-link">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                Kembali
        </a>
        <div class="view-header">
                    <h2 class="view-title">Kategori</h2>

        </div>

        <span>Tambah Kategori Baru Untuk Event Selanjutnya</span>
        
        <br><br>
        <?php if(isset($_SESSION['toast_msg'])): ?>
            <script>
            Swal.fire({
                icon: '<?= $_SESSION['toast_type'] ?>',
                title: '<?= $_SESSION['toast_type'] == "success" ? "Berhasil" : "Gagal" ?>',
                text: '<?= $_SESSION['toast_msg'] ?>'
            });
            </script>
            <?php
            unset($_SESSION['toast_msg']);
            unset($_SESSION['toast_type']);
            endif;
        ?>

        <form action="proses_tambah_kategori.php" method="post">
            <div class="form-group">
                <label class="form-label" for="nama">Nama Kategori</label>
                <input type="text" name="nama" id="nama" class="input-text" placeholder="Nama kategori baru">
            </div>

            <br>

            <div class="form-group">
                <label class="form-label" for="deskripsi">deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" class="input-text" placeholder="Masukkan deskripsi dari kategori yang ingin anda buat.."></textarea>
            </div>

            <br>

            <button type="submit" class="btn-submit">Simpan</button>
        </form>
      
    </main>

  </div>

  <div id="toast" class="toast <?php echo !empty($toast_msg) ? 'show' : ''; ?> <?php echo ($toast_type === 'success') ? 'toast-success' : ''; ?>">
    <span id="toast-message"><?php echo htmlspecialchars($toast_msg); ?></span>
  </div>

  <script>
   
  </script>
</body>
</html>
