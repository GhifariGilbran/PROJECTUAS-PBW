<?php
session_start();
include 'koneksi.php';

// Check authentication
if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['user_role'];
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Redirect Admin away if they shouldn't view participant event finder
if ($role !== 'peserta') {
    header("Location: dashboard.php");
    exit();
}

// Fetch user's registered event IDs to mark them
$reg_ids = [];
$reg_ids_stmt = mysqli_prepare($koneksi, "SELECT event_id FROM registrations WHERE user_id = ?");
mysqli_stmt_bind_param($reg_ids_stmt, "i", $user_id);
mysqli_stmt_execute($reg_ids_stmt);
$reg_res = mysqli_stmt_get_result($reg_ids_stmt);
while ($reg_row = mysqli_fetch_row($reg_res)) {
    $reg_ids[] = $reg_row[0];
}
mysqli_stmt_close($reg_ids_stmt);

// Handle Search and Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$price_type = isset($_GET['price_type']) ? trim($_GET['price_type']) : '';

$query = "SELECT * FROM events WHERE status = 'Approved'";
$params = [];
$types = "";

if ($search !== '') {
    $query .= " AND (name LIKE ? OR `desc` LIKE ? OR panitia LIKE ? OR location LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ssss";
}

if ($category !== '') {
    $query .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if ($price_type !== '') {
    if ($price_type === 'gratis') {
        $query .= " AND (LOWER(price) LIKE '%gratis%' OR price = '0' OR price = '')";
    } else if ($price_type === 'berbayar') {
        $query .= " AND (LOWER(price) NOT LIKE '%gratis%' AND price != '0' AND price != '')";
    }
}

$query .= " ORDER BY id DESC";

$stmt = mysqli_prepare($koneksi, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$events_res = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Event - UniVent</title>
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
          </ul>
        </div>
      <?php else: ?>
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
              <a href="event.php" class="menu-link active">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Cari Event
              </a>
            </li>
            <li>
              <a href="my_events.php" class="menu-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                Event Saya
              </a>
            </li>
            <li>
              <a href="#" class="menu-link" onclick="showFeatureAlert('Sertifikat')">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                Sertifikat
              </a>
            </li>
          </ul>
        </div>
      <?php endif; ?>
    </aside>

    <!-- Main Content Area -->
    <main>
      <section class="view-section active">
        <div class="view-header" style="flex-direction: column; align-items: flex-start; gap: 0.5rem;">
          <h2 class="view-title">Eksplorasi Event</h2>
          <p style="color: var(--text-muted); font-size: 0.9rem;">Temukan dan ikuti berbagai event universitas menarik di bawah ini.</p>
        </div>

        <!-- Search & Filter Form -->
        <form action="event.php" method="GET" class="search-filter-wrapper">
          <div class="search-input-group">
            <svg class="search-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" name="search" class="search-control" placeholder="Cari nama event, penyelenggara, lokasi..." value="<?php echo htmlspecialchars($search); ?>">
          </div>
          <div class="filters-row">
            <div class="filter-group">
              <label class="filter-label">Kategori</label>
              <select name="category" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                <option value="Seminar" <?php echo $category === 'Seminar' ? 'selected' : ''; ?>>Seminar</option>
                <option value="Workshop" <?php echo $category === 'Workshop' ? 'selected' : ''; ?>>Workshop</option>
                <option value="Lomba" <?php echo $category === 'Lomba' ? 'selected' : ''; ?>>Lomba</option>
                <option value="Webinar" <?php echo $category === 'Webinar' ? 'selected' : ''; ?>>Webinar</option>
                <option value="Lainnya" <?php echo $category === 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
              </select>
            </div>
            <div class="filter-group">
              <label class="filter-label">Biaya</label>
              <select name="price_type" class="filter-select" onchange="this.form.submit()">
                <option value="">Semua Biaya</option>
                <option value="gratis" <?php echo $price_type === 'gratis' ? 'selected' : ''; ?>>Gratis</option>
                <option value="berbayar" <?php echo $price_type === 'berbayar' ? 'selected' : ''; ?>>Berbayar</option>
              </select>
            </div>
            <div class="filter-group" style="margin-left: auto; align-self: flex-end; flex-direction: row; gap: 0.75rem; align-items: center;">
              <button type="submit" class="btn-submit" style="padding: 0 1.5rem; height: 40px; border-radius: 8px;">Cari</button>
              <?php if ($search !== '' || $category !== '' || $price_type !== ''): ?>
                <a href="event.php" class="btn-reset" style="color: var(--color-danger); font-size: 0.85rem; font-weight: 700; text-decoration: none;">Reset</a>
              <?php endif; ?>
            </div>
          </div>
        </form>

        <div class="event-grid">
          <?php 
          $has_events = false;
          while ($event = mysqli_fetch_assoc($events_res)):
              $has_events = true;
              $is_registered = in_array($event['id'], $reg_ids);
          ?>
            <div class="event-card">
              <div class="event-img-wrapper" style="display: flex; align-items: center; justify-content: center; background-color: #1a1a1e; height: 160px; position: relative; border-bottom: 1px solid #2d2d34;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent-blue); opacity: 0.6;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span class="event-card-badge"><?php echo htmlspecialchars($event['category']); ?></span>
              </div>
              <div class="event-card-content">
                <h3 class="event-card-title"><?php echo htmlspecialchars($event['name']); ?></h3>
                <p class="event-card-desc"><?php echo htmlspecialchars($event['desc']); ?></p>
                
                <div class="event-card-meta">
                  <div class="event-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <span><?php echo htmlspecialchars($event['date']); ?></span>
                  </div>
                  <div class="event-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span title="<?php echo htmlspecialchars($event['location']); ?>"><?php echo htmlspecialchars($event['location']); ?></span>
                  </div>
                  <div class="event-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                    <span>Kuota: <strong><?php echo htmlspecialchars($event['quota']); ?></strong></span>
                  </div>
                  <div class="event-meta-item">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    <span style="color: var(--accent-blue); font-weight: 700;"><?php echo htmlspecialchars($event['price']); ?></span>
                  </div>
                </div>

                <div class="event-card-footer">
                  <a href="event_detail.php?id=<?php echo $event['id']; ?>" class="event-card-btn">
                    Lihat Detail &rarr;
                  </a>
                  <?php if ($is_registered): ?>
                    <span class="registered-badge">Terdaftar</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php 
          endwhile;

          if (!$has_events):
          ?>
            <div class="empty-state">
              <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
              <h3>Tidak ada event ditemukan</h3>
              <p>Coba gunakan kata kunci pencarian lain atau ganti filter kategori / biaya Anda.</p>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </main>

  </div>

  <!-- Custom Alert Toast notification -->
  <div id="toast" class="toast">
    <span id="toast-message"></span>
  </div>

  <script>
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toast-message');

    function showToast(message, type = 'info') {
      toastMessage.textContent = message;
      toast.className = 'toast show';
      if (type === 'success') {
        toast.classList.add('toast-success');
      }
      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }

    function showFeatureAlert(featureName) {
      showToast(`Fitur "${featureName}" adalah mockup untuk purwarupa ini.`, 'info');
    }
  </script>
</body>
</html>
