<?php
session_start();
include 'koneksi.php';

// Redirect to dashboard if already logged in
if (isset($_SESSION['user_role'])) {
    header("Location: dashboard.php");
    exit();
}

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // PERBAIKAN: Query hanya berdasarkan username saja, tidak mengunci role dari form
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Support both plain-text and hashed passwords for maximum compatibility
        if ($password === $row['password'] || password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['nama_lengkap'];
            $_SESSION['user_role'] = $row['role']; // Role otomatis didapat dari database
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Password yang Anda masukkan salah!";
        }
    } else {
        $error_message = "Username tidak ditemukan!";
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk - UniVent</title>
  <link rel="stylesheet" href="style.css?v=1.1">
</head>
<body class="login-body-clean">

  <div class="login-wrapper-clean">
    <div class="login-container-clean">
      
      <div class="login-logo-clean">
        <h1>Uni<span>Vent</span></h1>
        <p>Sistem Manajemen Event Kampus Modern</p>
      </div>

      <div class="login-box-clean">
        
        <?php if (!empty($error_message)): ?>
          <div class="error-msg">
            <?php echo htmlspecialchars($error_message); ?>
          </div>
        <?php endif; ?>

        <form action="index.php" method="POST">
          <div class="login-input-group">
            <label for="username">Username</label>
            <div class="input-wrapper">
              <input type="text" id="username" name="username" class="input-text clean-input" placeholder="Masukkan username" required autocomplete="off">
            </div>
          </div>

          <div class="login-input-group" style="margin-bottom: 2rem;">
            <label for="password">Password</label>
            <div class="input-wrapper">
              <input type="password" id="password" name="password" class="input-text clean-input" placeholder="Masukkan password" required>
            </div>
          </div>

          <button type="submit" class="login-btn">Masuk ke Dashboard</button>

          <p class="register-link-text">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>

          <div class="demo-credentials" style="margin-top: 2.5rem;">
            <div class="demo-credentials-title">Info Akses Demo</div>
            <div class="demo-credentials-item">
              <span>Akses Admin</span><span>admin / admin123</span>
            </div>
            <div class="demo-credentials-item">
              <span>Akses Peserta</span><span>peserta / peserta123</span>
            </div>
          </div>
        </form>

      </div>
    </div>
  </div>

</body>
</html>