<?php
session_start();
include 'koneksi.php';

// Check authentication
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'panitia')) {
    header("Location: dashboard.php");
    exit();
}

$role = $_SESSION['user_role'];
$username = $_SESSION['username'];
$id = $_SESSION['user_id'];

$error = isset($_SESSION['tambah_event_error']) ? $_SESSION['tambah_event_error'] : null;
unset($_SESSION['tambah_event_error']);
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
      
      <?php if ($role === 'admin'): ?>
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
              <a href="tambah_event.php" class="menu-link active">
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

      
      <?php endif; ?>

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
        <form action="proses_tambah_event.php" method="POST">
          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label" for="name">Nama Event</label>
              <input type="text" id="name" name="name" class="input-text" required placeholder="Contoh: Seminar Teknologi">
            </div>

            <?php 
              $if = mysqli_query($koneksi, "SELECT id,prodi FROM users WHERE role = 'panitia' AND prodi = 'informatika'");
              $if = mysqli_fetch_assoc($if);

              $si = mysqli_query($koneksi, "SELECT id,prodi FROM users WHERE role = 'panitia' AND prodi = 'sistem_informasi'");
              $si = mysqli_fetch_assoc($si);

              if($role === 'admin'){
            ?>
              <div class="form-group">
              <label class="form-label" for="panitia">Penyelenggara (Panitia)</label>
              <select id="panitia" name="panitia" class="input-text" required>
                <option value=""></option>
                <option value="<?= $if['id'] ?>">Informatika</option>
                <option value="<?= $si['id'] ?>">Sistem Informasi</option>
              </select>
            </div>

            <?php }else{ ?>

        
            <input type="hidden" name="panitia"  value="<?= $id ?>">


            <?php } ?>
            
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label" for="category">Kategori</label>
              <select id="category" name="category" class="input-text" required>
                
                <?php
                  $category = mysqli_query($koneksi, "SELECT * FROM categories");
                  
                  while($row = mysqli_fetch_assoc($category)) {
                ?>

                <option value="<?= $row['id']; ?>"><?= $row['nama'] ?></option>
                    
                <?php } ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label" for="date">Tanggal Mulai</label>
              <input type="date" id="tglmulai" name="tglmulai" class="input-text" required>
            </div>

            <div class="form-group">
              <label class="form-label" for="date">Tanggal Selesai</label>
              <input type="date" id="tglselesai" name="tglselesai" class="input-text" required>
            </div>
          </div>

          <div class="form-row-2">
            <div class="form-group">
              <label class="form-label" for="location">Lokasi</label>
              <input type="text" id="location" name="location" class="input-text" required placeholder="Contoh: Aula Utama">
            </div>
            <div class="form-group">
              <label class="form-label" for="poster">Poster (Gambar)</label>
              <input type="file" id="poster" name="poster" class="input-text" required>
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
                <input type="text" id="price" name="price" class="input-text" required placeholder="Isi 0 jika gratis">
              </div>
            </div>
            <button type="submit" class="btn-submit">Simpan Event</button>
          </div>
        </form>
      </div>
    </main>

  </div>

  <script>
    function showFeatureAlert(featureName) {
      alert('Fitur "' + featureName + '" adalah mockup.');
    }
  </script>
</body>
</html>
