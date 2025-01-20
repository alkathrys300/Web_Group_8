<?php
include('db_connection.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_name = $_POST['package_name'];
    $price = $_POST['price'];
    $branch_id = intval($_GET['BranchID']); 

    
    $query = "INSERT INTO packages (package_name, price, branch_id) VALUES ('$package_name', '$price', '$branch_id')";

    if (mysqli_query($conn, $query)) {
        
        header("Location: view_packages.php?BranchID=$branch_id");
        exit;
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Package</title>
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

        input, button {
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
        <h1>Add New Package</h1>
        <form method="POST">
            <label for="package_name">Package Name:</label>
            <input type="text" id="package_name" name="package_name" required>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <button type="submit">Add Package</button>
            <button type="button" class="cancel-button" onclick="window.location.href='view_packages.php?BranchID=<?= intval($_GET['BranchID']) ?>'">Cancel</button>
        </form>
    </div>
</body>

</html>