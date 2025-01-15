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

// Fetch all student membership cards
function fetchAllStudentCards($conn) {
    $sql = "SELECT * FROM membershipcard";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC); // Fetch all records as an associative array
    } else {
        return [];
    }
}

// Fetch the student cards for the admin
$studentCards = fetchAllStudentCards($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Membership Card Management</title>

    <!-- Bootstrap CSS -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="bg-light">
    <!-- Header -->
    <header class="bg-dark text-white py-4 text-center">
        <h1>Admin Membership Card</h1>
        <p class="mb-0">View Student Membership Cards</p>
        <h6>For Admin</h6>
        <nav id="navmenu" class="navmenu">
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="user_management.php">User Management</a></li>
                <li><a href="index1.php">Admin Page</a></li>
                <li><a href="admin_approval.php">Admin Approval</a></li>
                <li><a href="?logout=true" class="text-danger">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="card shadow p-4">
                    <h2>Student Membership Cards</h2>
                    <?php if (count($studentCards) > 0): ?>
                        <table class="table table-bordered table-hover mt-3">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Card Number</th>
                                    <th>User ID</th>
                                    <th>Balance (RM)</th>
                                    <th>Points</th>
                                    <th>Status</th>
                                    <th>Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($studentCards as $index => $card): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($card['Card_Number']); ?></td>
                                        <td><?php echo htmlspecialchars($card['user_id']); ?></td>
                                        <td>RM <?php echo number_format($card['balance'], 2); ?></td>
                                        <td><?php echo $card['total_points'] ?? 0; ?></td>
                                        <td><?php echo ucfirst($card['card_status']); ?></td>
                                        <td><?php echo htmlspecialchars($card['Expiry_Date']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No membership cards found.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <p class="mb-0">&copy; 2024 UMPSA Koop Printing Management System (RapidPrint)</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
