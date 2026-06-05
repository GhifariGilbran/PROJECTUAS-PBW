<?php
session_start();

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

        <form action="proses_register.php" method="POST">
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