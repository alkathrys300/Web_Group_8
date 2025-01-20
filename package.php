<?php

include 'db_connection.php';


$branch_id = $_GET['id'] ?? null;

if ($branch_id) {
    
    $sql = "SELECT * FROM koperasibranch WHERE branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $branch = $result->fetch_assoc();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $number = $_POST['number'];
    $email = $_POST['email'];

    
    if (!empty($name) && !empty($address) && !empty($number) && !empty($email)) {
        $update_sql = "UPDATE koperasibranch SET branch_name = ?, branch_address = ?, branch_number = ?, branch_email = ? WHERE branch_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param('ssssi', $name, $address, $number, $email, $branch_id);

        if ($update_stmt->execute()) {
            echo "<script>alert('Branch updated successfully!'); window.location.href = 'branch_management.php';</script>";
        } else {
            echo "<script>alert('Failed to update branch.');</script>";
        }
    } else {
        echo "<script>alert('All fields are required.');</script>";
    }
}


if (isset($_POST['delete'])) {
    $delete_sql = "DELETE FROM koperasibranch WHERE branch_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param('i', $branch_id);

    if ($delete_stmt->execute()) {
        echo "<script>alert('Branch deleted successfully!'); window.location.href = 'branch_management.php';</script>";
    } else {
        echo "<script>alert('Failed to delete branch.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Package</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Edit Branch</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Branch Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $branch['branch_name'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Branch Address</label>
                <input type="text" class="form-control" id="address" name="address" value="<?php echo $branch['branch_address'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="number" class="form-label">Contact Number</label>
                <input type="text" class="form-control" id="number" name="number" value="<?php echo $branch['branch_number'] ?? ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $branch['branch_email'] ?? ''; ?>" required>
            </div>
            <button type="submit" name="update" class="btn btn-primary">Update</button>
            <button type="submit" name="delete" class="btn btn-danger">Delete</button>
        </form>
    </div>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>