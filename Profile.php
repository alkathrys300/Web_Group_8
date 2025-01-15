<?php
session_start();
include('db.php');

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

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Retrieve the logged-in user's ID

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch user info
$sql_user = "SELECT user_name, user_email, User_Phone, user_role, student_card FROM user WHERE user_id = ?";
$stmt_user = $conn->prepare($sql_user);

if (!$stmt_user) {
    die("SQL Error (User Query): " . $conn->error);
}

$stmt_user->bind_param("s", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_info = $result_user->fetch_assoc();

if (!$user_info) {
    die("Error: User not found in the database.");
}

// Fetch membership card info
$sql_card = "SELECT Card_Number, balance, card_status, Expiry_Date, QR_Code FROM membershipcard WHERE user_id = ?";
$stmt_card = $conn->prepare($sql_card);

if (!$stmt_card) {
    die("SQL Error (Card Query): " . $conn->error);
}

$stmt_card->bind_param("s", $user_id);
$stmt_card->execute();
$result_card = $stmt_card->get_result();
$card_info = $result_card->fetch_assoc();

// If no card exists, show a placeholder
$qr_url = $card_info ? $card_info['QR_Code'] : null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Profile - UMPSA Koop Printing Management</title>

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
        <p>Student Profile</p>
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="Profile.php">Student Profile</a></li>
          <li><a href="membership.php">Membership Card</a></li>
          <li><a href="?logout=true" class="text-danger">Logout</a></li>
        </ul>
        <i class="mobile-nav-toggle bi bi-list"></i>
      </nav>
    </div>
  </header>

  <div class="container mt-5">
      <h1 class="text-center mb-4">User Profile</h1>

      <!-- Personal Information Section -->
      <div class="card p-4 mb-4 shadow">
          <h3>Personal Information</h3>
          <form method="POST" action="updateProfile.php">
              <div class="mb-3">
                  <label for="username" class="form-label">Name:</label>
                  <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_info['user_name']); ?>" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label for="email" class="form-label">Email:</label>
                  <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_info['user_email']); ?>" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label for="phone" class="form-label">Phone:</label>
                  <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user_info['User_Phone']); ?>" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label for="role" class="form-label">Role:</label>
                  <input type="text" id="role" value="<?php echo htmlspecialchars($user_info['user_role']); ?>" class="form-control" disabled>
              </div>
              <div class="mb-3">
                  <label for="student_card" class="form-label">Student Card:</label>
                  <input type="text" id="student_card" value="<?php echo htmlspecialchars($user_info['student_card']); ?>" class="form-control" disabled>
              </div>
              <button type="submit" class="btn btn-primary w-100">Update Profile</button>
          </form>
      </div>

      <!-- Membership Card Information Section -->
      <div class="card p-4 shadow">
          <h3>Membership Card Information</h3>
          <?php if ($card_info): ?>
              <p><strong>Card Number:</strong> <?php echo htmlspecialchars($card_info['Card_Number']); ?></p>
              <p><strong>Balance:</strong> RM <?php echo number_format($card_info['balance'], 2); ?></p>
              <p><strong>Status:</strong> <?php echo ucfirst($card_info['card_status']); ?></p>
              <p><strong>Expiry Date:</strong> <?php echo htmlspecialchars($card_info['Expiry_Date']); ?></p>
              <p><strong>QR Code:</strong></p>
              <?php if ($qr_url): ?>
                  <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="QR Code" class="img-fluid" style="max-width: 200px;">
              <?php else: ?>
                  <p class="text-muted">No QR code available for this card.</p>
              <?php endif; ?>
          <?php else: ?>
              <p class="text-muted">No membership card available for this user.</p>
          <?php endif; ?>
      </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
