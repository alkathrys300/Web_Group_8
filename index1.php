<?php
session_start();

// Kill session function
function kill_session() {
    // Unset all session variables
    $_SESSION = [];
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}

// Handle logout action
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    kill_session();
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - UMPSA Koop Printing Management</title>

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body>
  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl d-flex justify-content-between align-items-center">
      <a href="index.php" class="logo">
        <h1>RapidPrint UMPSA</h1>
        <p>Admin Page</p>
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="user_management.php">User Management</a></li>
          <li><a href="Admain View Card.php">Admin View Card</a></li>
          <li><a href="admin_approval.php">Admin Approval</a></li>
          <li><a href="?logout=true" class="text-danger">Logout</a></li>
        </ul>
        <i class="mobile-nav-toggle bi bi-list"></i>
      </nav>
    </div>
  </header>

  <main>
    <section id="hero" class="hero section">
      <div class="container text-center">
        <h2>Welcome, Admin!</h2>
        <p>Manage your tasks efficiently.</p>
      </div>
    </section>
  </main>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
