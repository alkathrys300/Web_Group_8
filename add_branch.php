<?php
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branch_name = $_POST['branch_name'];
    $manager = $_POST['manager'];
    $phone_number = $_POST['phone_number'];

    $query = "INSERT INTO branches (branch_name, manager, phone_number) VALUES ('$branch_name', '$manager', '$phone_number')";

    if (mysqli_query($conn, $query)) {
        header("Location: branch_management.php");
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
    <title>Add New Branch</title>
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
        <h1>Add New Branch</h1>
        <form method="POST">
            <label for="branch_name">Branch Name:</label>
            <select name="branch_name" id="branch_name" required>
                <option value="Pekan">Pekan</option>
                <option value="Gambang">Gambang</option>
            </select>

            <label for="manager">Manager</label>
            <select name="manager" id="manager" required>
                <option value="Omar">Omar</option>
                <option value="Turki">Turki</option>
                <option value="Raheem">Raheem</option>
            </select>

            <label for="phone_number">Contact Number</label>
            <input type="text" id="phone_number" name="phone_number" required>

            <button type="submit">Add Branch</button>
            <button type="button" class="cancel-button" onclick="window.location.href='branch_management.php'">Cancel</button>
        </form>
    </div>
</body>

</html>
