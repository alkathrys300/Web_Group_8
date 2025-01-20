<?php
include('db_connection.php'); 

if (isset($_GET['BranchID'])) {
    $branch_id = intval($_GET['BranchID']);

   
    $query = "SELECT * FROM branches WHERE branch_id = $branch_id";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $branch = mysqli_fetch_assoc($result);
    } else {
        die("Branch not found.");
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_name = $_POST['branch_name'];
    $manager = $_POST['manager'];
    $contact_number = $_POST['contact_number'];

    
    $query = "UPDATE branches SET branch_name = '$branch_name', manager = '$manager', phone_number = '$contact_number' WHERE branch_id = $branch_id";

    if (mysqli_query($conn, $query)) {
        header("Location: branch_management.php");
        exit;
    } else {
        echo "Error updating branch: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Branch</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }

        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        button {
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        .cancel-button {
            background-color: #e74c3c;
            color: white;
        }

        .cancel-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>

<body>
    <div class="form-container">
        <h1>Update Branch</h1>
        <form method="POST">
            <label for="branch_name">Branch Name:</label>
            <select name="branch_name" id="branch_name" required>
                <option value="Pekan" <?= $branch['branch_name'] === 'Pekan' ? 'selected' : '' ?>>Pekan</option>
                <option value="Gambang" <?= $branch['branch_name'] === 'Gambang' ? 'selected' : '' ?>>Gambang</option>
            </select>

            <label for="manager">Manager:</label>
            <select name="manager" id="manager" required>
                <option value="Omar" <?= $branch['manager'] === 'Omar' ? 'selected' : '' ?>>Omar</option>
                <option value="Turki" <?= $branch['manager'] === 'Turki' ? 'selected' : '' ?>>Turki</option>
                <option value="Raheem" <?= $branch['manager'] === 'Raheem' ? 'selected' : '' ?>>Raheem</option>
            </select>

            <label for="contact_number">Contact Number:</label>
            <input type="text" id="contact_number" name="contact_number" value="<?= htmlspecialchars($branch['phone_number']) ?>" required>

            <button type="submit">Update Branch</button>
            <button type="button" class="cancel-button" onclick="window.location.href='branch_management.php'">Cancel</button>
        </form>
    </div>
</body>

</html>