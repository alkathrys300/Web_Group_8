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

// Function to fetch pending applications
function getPendingApplications($conn) {
    $sql = "SELECT m.*, COALESCE(u.user_name, 'Unknown User') AS username 
            FROM membershipcard m 
            LEFT JOIN user u ON m.user_id = u.user_id 
            WHERE m.approval_status = 'pending' 
            ORDER BY m.Issue_Date DESC";
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        error_log("MySQL Error: " . $conn->error);
        return [];
    }
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Handle admin actions (approve/reject)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $card_id = $_POST['card_id'] ?? '';

    if ($card_id) {
        if ($action == 'approve') {
            $sql = "UPDATE membershipcard SET approval_status = 'approved', card_status = 'active' WHERE Membership_Card_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $card_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Application approved successfully.";
            } else {
                $_SESSION['error_message'] = "Error approving application.";
            }
        } elseif ($action == 'reject') {
            $sql = "UPDATE membershipcard SET approval_status = 'rejected' WHERE Membership_Card_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $card_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Application rejected successfully.";
            } else {
                $_SESSION['error_message'] = "Error rejecting application.";
            }
        }
    } else {
        $_SESSION['error_message'] = "Invalid card ID.";
    }
}

// Fetch pending applications
$pendingApplications = getPendingApplications($conn);
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
                <li><a href="?logout=true" class="text-danger">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="container mt-5">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-header">
                <h2>Pending Applications</h2>
            </div>
            <div class="card-body">
                <?php if (empty($pendingApplications)): ?>
                    <p class="text-muted">No pending applications.</p>
                <?php else: ?>
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Issue Date</th>
                                <th>Card Number</th>
                                <th>User ID</th>
                                <th>Username</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingApplications as $application): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($application['Issue_Date']); ?></td>
                                    <td><?php echo htmlspecialchars($application['Card_Number']); ?></td>
                                    <td><?php echo htmlspecialchars($application['user_id']); ?></td>
                                    <td><?php echo htmlspecialchars($application['username']); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="card_id" value="<?php echo $application['Membership_Card_id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                            <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white text-center py-3">
        <p class="mb-0">&copy; 2024 UMPSA Koop Printing Management System (RapidPrint)</p>
    </footer>
</body>

</html>
