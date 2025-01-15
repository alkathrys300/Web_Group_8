<?php 
session_start();
include('db.php');
// Kill session function
function kill_session() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Retrieve the logged-in user's ID

$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'apply': 
                $result = handleCardApplication($conn, $user_id);
                $message = $result['message'];
                $error = $result['error'];
                break;
            case 'topup':
                $result = handleTopUp($conn, $user_id);
                $message = $result['message'];
                $error = $result['error'];
                break;
            case 'cancel':
                $result = handleCardCancellation($conn, $user_id);
                $message = $result['message'];
                $error = $result['error'];
                break;
            case 'view_info':
                $result = viewCardInfo($conn, $user_id);
                $message = $result['message'];
                $error = $result['error'];
                break;
        }
    }
}

function handleCardApplication($conn, $user_id) {
    $response = ['message' => '', 'error' => ''];

    // Check if user already has a pending or approved application
    $sql_check = "SELECT * FROM membershipcard WHERE user_id = ? AND (approval_status = 'pending' OR approval_status = 'approved')";
    $stmt_check = $conn->prepare($sql_check);
    if (!$stmt_check) {
        $response['error'] = "Error preparing query: " . $conn->error;
        return $response;
    }
    $stmt_check->bind_param("s", $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $existing_card = $result_check->fetch_assoc();
        $response['message'] = $existing_card['approval_status'] == 'pending'
            ? "Your application is still pending approval."
            : "You already have an approved membership card. Your card number is: " . $existing_card['Card_Number'];
        return $response;
    }

    // Create a new pending application
    $card_number = 'RP' . date('Ymd') . rand(1000, 9999);
    $membership_id = uniqid('MEM');
    $qr_data = [
        'Card Number' => $card_number,
        'Balance' => 0.00,
        'Expiry Date' => date('Y-m-d', strtotime('+1 year')),
    ];
    $qr_url = "https://quickchart.io/qr?size=150&text=" . urlencode(json_encode($qr_data));

    $sql_insert = "INSERT INTO membershipcard 
        (Membership_Card_id, user_id, Card_Number, Issue_Date, Expiry_Date, 
         card_status, QR_Code, balance, approval_status) 
        VALUES (?, ?, ?, CURRENT_DATE, DATE_ADD(CURRENT_DATE, INTERVAL 1 YEAR), 
               'inactive', ?, 0.00, 'pending')";

    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
        $response['error'] = "Error preparing query: " . $conn->error;
        return $response;
    }
    $stmt_insert->bind_param("ssss", $membership_id, $user_id, $card_number, $qr_url);

    if ($stmt_insert->execute()) {
        // Create notification for admin
        $sql_notify = "INSERT INTO admin_notifications (user_id, message) VALUES (?, 'New membership card application received')";
        $stmt_notify = $conn->prepare($sql_notify);
        if ($stmt_notify) {
            $stmt_notify->bind_param("s", $user_id);
            $stmt_notify->execute();
        }
        $response['message'] = "Application submitted successfully! Waiting for admin approval.";
    } else {
        $response['error'] = "Error submitting application: " . $stmt_insert->error;
    }

    return $response;
}

function handleTopUp($conn, $user_id) {
    $response = ['message' => '', 'error' => ''];
    $amount = $_POST['amount'];
    $sql = "UPDATE membershipcard SET balance = balance + ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ds", $amount, $user_id);

    if ($stmt->execute()) {
        $response['message'] = "Top-up successful! Amount: RM " . number_format($amount, 2);
    } else {
        $response['error'] = "Error processing top-up.";
    }

    return $response;
}

function handleCardCancellation($conn, $user_id) {
    $response = ['message' => '', 'error' => ''];
    $sql = "UPDATE membershipcard SET card_status = 'cancelled' WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);

    if ($stmt->execute()) {
        $response['message'] = "Card cancelled successfully.";
    } else {
        $response['error'] = "Error cancelling card.";
    }

    return $response;
}

function viewCardInfo($conn, $user_id) {
    $response = ['message' => '', 'error' => ''];
    $sql = "SELECT Card_Number, balance, card_status, Expiry_Date, QR_Code, approval_status 
            FROM membershipcard WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cardInfo = $result->fetch_assoc();

    if ($cardInfo) {
        $_SESSION['cardInfo'] = $cardInfo;
        $response['message'] = "Card information loaded successfully.";
    } else {
        $response['error'] = "No card found for your account.";
    }

    return $response;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membership Card Management</title>

    <!-- Bootstrap CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="bg-light">
    <!-- Header -->
    <header class="bg-dark text-white py-4 text-center">
        <h1>Membership Card</h1>
        <p class="mb-0">Student Membership Card Page</p>
       
 <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="Student.php">Student home</a></li>
          <a href="?logout=true" class="logout-button">Logout</a>

        </ul>
        <i class="mobile-nav-toggle bi bi-list"></i>
      </nav>
      

    </div>
  </header>
    </header>
    <main class="container mt-5">
        <!-- Display Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Display QR Code After Application -->
        <?php if (isset($_SESSION['qr_code'])): ?>
            <div class="card my-3 p-3 text-center">
                <h3>Your Card QR Code</h3>
                <img src="<?php echo $_SESSION['qr_code']; ?>" alt="QR Code" class="mx-auto d-block">
                <p class="text-muted mt-2">Please save this QR code for future use.</p>
            </div>
            <?php unset($_SESSION['qr_code']); ?>
        <?php endif; ?>

        <!-- Apply for Membership Card -->
        <div class="card p-4 shadow mb-4">
            <h2>Apply for Membership Card</h2>
            <form method="POST">
                <input type="hidden" name="action" value="apply">
                <button type="submit" class="btn btn-primary w-100">Apply for Card</button>
            </form>
        </div>

        <!-- Top-Up -->
        <!-- Top-Up -->
<div class="card p-4 shadow mb-4">
    <h2>Top Up Card Balance</h2>
    <form method="POST">
        <input type="hidden" name="action" value="topup">
        <div class="mb-3">
            <label for="amount" class="form-label">Amount (RM):</label>
            <input type="number" id="amount" name="amount" class="form-control" min="0" step="0.01" required>
        </div>
        <button type="submit" class="btn btn-primary w-100" <?php echo (isset($_SESSION['cardInfo']) && $_SESSION['cardInfo']['approval_status'] != 'approved') ? 'disabled' : ''; ?>>Top Up</button>
    </form>
</div>

<!-- Cancel Membership Card -->
<div class="card p-4 shadow mb-4">
    <h2>Cancel Membership Card</h2>
    <form method="POST">
        <input type="hidden" name="action" value="cancel">
        <button type="submit" class="btn btn-danger w-100" <?php echo (isset($_SESSION['cardInfo']) && $_SESSION['cardInfo']['approval_status'] != 'approved') ? 'disabled' : ''; ?>>Cancel Card</button>
    </form>
</div>

        <!-- Cancel Membership Card -->
        <div class="card p-4 shadow mb-4">
            <h2>Cancel Membership Card</h2>
            <form method="POST">
                <input type="hidden" name="action" value="cancel">
                <button type="submit" class="btn btn-danger w-100">Cancel Card</button>
            </form>
        </div>

        <!-- View Card Information -->
        <div class="card p-4 shadow">
            <h2>View Card Information</h2>
            <form method="POST">
                <input type="hidden" name="action" value="view_info">
                <button type="submit" class="btn btn-info w-100">View Info</button>
            </form>
        </div>

        <?php if (isset($_SESSION['cardInfo'])): ?>
    <div class="card p-4 shadow mt-4">
        <h3>Card Details</h3>
        <p><strong>Card Number:</strong> <?php echo htmlspecialchars($_SESSION['cardInfo']['Card_Number']); ?></p>
        <p><strong>Balance:</strong> RM <?php echo number_format($_SESSION['cardInfo']['balance'], 2); ?></p>
        <p><strong>Status:</strong> <?php echo ucfirst($_SESSION['cardInfo']['card_status']); ?></p>
        <p><strong>Approval Status:</strong> <?php echo ucfirst($_SESSION['cardInfo']['approval_status']); ?></p>
        <p><strong>Expiry Date:</strong> <?php echo htmlspecialchars($_SESSION['cardInfo']['Expiry_Date']); ?></p>
        <?php if ($_SESSION['cardInfo']['approval_status'] == 'pending'): ?>
            <div class="alert alert-info">
                Your card application is pending approval. Please wait for admin confirmation.
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['cardInfo']['QR_Code'])): ?>
            <p><strong>QR Code:</strong></p>
            <img src="<?php echo htmlspecialchars($_SESSION['cardInfo']['QR_Code']); ?>" alt="QR Code" class="img-fluid" style="max-width: 200px;">
        <?php endif; ?>
    </div>
<?php unset($_SESSION['cardInfo']); ?>
<?php endif; ?>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <p class="mb-0">&copy; 2024 UMPSA Koop Printing Management System (RapidPrint)</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>