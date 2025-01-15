<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rapidprint";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to ensure proper handling of special characters
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Log the error (in a production environment)
    error_log("Database connection error: " . $e->getMessage());
    
    // Show user-friendly message
    die("Unable to connect to the database. Please try again later.");
}
?>