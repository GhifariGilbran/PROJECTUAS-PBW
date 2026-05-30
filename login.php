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
    $role = $_POST['role'];

    // Query database for the user with matching username and role
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM users WHERE username = ? AND role = ?");
    mysqli_stmt_bind_param($stmt, "ss", $username, $role);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Support both plain-text and hashed passwords for maximum compatibility
        if ($password === $row['password'] || password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['nama_lengkap'];
            $_SESSION['user_role'] = $row['role'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Password yang Anda masukkan salah!";
        }
    } else {
        $error_message = "Username tidak ditemukan untuk role yang dipilih!";
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - UniVent</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">

  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-logo">
        <h1>Uni<span>Vent</span></h1>
        <p>Sistem Informasi Event Universitas</p>
      </div>

      <?php if (!empty($error_message)): ?>
        <div class="login-error">
          <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>

      <div class="demo-credentials">
        <div class="demo-credentials-title">Akun Database (Uji Coba)</div>
        <div class="demo-credentials-item">
          <span>Admin:</span>
          <span>admin / admin123</span>
        </div>
        <div class="demo-credentials-item">
          <span>Peserta:</span>
          <span>peserta / peserta123</span>
        </div>
      </div>

      <form action="login.php" method="POST">
        <div class="login-input-group">
          <label for="role">Masuk Sebagai</label>
          <select id="role" name="role" class="login-select" required>
            <option value="peserta">Peserta (Participant)</option>
            <option value="admin">Admin (Administrator)</option>
          </select>
        </div>

        <div class="login-input-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" class="input-text" placeholder="Masukkan username..." required autocomplete="off">
        </div>

        <div class="login-input-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="input-text" placeholder="Masukkan password..." required>
        </div>

        <button type="submit" class="login-btn">Masuk ke Dashboard</button>
      </form>
    </div>
  </div>

</body>
</html>
