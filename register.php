<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['user_role'])) {
    header("Location: dashboard.php");
    exit();
}

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Validasi sederhana
    if(empty($nama_lengkap) || empty($username) || empty($password)) {
        $error_message = "Semua kolom wajib diisi!";
    } else {
        // Cek apakah username sudah ada di database
        $check_stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($check_stmt, "s", $username);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $error_message = "Username sudah digunakan, silakan pilih yang lain.";
        } else {
            // Hash password untuk keamanan akun peserta
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'peserta'; // Pendaftaran manual otomatis dikunci sebagai peserta
            
            $insert_stmt = mysqli_prepare($koneksi, "INSERT INTO users (nama_lengkap, username, password, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($insert_stmt, "ssss", $nama_lengkap, $username, $hashed_password, $role);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $success_message = "Akun berhasil dibuat! Silakan masuk.";
            } else {
                $error_message = "Terjadi kesalahan saat mendaftar. Coba lagi.";
            }
            mysqli_stmt_close($insert_stmt);
        }
        mysqli_stmt_close($check_stmt);
    }
}
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

  <div class="login-wrapper-clean">
    <div class="login-container-clean">
      <div class="login-box-clean">
        
        <div class="login-logo-clean">
          <h1>Uni<span>Vent</span></h1>
          <p>Buat akun baru untuk mulai mendaftar event</p>
        </div>

        <?php if (!empty($error_message)): ?>
          <div class="error-msg">
            <?php echo htmlspecialchars($error_message); ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
          <div class="success-msg">
            <?php echo htmlspecialchars($success_message); ?>
          </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
          <div class="login-input-group">
            <label for="nama_lengkap">Nama Lengkap</label>
            <div class="input-wrapper">
              <input type="text" id="nama_lengkap" name="nama_lengkap" class="input-text clean-input" placeholder="Masukkan nama lengkap" required autocomplete="off">
            </div>
          </div>

          <div class="login-input-group">
            <label for="username">Username</label>
            <div class="input-wrapper">
              <input type="text" id="username" name="username" class="input-text clean-input" placeholder="Buat username" required autocomplete="off">
            </div>
          </div>

          <div class="login-input-group" style="margin-bottom: 2rem;">
            <label for="password">Password</label>
            <div class="input-wrapper">
              <input type="password" id="password" name="password" class="input-text clean-input" placeholder="Buat password" required>
            </div>
          </div>

          <button type="submit" class="login-btn">Daftar Sekarang</button>

          <p class="register-link-text">Sudah punya akun? <a href="index.php">Masuk di sini</a></p>
        </form>

      </div>
    </div>
  </div>

</body>
</html>