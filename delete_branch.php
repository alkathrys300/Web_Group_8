<?php
include('db_connection.php'); 

if (isset($_GET['BranchID'])) {
    $branch_id = intval($_GET['BranchID']);

    $query = "DELETE FROM branches WHERE branch_id = $branch_id";

    if (mysqli_query($conn, $query)) {
        header("Location: branch_management.php");
        exit;
    } else {
        echo "Error deleting branch: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request.";
}
?>