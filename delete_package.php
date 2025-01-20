<?php
include('db_connection.php'); 

if (isset($_GET['PackageID'])) {
    $package_id = intval($_GET['PackageID']); 

    
    $query = "SELECT branch_id FROM packages WHERE id = $package_id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $package = mysqli_fetch_assoc($result);
        $branch_id = $package['branch_id'];

        
        $delete_query = "DELETE FROM packages WHERE id = $package_id";
        if (mysqli_query($conn, $delete_query)) {
            header("Location: view_packages.php?BranchID=$branch_id");
            exit;
        } else {
            die("Error deleting package: " . mysqli_error($conn));
        }
    } else {
        die("Package not found. Please ensure the PackageID exists in the database.");
    }
} else {
    die("PackageID not provided.");
}
?>