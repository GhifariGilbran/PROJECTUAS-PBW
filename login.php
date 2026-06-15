<?php
session_start();

if (isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'panitia') {
        header("Location: dashboard.php");
    } else {
        header("Location: dashboard_peserta.php"); 
    }
    exit();
}

$error_message = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : "";
unset($_SESSION['login_error']);
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
        <p>Sistem Manajemen Event Kampus modern</p>
      </div>

      <div class="login-box-clean">
        
        <?php if (!empty($error_message)): ?>
          <div class="error-msg">
            <?php echo htmlspecialchars($error_message); ?>
          </div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST">
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

          <button type="submit" class="login-btn">Masuk</button>

          <p class="register-link-text">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>

        </form>

      </div>
    </div>
  </div>

</body>
</html>