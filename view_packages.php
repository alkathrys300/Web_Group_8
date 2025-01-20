<?php
include('db_connection.php'); 


if (isset($_GET['BranchID'])) {
    $branch_id = intval($_GET['BranchID']); 

    
    $branch_query = "SELECT branch_name FROM branches WHERE branch_id = $branch_id";
    $branch_result = mysqli_query($conn, $branch_query);

    if ($branch_result && mysqli_num_rows($branch_result) > 0) {
        $branch = mysqli_fetch_assoc($branch_result);
        $branch_name = htmlspecialchars($branch['branch_name']);
    } else {
        die("Branch not found.");
    }

    
    $query = "SELECT * FROM packages WHERE branch_id = $branch_id";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }
} else {
    die("BranchID not provided.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages - <?= $branch_name ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .packages {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .package-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 300px;
            padding: 20px;
            text-align: center;
        }

        .package-card h2 {
            color: #3498db;
        }

        .package-card p {
            margin: 5px 0;
        }

        .actions button {
            margin: 5px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-update {
            background-color: #3498db;
            color: #fff;
        }

        .btn-delete {
            background-color: #e74c3c;
            color: #fff;
        }

        .add-button {
            display: inline-block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #2ecc71;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }

        .add-button:hover {
            background-color: #27ae60;
        }

        .back-button {
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
        }

        .back-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Manage Packages - <?= $branch_name ?></h1>
        <a href="branch_management.php" class="back-button">Back to Branches</a>

        <div class="packages">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='package-card'>";
                    echo "<h2>" . htmlspecialchars($row['package_name']) . "</h2>";
                    echo "<p>Price: RM" . htmlspecialchars($row['price']) . "</p>";
                    echo "<p>Description: " . (isset($row['description']) ? htmlspecialchars($row['description']) : "N/A") . "</p>";
                    echo "<div class='actions'>";
                    echo "<button class='btn-update' onclick=\"window.location.href='update_package.php?PackageID=" . htmlspecialchars($row['id']) . "'\">Update</button>";
                    echo "<button class='btn-delete' onclick=\"window.location.href='delete_package.php?PackageID=" . htmlspecialchars($row['id']) . "'\">Delete</button>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>No packages found for this branch.</p>";
            }
            ?>
        </div>

        <a href="add_package.php?BranchID=<?= $branch_id ?>" class="add-button">Add New Package</a>
    </div>
</body>

</html>