<?php
// Start the session
session_start();

// Include database connection
require_once('db_connection.php'); // Adjust the path to your database connection file

// Function to check card info
function checkCardInfo($conn) {
    $cardNumber = $_POST['identifier'] ?? '';
    $qrCode = '%' . $_POST['identifier'] . '%';

    // Prepare SQL query
    $sql = "SELECT m.*, SUM(o.Order_Point) as total_points 
            FROM membershipcard m 
            LEFT JOIN `order` o ON m.user_id = o.user_id 
            WHERE m.Card_Number = ? OR m.QR_Code LIKE ?
            GROUP BY m.Membership_Card_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $cardNumber, $qrCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null; // No matching card found
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'check_points') {
        $cardInfo = checkCardInfo($conn);
        $_SESSION['cardInfo'] = $cardInfo; // Save the result in a session
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect to avoid form resubmission
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Card Info</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your CSS file -->
</head>
<body>
    <div class="container">
        <h1>Check Card Information</h1>
        
        <!-- Form for Checking Info -->
        <form method="POST" class="form" id="points-form">
            <input type="hidden" name="action" value="check_points">
            <div class="form-group">
                <label for="identifier">Enter Card Number:</label>
                <input type="text" id="identifier" name="identifier" required>
            </div>
            <button type="submit" class="btn">Check Info</button>
        </form>

        <!-- Display Card Info -->
        <?php if (isset($_SESSION['cardInfo'])): ?>
            <?php $cardInfo = $_SESSION['cardInfo']; unset($_SESSION['cardInfo']); ?>
            <?php if ($cardInfo): ?>
                <div class="card-info">
                    <h3>Card Information</h3>
                    <div class="info-grid">
                        <p><strong>Balance:</strong> RM <?php echo number_format($cardInfo['balance'], 2); ?></p>
                        <p><strong>Points:</strong> <?php echo $cardInfo['total_points'] ?? 0; ?></p>
                        <p><strong>Card Status:</strong> <?php echo ucfirst($cardInfo['card_status']); ?></p>
                        <p><strong>Expiry Date:</strong> <?php echo $cardInfo['Expiry_Date']; ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="card-info">
                    <p>No card information found for the provided identifier.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
