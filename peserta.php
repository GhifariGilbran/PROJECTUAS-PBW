<?php
session_start();
include 'koneksi.php';

// Check authentication & restrict to admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$role = $_SESSION['user_role'];
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Get all approved events
$events_res = mysqli_query($koneksi, "SELECT * FROM events WHERE status = 'Approved' ORDER BY id DESC");
$events_list = [];
while ($row = mysqli_fetch_assoc($events_res)) {
    $events_list[] = $row;
}

$selected_event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// Handle Presence toggling (Only Admin/Committee can toggle, but let's allow it from here to make it functional)
if (isset($_GET['action']) && $_GET['action'] === 'toggle_presence' && isset($_GET['reg_id'])) {
    $reg_id = (int)$_GET['reg_id'];
    
    // Check current status
    $curr_stmt = mysqli_prepare($koneksi, "SELECT presence_status FROM registrations WHERE id = ?");
    mysqli_stmt_bind_param($curr_stmt, "i", $reg_id);
    mysqli_stmt_execute($curr_stmt);
    mysqli_stmt_bind_result($curr_stmt, $curr_status);
    mysqli_stmt_fetch($curr_stmt);
    mysqli_stmt_close($curr_stmt);
    
    $new_status = ($curr_status === 'Hadir') ? 'Belum Hadir' : 'Hadir';
    // Format: 10 - 24 - 2026 10.30
    $new_time = ($new_status === 'Hadir') ? date('m - d - Y H.i') : '-';
    
    $upd_stmt = mysqli_prepare($koneksi, "UPDATE registrations SET presence_status = ?, presence_time = ? WHERE id = ?");
    mysqli_stmt_bind_param($upd_stmt, "ssi", $new_status, $new_time, $reg_id);
    mysqli_stmt_execute($upd_stmt);
    mysqli_stmt_close($upd_stmt);
    
    header("Location: peserta.php?event_id=" . $selected_event_id);
    exit();
}

// Fetch participants registered in the selected event
$participants = [];
if ($selected_event_id > 0) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT r.id AS reg_id, u.username AS nim, u.nama_lengkap, r.registration_date, r.presence_status, r.presence_time
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        WHERE r.event_id = ?
        ORDER BY r.id ASC
    ");
    mysqli_stmt_bind_param($stmt, "i", $selected_event_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $participants[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Peserta - UniVent</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* Styling khusus scrollbar horizontal */
    .event-picker-list::-webkit-scrollbar {
      height: 6px;
    }
    .event-picker-list::-webkit-scrollbar-track {
      background: #1a1a1e;
      border-radius: 3px;
    }
    .event-picker-list::-webkit-scrollbar-thumb {
      background: #555;
      border-radius: 3px;
    }
    .event-picker-list::-webkit-scrollbar-thumb:hover {
      background: #777;
    }
  </style>
</head>
<body>

  <!-- Top Navigation Header -->
  <header>
    <div class="logo-container">
      <h1 class="logo-title">Uni<span>Vent</span></h1>
      <span class="logo-subtitle">University Event</span>
    </div>
    
    <div class="header-right" style="gap: 1.5rem; align-items: center;">
      <div class="user-profile-meta">
        <span class="user-info-text"><?php echo htmlspecialchars($username); ?></span>
        <span class="user-info-role"><?php echo htmlspecialchars($role); ?></span>
      </div>
      <!-- Circular Avatar Icon to match image -->
      <div class="avatar-container" style="color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; margin-right: 0.5rem;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
          <circle cx="12" cy="7" r="4"/>
        </svg>
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
              <a href="peserta.php" class="menu-link active">
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
              <a href="event.php" class="menu-link">
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
        <div class="view-header" style="flex-direction: column; align-items: flex-start; gap: 0.25rem; margin-bottom: 1.5rem;">
          <h2 class="view-title">Kelola Peserta</h2>
          <p style="color: var(--text-muted); font-size: 0.9rem;">Lihat Daftar Dan Kehadiran Peserta per Event</p>
        </div>

        <!-- Pilih Event Section (Search-based) -->
        <div class="event-picker-section" style="background-color: var(--bg-card); border: 1px solid #2d2d34; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; position: relative;">
          
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 class="filter-label" style="color: var(--text-main); font-size: 0.85rem; font-weight: 700; text-transform: uppercase; margin: 0;">Pilih & Cari Event</h3>
            <?php if ($selected_event_id > 0): ?>
              <?php
              $selected_ev = null;
              foreach ($events_list as $ev) {
                  if ($ev['id'] == $selected_event_id) {
                      $selected_ev = $ev;
                      break;
                  }
              }
              ?>
            <?php endif; ?>
          </div>

          <!-- Search Input Group -->
          <div class="search-input-group" style="position: relative; margin-bottom: 1rem;">
            <svg class="search-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); z-index: 10;">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="event-search-box" class="search-control" placeholder="Ketik nama event untuk mencari..." style="padding-left: 3rem; width: 100%; height: 44px; background-color: #1a1a1e; border: 1px solid #2d2d34; border-radius: 8px; color: var(--text-main); font-size: 0.9rem;" autocomplete="off" value="<?php echo isset($selected_ev) ? htmlspecialchars($selected_ev['name']) : ''; ?>">
            
            <!-- Dropdown Search Results -->
            <div id="event-search-results" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background-color: #18181c; border: 1px solid #2d2d34; border-radius: 8px; margin-top: 0.5rem; max-height: 250px; overflow-y: auto; z-index: 1000; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
              <!-- Rendered via JS -->
            </div>
          </div>

          <!-- Selected Event Display -->
          <?php if (isset($selected_ev) && $selected_ev !== null): ?>
            <div style="background-color: rgba(111, 208, 246, 0.05); border: 1px dashed var(--accent-blue); padding: 1rem; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 1.5rem;">📅</span>
                <div>
                  <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;"><?php echo htmlspecialchars($selected_ev['name']); ?></div>
                  <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.15rem;"><?php echo htmlspecialchars($selected_ev['date']); ?> | <?php echo htmlspecialchars($selected_ev['location']); ?></div>
                </div>
              </div>
              <span style="background-color: var(--accent-blue); color: var(--text-dark); font-size: 0.7rem; font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 4px; text-transform: uppercase;">Aktif</span>
            </div>
          <?php else: ?>
            <div style="color: var(--text-muted); font-size: 0.85rem; font-style: italic;">
              Silakan cari dan pilih event di atas untuk melihat peserta yang terdaftar.
            </div>
          <?php endif; ?>

        </div>

        <?php if ($selected_event_id > 0): ?>
        <!-- Table Title Banner -->
        <div class="section-banner-peserta" style="background-color: var(--accent-blue); color: var(--text-dark); padding: 1rem 1.5rem; font-weight: 700; border-radius: 12px 12px 0 0; border: 1px solid #2d2d34; border-bottom: none; font-size: 0.95rem;">
          Semua Peserta Dalam Event
        </div>

        <div class="table-container" style="border-radius: 0 0 12px 12px; margin-bottom: 3rem;">
          <table>
            <thead>
              <tr>
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
              <?php if (empty($participants)): ?>
                <tr>
                  <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 3rem 1.5rem;">
                    Belum ada peserta yang mendaftar pada event ini.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($participants as $p): 
                    $is_present = ($p['presence_status'] === 'Hadir');
                    $status_style = $is_present ? 'color: var(--accent-blue); font-weight: 700;' : 'color: var(--color-pending); font-weight: 700;';
                ?>
                  <tr>
                    <td class="event-name-cell"><?php echo htmlspecialchars($p['nama_lengkap']); ?></td>
                    <td style="font-weight: 600;"><?php echo htmlspecialchars($p['nim']); ?></td>
                    <td>Informatika</td>
                    <td><?php echo date('m - d - Y', strtotime($p['registration_date'])); ?></td>
                    <td style="font-family: monospace; font-size: 0.85rem;"><?php echo htmlspecialchars($p['presence_time'] && $p['presence_time'] !== '-' ? $p['presence_time'] : '-'); ?></td>
                    <td><span style="<?php echo $status_style; ?>"><?php echo htmlspecialchars($p['presence_status']); ?></span></td>
                    <td class="actions-cell">
                      <a href="peserta.php?event_id=<?php echo $selected_event_id; ?>&action=toggle_presence&reg_id=<?php echo $p['reg_id']; ?>" class="btn-sm <?php echo $is_present ? 'btn-reject' : 'btn-approve'; ?>" style="text-decoration: none; display: inline-block; text-align: center; width: 90px;">
                        <?php echo $is_present ? 'Batal Hadir' : 'Hadirkan'; ?>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php else: ?>
          <!-- Beautiful Empty State when no event is selected -->
          <div class="empty-state" style="margin-top: 1rem; margin-bottom: 3rem;">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color: var(--text-muted); opacity: 0.6; margin-bottom: 1rem;">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <h3>Belum Ada Event Terpilih</h3>
            <p>Silakan cari dan pilih salah satu event menggunakan kotak pencarian di atas untuk melihat dan mengelola daftar kehadiran peserta.</p>
          </div>
        <?php endif; ?>

      </section>
    </main>

  </div>

  <script>
    function showFeatureAlert(featureName) {
      alert('Fitur "' + featureName + '" adalah mockup.');
    }

    // Interactive event search
    const eventsList = <?php echo json_encode($events_list); ?>;
    const searchBox = document.getElementById('event-search-box');
    const searchResults = document.getElementById('event-search-results');

    function renderList(list) {
      searchResults.innerHTML = '';
      list.forEach(ev => {
        const item = document.createElement('div');
        item.style.padding = '0.75rem 1rem';
        item.style.cursor = 'pointer';
        item.style.borderBottom = '1px solid #2d2d34';
        item.style.transition = 'background-color 0.2s';
        
        item.addEventListener('mouseenter', () => item.style.backgroundColor = 'rgba(255,255,255,0.03)');
        item.addEventListener('mouseleave', () => item.style.backgroundColor = 'transparent');
        
        item.innerHTML = `
          <div style="font-weight: 600; color: var(--text-main); font-size: 0.85rem;">${ev.name}</div>
          <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem;">${ev.date} | ${ev.category}</div>
        `;
        
        item.addEventListener('click', () => {
          window.location.href = `peserta.php?event_id=${ev.id}`;
        });
        
        searchResults.appendChild(item);
      });
      searchResults.style.display = 'block';
    }

    searchBox.addEventListener('input', function() {
      const query = this.value.toLowerCase().trim();
      if (query === '') {
        searchResults.style.display = 'none';
        return;
      }

      const filtered = eventsList.filter(ev => 
        ev.name.toLowerCase().includes(query) || 
        ev.category.toLowerCase().includes(query) ||
        ev.panitia.toLowerCase().includes(query)
      );

      if (filtered.length > 0) {
        renderList(filtered);
      } else {
        searchResults.innerHTML = '<div style="padding: 0.75rem 1rem; color: var(--text-muted); font-size: 0.85rem; font-style: italic;">Event tidak ditemukan</div>';
        searchResults.style.display = 'block';
      }
    });

    document.addEventListener('click', function(e) {
      if (!searchBox.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
      }
    });

    searchBox.addEventListener('focus', function() {
      if (this.value.trim() === '') {
        // Show recent events as suggestions
        renderList(eventsList.slice(0, 5));
      }
    });
  </script>
</body>
</html>
