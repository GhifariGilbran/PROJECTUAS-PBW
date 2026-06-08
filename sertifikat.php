<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'panitia') {
    header("Location: login.php");
    exit();
}

$role     = $_SESSION['user_role'];
$username = $_SESSION['username'] ?? 'Panitia';
$user_id  = $_SESSION['user_id'];

$search_query  = "";
$message       = "";
$message_type  = "";
$events_found  = [];

// ── CARI EVENT ────────────────────────────────────────────────────────────────
if (isset($_POST['cari_event'])) {
    $search_query  = mysqli_real_escape_string($koneksi, $_POST['search_event']);
    $result_event  = mysqli_query($koneksi, "
        SELECT id, name, tgl_mulai
        FROM events
        WHERE name LIKE '%$search_query%' AND panitia_id = $user_id
    ");

    if (mysqli_num_rows($result_event) > 0) {
        while ($row = mysqli_fetch_assoc($result_event)) {
            $events_found[] = $row;
        }
    } else {
        $message      = "Event dengan nama '$search_query' tidak ditemukan atau bukan milik Anda.";
        $message_type = "error";
    }
}

// ── KIRIM SERTIFIKAT ──────────────────────────────────────────────────────────
// Logika: simpan link Google Drive ke kolom file_path di tabel certificate
// untuk semua registration yang terdaftar di event tersebut.
if (isset($_POST['kirim_sertifikat'])) {
    $search_query    = mysqli_real_escape_string($koneksi, $_POST['search_event'] ?? '');
    $link_drive      = mysqli_real_escape_string($koneksi, $_POST['link_drive'] ?? '');
    $event_id_target = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;

    if (!empty($link_drive) && $event_id_target > 0) {
        // Verifikasi event milik panitia ini
        $check_event = mysqli_query($koneksi, "
            SELECT id, name FROM events
            WHERE id = $event_id_target AND panitia_id = $user_id
            LIMIT 1
        ");

        if (mysqli_num_rows($check_event) > 0) {
            $event_data = mysqli_fetch_assoc($check_event);
            $event_name = $event_data['name'];

            // Ambil semua registration_id untuk event ini
            $reg_res = mysqli_query($koneksi, "
                SELECT id FROM registration WHERE event_id = $event_id_target
            ");

            if (mysqli_num_rows($reg_res) > 0) {
                $inserted = 0;
                $updated  = 0;

                while ($reg = mysqli_fetch_assoc($reg_res)) {
                    $reg_id       = $reg['id'];
                    $kode_sertif  = strtoupper(uniqid('SERT-'));

                    // Cek apakah sudah ada certificate untuk registration ini
                    $cek = mysqli_query($koneksi, "
                        SELECT id FROM certificate WHERE registration_id = $reg_id LIMIT 1
                    ");

                    if (mysqli_num_rows($cek) > 0) {
                        // Update link
                        mysqli_query($koneksi, "
                            UPDATE certificate
                            SET file_path = '$link_drive'
                            WHERE registration_id = $reg_id
                        ");
                        $updated++;
                    } else {
                        // Insert baru
                        mysqli_query($koneksi, "
                            INSERT INTO certificate (registration_id, kode_sertifikat, file_path)
                            VALUES ($reg_id, '$kode_sertif', '$link_drive')
                        ");
                        $inserted++;
                    }
                }

                $message      = "Sertifikat untuk event '<strong>" . htmlspecialchars($event_name) . "</strong>' berhasil disimpan! ($inserted baru, $updated diperbarui)";
                $message_type = "success";

                // Kembalikan hasil pencarian supaya form tidak hilang
                $res2 = mysqli_query($koneksi, "
                    SELECT id, name, tgl_mulai FROM events
                    WHERE name LIKE '%$search_query%' AND panitia_id = $user_id
                ");
                while ($row = mysqli_fetch_assoc($res2)) {
                    $events_found[] = $row;
                }

            } else {
                $message      = "Tidak ada peserta yang terdaftar di event ini.";
                $message_type = "error";
            }
        } else {
            $message      = "Event tidak ditemukan atau bukan hak akses Anda.";
            $message_type = "error";
        }
    } else {
        $message      = "Mohon pilih event dan isi link Google Drive dengan benar.";
        $message_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Sertifikat - UniVent</title>
  <link rel="stylesheet" href="style.css?v=1.2">
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
            <a href="sertifikat.php" class="menu-link active" style="color:#000; border-radius:12px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
              Sertifikat
            </a>
          </li>
        </ul>
      </div>
    </aside>

    <main>
      <section class="view-section active">
        <div class="certificate-container">

          <h2 class="page-header-title">Upload Sertifikat</h2>
          <p class="page-header-subtitle">Upload Sertifikat Dari Peserta Event</p>

          <?php if (!empty($message)): ?>
            <div class="alert-msg <?php echo ($message_type === 'success') ? 'alert-success' : 'alert-error'; ?>">
              <?php echo $message; ?>
            </div>
          <?php endif; ?>

          <form action="sertifikat.php" method="POST">

            <label class="form-group-label" for="search_event">Cari Event Berdasarkan Nama</label>
            <div class="search-flex-row">
              <div class="search-input-wrapper">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="11" cy="11" r="8"></circle>
                  <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="search_event" id="search_event" class="input-custom"
                  placeholder="Seminar AI & Machine Learning"
                  value="<?php echo htmlspecialchars($search_query); ?>" required>
              </div>
              <button type="submit" name="cari_event" class="btn-cyan">Cari</button>
            </div>

            <!-- HASIL PENCARIAN EVENT -->
            <?php if (!empty($events_found)): ?>
            <div style="margin: 1rem 0;">
              <label class="form-group-label">Pilih Event :</label>
              <table style="width:100%; border-collapse:collapse; margin-bottom:1rem;">
                <thead>
                  <tr style="background:#cbeff7;">
                    <th style="padding:8px; border:1px solid #ddd; text-align:left;">Pilih</th>
                    <th style="padding:8px; border:1px solid #ddd; text-align:left;">Nama Event</th>
                    <th style="padding:8px; border:1px solid #ddd; text-align:left;">Tanggal Mulai</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($events_found as $index => $ev): ?>
                  <tr>
                    <td style="padding:8px; border:1px solid #ddd; text-align:center;">
                      <input type="radio" name="event_id" value="<?php echo $ev['id']; ?>"
                        <?php echo ($index === 0) ? 'checked' : ''; ?> required>
                    </td>
                    <td style="padding:8px; border:1px solid #ddd;"><?php echo htmlspecialchars($ev['name']); ?></td>
                    <td style="padding:8px; border:1px solid #ddd;"><?php echo htmlspecialchars($ev['tgl_mulai']); ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <?php endif; ?>

            <div class="instructions-box">
              <h4>Perlu di Perhatikan :</h4>
              <ol>
                <li>Upload Semua Sertifikat melalui Google Drive.</li>
                <li>Pastikan folder Google Drive dalam keadaan publik.</li>
                <li>Salin link dari folder Google Drive nya, lalu sisipkan ke sini :</li>
              </ol>
            </div>

            <div class="link-submit-row">
              <input type="url" name="link_drive" class="link-input"
                placeholder="Sisipkan link Google Drive anda disini.."
                value="<?php echo htmlspecialchars($_POST['link_drive'] ?? ''); ?>">
              <button type="submit" name="kirim_sertifikat" class="btn-cyan">Kirim</button>
            </div>

          </form>

        </div>
      </section>
    </main>

  </div>

</body>
</html>