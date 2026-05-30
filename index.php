<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_role'])) {
    header("Location: dashboard.php");
} else {
    // If not logged in, force redirect to login page
    header("Location: login.php");
}
exit();
?>
