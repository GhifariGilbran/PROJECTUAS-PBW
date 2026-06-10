<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['user_role'])) {
    header("Location: dashboard.php");
    exit();
}

$error_message = isset($_SESSION['register_error']) ? $_SESSION['register_error'] : "";
$success_message = isset($_SESSION['register_success']) ? $_SESSION['register_success'] : "";
unset($_SESSION['register_error'], $_SESSION['register_success']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Akun - UniVent</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-body-clean">

  <!-- Popup Validasi -->
  <div id="popup-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
    background:rgba(0,0,0,0.55); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#1e1e24; border:1px solid #3d3d45; border-radius:12px;
      padding:2rem; max-width:380px; width:90%; text-align:center;
      box-shadow:0 10px 30px rgba(0,0,0,0.5);">
      <svg width="48" height="48" fill="none" stroke="#ff6b6b" stroke-width="2"
        viewBox="0 0 24 24" style="margin-bottom:1rem;">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
      </svg>
      <p id="popup-pesan" style="color:#fff; font-size:1rem; font-weight:600; margin-bottom:1.5rem;"></p>
      <button onclick="tutupPopup()" style="background:#ff6b6b; color:#fff; border:none;
        border-radius:8px; padding:0.6rem 2rem; font-size:0.9rem; font-weight:700; cursor:pointer;">
        OK
      </button>
    </div>
  </div>

  <div class="login-wrapper-clean">
    <div class="login-container-clean">
      <div class="login-box-clean">
        
        <div class="login-logo-clean">
          <h1>Uni<span>Vent</span></h1>
          <p>Buat akun baru untuk mulai mendaftar event</p>
        </div>

        <?php if (!empty($error_message)): ?>
          <div class="error-msg"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
          <div class="success-msg"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form id="form-register" action="proses_register.php" method="POST">
          <div class="form-row-2">

            <div class="login-input-group">
              <label for="username">Username</label>
              <div class="input-wrapper">
                <input type="text" id="username" name="username" class="input-text clean-input"
                  placeholder="Buat username" autocomplete="off">
              </div>
            </div>

            <div class="login-input-group" style="margin-bottom:2rem;">
              <label for="password">Password</label>
              <div class="input-wrapper">
                <input type="password" id="password" name="password" class="input-text clean-input"
                  placeholder="Buat password">
              </div>
            </div>

            <div class="login-input-group">
              <label for="nim">NIM</label>
              <div class="input-wrapper">
                <input type="text" id="nim" name="nim" class="input-text clean-input"
                  placeholder="Masukkan NIM anda" autocomplete="off">
              </div>
            </div>

            <div class="login-input-group">
              <label for="nama_lengkap">Nama Lengkap</label>
              <div class="input-wrapper">
                <input type="text" id="nama_lengkap" name="nama_lengkap" class="input-text clean-input"
                  placeholder="Masukkan nama lengkap" autocomplete="off">
              </div>
            </div>

            <div class="login-input-group" style="margin-bottom:2rem;">
              <label for="email">Email</label>
              <div class="input-wrapper">
                <input type="email" id="email" name="email" class="input-text clean-input"
                  placeholder="Masukkan email">
              </div>
            </div>

            <div class="login-input-group" style="margin-bottom:2rem;">
              <label for="prodi">Prodi</label>
              <div class="input-wrapper">
                <select name="prodi" id="prodi" class="clean-input">
                  <option value="">Pilih Prodi</option>
                  <option value="informatika">Informatika</option>
                  <option value="sistem_informasi">Sistem Informasi</option>
                </select>
              </div>
            </div>

            <div class="login-input-group" style="margin-bottom:2rem;">
              <label for="angkatan">Angkatan</label>
              <div class="input-wrapper">
                <input type="text" id="angkatan" name="angkatan" class="input-text clean-input"
                  placeholder="Masukkan tahun masuk anda">
              </div>
            </div>

            <div class="login-input-group" style="margin-bottom:2rem;">
              <label for="no_hp">Nomor HP</label>
              <div class="input-wrapper">
                <input type="text" id="no_hp" name="no_hp" class="input-text clean-input"
                  placeholder="Masukkan nomor HP yang aktif">
              </div>
            </div>

          </div>

          <button type="submit" class="login-btn">Daftar Sekarang</button>
          <p class="register-link-text">Sudah punya akun? <a href="index.php">Masuk di sini</a></p>
        </form>

      </div>
    </div>
  </div>

  <script>
    document.getElementById('form-register').addEventListener('submit', function(e) {
      const fields = [
        { id: 'username',     label: 'Username' },
        { id: 'password',     label: 'Password' },
        { id: 'nim',          label: 'NIM' },
        { id: 'nama_lengkap', label: 'Nama Lengkap' },
        { id: 'email',        label: 'Email' },
        { id: 'prodi',        label: 'Prodi' },
        { id: 'angkatan',     label: 'Angkatan' },
        { id: 'no_hp',        label: 'Nomor HP' },
      ];

      for (const field of fields) {
        const el = document.getElementById(field.id);
        if (!el || el.value.trim() === '') {
          e.preventDefault();
          tampilPopup(`Field "${field.label}" wajib diisi!`);
          el.focus();
          return;
        }
      }
    });

    function tampilPopup(pesan) {
      document.getElementById('popup-pesan').textContent = pesan;
      const overlay = document.getElementById('popup-overlay');
      overlay.style.display = 'flex';
    }

    function tutupPopup() {
      document.getElementById('popup-overlay').style.display = 'none';
    }

    // Tutup popup kalau klik di luar box
    document.getElementById('popup-overlay').addEventListener('click', function(e) {
      if (e.target === this) tutupPopup();
    });
  </script>

</body>
</html>