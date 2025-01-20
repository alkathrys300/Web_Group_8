<?php
// Include the database connection
include 'db_connection.php';

// Get the branch ID from the URL
$branch_id = $_GET['id'] ?? null;
echo "<p>Branch ID: $branch_id</p>";
// Fetch branch details
if ($branch_id) {
    $sql = "SELECT * FROM koperasibranch WHERE branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $branch = $result->fetch_assoc();
} else {
    die("Invalid branch ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Branch Details</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Branch Details</h2>
        <?php if ($branch): ?>
            <div class="card">
                <div class="card-body">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($branch['branch_name']); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($branch['branch_address']); ?></p>
                    <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($branch['branch_number']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($branch['branch_email']); ?></p>
                </div>
            </div>
        <?php else: ?>
            <p>Branch not found.</p>
        <?php endif; ?>
        <a href="branch_management.php" class="btn btn-secondary mt-3">Back to Branch Management</a>
    </div>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>