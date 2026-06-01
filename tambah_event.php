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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $panitia = $_POST['panitia'];
    $category = $_POST['category'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    $quota = (int)$_POST['quota'];
    $price = $_POST['price'];
    $desc = $_POST['desc'];
    $poster = $_POST['poster'];
    $status = 'Approved'; // By default, admin created events are approved

    $stmt = mysqli_prepare($koneksi, "INSERT INTO events (name, panitia, category, date, location, quota, price, `desc`, poster, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssssissss", $name, $panitia, $category, $date, $location, $quota, $price, $desc, $poster, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['toast_msg'] = "Event berhasil ditambahkan!";
        $_SESSION['toast_type'] = "success";
        header("Location: kelola_event.php");
        exit();
    } else {
        $error = "Terjadi kesalahan saat menyimpan event.";
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah Event - UniVent</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Top Navigation Header -->
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
    
    <!-- Sidebar -->
    <aside id="sidebar">
        <!-- ADMIN MENU -->
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
          </ul>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main>
      <a href="kelola_event.php" class="btn-back-link">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Kembali ke Kelola Event
      </a>

      <div class="view-header">
        <h2 class="view-title">Tambah Event Baru</h2>
      </div>

      <?php if(isset($error)): ?>
        <div style="background: rgba(255,82,82,0.1); color: var(--color-danger); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <div class="form-card">
        <form action="tambah_event.php" method="POST">
          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label" for="name">Nama Event</label>
              <input type="text" id="name" name="name" class="input-text" required placeholder="Contoh: Seminar Teknologi">
            </div>
            <div class="form-group">
              <label class="form-label" for="panitia">Penyelenggara (Panitia)</label>
              <input type="text" id="panitia" name="panitia" class="input-text" required placeholder="Contoh: BEM Fakultas">
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label" for="category">Kategori</label>
              <select id="category" name="category" class="input-text" required style="cursor: pointer; appearance: none; background-color: var(--bg-input);">
                <option value="Seminar">Seminar</option>
                <option value="Workshop">Workshop</option>
                <option value="Lomba">Lomba</option>
                <option value="Webinar">Webinar</option>
                <option value="Lainnya">Lainnya</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="date">Tanggal & Waktu</label>
              <input type="text" id="date" name="date" class="input-text" required placeholder="Contoh: 15 Oktober 2026, 09:00 WIB">
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label" for="location">Lokasi</label>
              <input type="text" id="location" name="location" class="input-text" required placeholder="Contoh: Aula Utama">
            </div>
            <div class="form-group">
              <label class="form-label" for="poster">URL Poster (Gambar)</label>
              <input type="text" id="poster" name="poster" class="input-text" required placeholder="Contoh: https://example.com/poster.jpg">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="desc">Deskripsi Event</label>
            <textarea id="desc" name="desc" class="textarea-custom" required placeholder="Tuliskan deskripsi lengkap mengenai event ini..."></textarea>
          </div>

          <div class="form-footer-row">
            <div class="price-quota-inputs">
              <div class="small-input-group">
                <label class="form-label" for="quota">Kuota Peserta</label>
                <input type="number" id="quota" name="quota" class="input-text" required placeholder="Contoh: 100" min="1">
              </div>
              <div class="small-input-group">
                <label class="form-label" for="price">Biaya Pendaftaran</label>
                <input type="text" id="price" name="price" class="input-text" required placeholder="Contoh: Gratis atau Rp 50.000">
              </div>
            </div>
            <button type="submit" class="btn-submit">Simpan Event</button>
          </div>
        </form>
      </div>
    </main>

  </div>

</body>
</html>
