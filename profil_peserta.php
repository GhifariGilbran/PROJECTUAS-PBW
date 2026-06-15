<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

$role     = $_SESSION['user_role'] ?? '';
$username = $_SESSION['username'] ?? '';
$user_id  = $_SESSION['user_id']  ?? 0;

if ($role !== 'peserta') {
    header("Location: dashboard.php");
    exit();
}

$success_msg = '';
$error_msg   = '';

// ── PROSES UPDATE PROFIL ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $nim          = trim($_POST['nim'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $prodi        = $_POST['prodi'] ?? '';
    $angkatan     = trim($_POST['angkatan'] ?? '');
    $no_hp        = trim($_POST['no_hp'] ?? '');
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi    = $_POST['konfirmasi_password'] ?? '';

    // Validasi dasar
    if (empty($nama_lengkap)) {
        $error_msg = "Nama lengkap tidak boleh kosong.";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Format email tidak valid.";
    } elseif (!empty($password_baru) && $password_baru !== $konfirmasi) {
        $error_msg = "Konfirmasi password tidak cocok.";
    } elseif (!empty($password_baru) && strlen($password_baru) < 6) {
        $error_msg = "Password minimal 6 karakter.";
    } else {
        if (!empty($password_baru)) {
            $hashed = password_hash($password_baru, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, "
                UPDATE users
                SET nama_lengkap=?, nim=?, email=?, prodi=?, angkatan=?, no_hp=?, password=?, updated_at=NOW()
                WHERE id=?
            ");
            mysqli_stmt_bind_param($stmt, "sssssssi",
                $nama_lengkap, $nim, $email, $prodi, $angkatan, $no_hp, $hashed, $user_id);
        } else {
            $stmt = mysqli_prepare($koneksi, "
                UPDATE users
                SET nama_lengkap=?, nim=?, email=?, prodi=?, angkatan=?, no_hp=?, updated_at=NOW()
                WHERE id=?
            ");
            mysqli_stmt_bind_param($stmt, "ssssssi",
                $nama_lengkap, $nim, $email, $prodi, $angkatan, $no_hp, $user_id);
        }

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['username'] = $username; // tetap
            $success_msg = "Profil berhasil diperbarui.";
        } else {
            $error_msg = "Gagal memperbarui profil. Silakan coba lagi.";
        }
        mysqli_stmt_close($stmt);
    }
}

// ── AMBIL DATA PROFIL TERKINI ─────────────────────────────────────────────────
$profil_stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($profil_stmt, "i", $user_id);
mysqli_stmt_execute($profil_stmt);
$profil = mysqli_fetch_assoc(mysqli_stmt_get_result($profil_stmt));
mysqli_stmt_close($profil_stmt);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Saya - UniVent</title>
  <link rel="stylesheet" href="style.css?v=1.1">
  <link rel="stylesheet" href="dashboard_peserta.css">
  <style>
    /* ── PROFIL PAGE STYLES ── */
    .profil-wrapper {
      display: grid;
      grid-template-columns: 260px 1fr;
      gap: 1.5rem;
      align-items: start;
    }

    /* Kartu info kiri */
    .profil-card-left {
      background: var(--card-bg, #1e1e2e);
      border: 1px solid var(--border, #2a2a3d);
      border-radius: 14px;
      padding: 2rem 1.5rem;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 1rem;
      text-align: center;
    }

    .avatar-circle {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: linear-gradient(135deg, #38bdf8, #0ea5e9);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2rem;
      font-weight: 700;
      color: #fff;
      flex-shrink: 0;
    }

    .profil-card-left .p-name {
      font-size: 1rem;
      font-weight: 600;
      color: var(--text, #e2e8f0);
    }

    .profil-card-left .p-role {
      font-size: 0.75rem;
      color: #38bdf8;
      background: rgba(56,189,248,.12);
      padding: 2px 10px;
      border-radius: 99px;
      text-transform: uppercase;
      letter-spacing: .05em;
    }

    .profil-meta-list {
      width: 100%;
      margin-top: .5rem;
      display: flex;
      flex-direction: column;
      gap: .6rem;
    }

    .profil-meta-item {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 2px;
      padding: .6rem .75rem;
      background: rgba(255,255,255,.04);
      border-radius: 8px;
    }

    .profil-meta-item .meta-label {
      font-size: .68rem;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: .06em;
    }

    .profil-meta-item .meta-value {
      font-size: .85rem;
      color: var(--text, #e2e8f0);
      font-weight: 500;
      word-break: break-all;
    }

    /* Form kanan */
    .profil-form-card {
      background: var(--card-bg, #1e1e2e);
      border: 1px solid var(--border, #2a2a3d);
      border-radius: 14px;
      padding: 2rem;
    }

    .form-section-title {
      font-size: .7rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .1em;
      color: #64748b;
      margin: 1.5rem 0 .75rem;
      padding-bottom: .4rem;
      border-bottom: 1px solid var(--border, #2a2a3d);
    }

    .form-section-title:first-child { margin-top: 0; }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: .4rem;
    }

    .form-group.full { grid-column: 1 / -1; }

    .form-group label {
      font-size: .8rem;
      color: #94a3b8;
      font-weight: 500;
    }

    .form-group input,
    .form-group select {
      background: rgba(255,255,255,.05);
      border: 1px solid var(--border, #2a2a3d);
      border-radius: 8px;
      padding: .6rem .85rem;
      color: var(--text, #e2e8f0);
      font-size: .9rem;
      outline: none;
      transition: border-color .2s;
      width: 100%;
      box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
      border-color: #38bdf8;
    }

    .form-group input::placeholder { color: #475569; }

    .form-group select option { background: #1e1e2e; }

    .form-group input[readonly] {
      opacity: .5;
      cursor: not-allowed;
    }

    .btn-save {
      margin-top: 1.5rem;
      padding: .65rem 1.75rem;
      background: #38bdf8;
      color: #0f172a;
      border: none;
      border-radius: 8px;
      font-size: .9rem;
      font-weight: 600;
      cursor: pointer;
      transition: background .2s, transform .1s;
    }

    .btn-save:hover  { background: #0ea5e9; }
    .btn-save:active { transform: scale(.97); }

    /* Alert */
    .alert {
      padding: .75rem 1rem;
      border-radius: 8px;
      font-size: .875rem;
      margin-bottom: 1.25rem;
      display: flex;
      align-items: center;
      gap: .5rem;
    }

    .alert-success {
      background: rgba(34,197,94,.12);
      border: 1px solid rgba(34,197,94,.3);
      color: #4ade80;
    }

    .alert-error {
      background: rgba(239,68,68,.12);
      border: 1px solid rgba(239,68,68,.3);
      color: #f87171;
    }

    @media (max-width: 768px) {
      .profil-wrapper { grid-template-columns: 1fr; }
      .form-grid { grid-template-columns: 1fr; }
      .form-group.full { grid-column: 1; }
    }
  </style>
</head>
<body>

  <header>
    <div class="logo-container">
      <h1 class="logo-title">Uni<span>Vent</span></h1>
      <span class="logo-subtitle">University Event</span>
    </div>
    <div class="header-right">
      <div class="user-profile-meta">
        
        <span class="user-info-role"><?php echo htmlspecialchars(ucfirst($role)); ?></span>
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
            <a href="dashboard_peserta.php" class="menu-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
              Dashboard
            </a>
          </li>
          <li>
            <a href="event.php" class="menu-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
              Cari Event
            </a>
          </li>
          <li>
            <a href="my_events_peserta.php" class="menu-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
              Event Saya
            </a>
          </li>
          <li>
            <a href="sertifikat_peserta.php" class="menu-link">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
              Sertifikat
            </a>
          </li>
        </ul>
        <span class="menu-title" style="margin-top:1rem;">Manajemen Pembayaran</span>
        <ul class="menu-items">
          <li>
            <a href="pembayaran_peserta.php" class="menu-link">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="6" y1="15" x2="10" y2="15"/></svg>          
              Pembayaran Event
            </a>
          </li>
        </ul>
        <span class="menu-title" style="margin-top:1rem;">Pengaturan</span>
        <ul class="menu-items">
          <li>
            <a href="profil_peserta.php" class="menu-link active">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              Profil
            </a>
          </li>
        </ul>
      </div>
    </aside>

    <main>
      <div class="view-header">
        <h2 class="view-title">Profil Saya</h2>
      </div>

      <?php if ($success_msg): ?>
        <div class="alert alert-success">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
          <?php echo htmlspecialchars($success_msg); ?>
        </div>
      <?php endif; ?>

      <?php if ($error_msg): ?>
        <div class="alert alert-error">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          <?php echo htmlspecialchars($error_msg); ?>
        </div>
      <?php endif; ?>

      <div class="profil-wrapper">

        <!-- Kartu Kiri: Info Ringkas -->
        <div class="profil-card-left">
          <div class="avatar-circle">
            <?php echo strtoupper(substr($profil['nama_lengkap'] ?? $username, 0, 1)); ?>
          </div>
          <div class="p-name"><?php echo htmlspecialchars($profil['nama_lengkap'] ?? $username); ?></div>
          <div class="p-role">Peserta</div>

          <div class="profil-meta-list">
            <div class="profil-meta-item">
              <span class="meta-label">Username</span>
              <span class="meta-value"><?php echo htmlspecialchars($profil['username']); ?></span>
            </div>
            <div class="profil-meta-item">
              <span class="meta-label">NIM</span>
              <span class="meta-value"><?php echo htmlspecialchars($profil['nim'] ?? '-'); ?></span>
            </div>
            <div class="profil-meta-item">
              <span class="meta-label">Program Studi</span>
              <span class="meta-value"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $profil['prodi'] ?? '-'))); ?></span>
            </div>
            <div class="profil-meta-item">
              <span class="meta-label">Angkatan</span>
              <span class="meta-value"><?php echo htmlspecialchars($profil['angkatan'] ?? '-'); ?></span>
            </div>
            <div class="profil-meta-item">
              <span class="meta-label">No. HP</span>
              <span class="meta-value"><?php echo htmlspecialchars($profil['no_hp'] ?? '-'); ?></span>
            </div>
          </div>
        </div>

        <!-- Form Edit Kanan -->
        <div class="profil-form-card">
          <form method="POST" action="profil_peserta.php">

            <div class="form-section-title">Informasi Pribadi</div>
            <div class="form-grid">
              <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap"
                       value="<?php echo htmlspecialchars($profil['nama_lengkap'] ?? ''); ?>"
                       placeholder="Nama lengkap" required>
              </div>
              <div class="form-group">
                <label for="nim">NIM</label>
                <input type="text" id="nim" name="nim"
                       value="<?php echo htmlspecialchars($profil['nim'] ?? ''); ?>"
                       placeholder="Nomor Induk Mahasiswa">
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="<?php echo htmlspecialchars($profil['email'] ?? ''); ?>"
                       placeholder="email@example.com">
              </div>
              <div class="form-group">
                <label for="no_hp">No. HP / WhatsApp</label>
                <input type="text" id="no_hp" name="no_hp"
                       value="<?php echo htmlspecialchars($profil['no_hp'] ?? ''); ?>"
                       placeholder="08xxxxxxxxxx">
              </div>
              <div class="form-group">
                <label for="prodi">Program Studi</label>
                <select id="prodi" name="prodi">
                  <option value="">-- Pilih Prodi --</option>
                  <option value="informatika" <?php echo ($profil['prodi'] ?? '') === 'informatika' ? 'selected' : ''; ?>>Informatika</option>
                  <option value="sistem_informasi" <?php echo ($profil['prodi'] ?? '') === 'sistem_informasi' ? 'selected' : ''; ?>>Sistem Informasi</option>
                </select>
              </div>
              <div class="form-group">
                <label for="angkatan">Angkatan</label>
                <input type="text" id="angkatan" name="angkatan"
                       value="<?php echo htmlspecialchars($profil['angkatan'] ?? ''); ?>"
                       placeholder="Contoh: 2022" maxlength="10">
              </div>
              <div class="form-group">
                <label>Username</label>
                <input type="text" value="<?php echo htmlspecialchars($profil['username']); ?>" readonly>
              </div>
            </div>

            <div class="form-section-title">Ganti Password <span style="font-weight:400;text-transform:none;font-size:.75rem;color:#475569;">(kosongkan jika tidak ingin ganti)</span></div>
            <div class="form-grid">
              <div class="form-group">
                <label for="password_baru">Password Baru</label>
                <input type="password" id="password_baru" name="password_baru"
                       placeholder="Minimal 6 karakter">
              </div>
              <div class="form-group">
                <label for="konfirmasi_password">Konfirmasi Password</label>
                <input type="password" id="konfirmasi_password" name="konfirmasi_password"
                       placeholder="Ulangi password baru">
              </div>
            </div>

            <button type="submit" class="btn-save">Simpan Perubahan</button>
          </form>
        </div>

      </div>
    </main>
  </div>

</body>
</html>