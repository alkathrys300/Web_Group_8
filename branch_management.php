<?php
include('db_connection.php'); 


$query = "SELECT * FROM branches";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Branches</title>
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

        .branches {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .branch-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 300px;
            padding: 20px;
            text-align: center;
        }

        .branch-card h2 {
            color: #3498db;
        }

        .branch-card p {
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

        .btn-view {
            background-color: #f39c12;
            color: #fff;
        }

        .add-button {
            margin: 20px 5px;
            padding: 10px 20px;
            background-color: #2ecc71;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }

        .add-button:hover {
            background-color: #27ae60;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Manage Branches</h1>

        <div class="branches">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<div class='branch-card'>";
                    echo "<h2>" . htmlspecialchars($row['branch_name']) . "</h2>";
                    echo "<p>Manager: " . htmlspecialchars($row['manager']) . "</p>";
                    echo "<p>Contact Number: " . htmlspecialchars($row['phone_number']) . "</p>";
                    echo "<div class='actions'>";
                    echo "<button class='btn-update' onclick=\"window.location.href='update_branch.php?BranchID=" . $row['branch_id'] . "'\">Update</button>";
                    echo "<button class='btn-delete' onclick=\"window.location.href='delete_branch.php?BranchID=" . $row['branch_id'] . "'\">Delete</button>";
                    echo "<button class='btn-view' onclick=\"window.location.href='view_packages.php?BranchID=" . $row['branch_id'] . "'\">View Packages</button>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<p>No branches found.</p>";
            }
            ?>
        </div>

        <a href="add_branch.php" class="add-button">Add Branch</a>
    </div>
</body>

</html>
