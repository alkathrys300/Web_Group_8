<?php 
session_start(); 
include('db.php');

// Kill session function
function kill_session() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}

// Check if logout is requested
if (isset($_GET['logout'])) {
    kill_session();
    // Redirect to login page
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    // Query to check login credentials
    $query = "SELECT * FROM user WHERE user_email = ? AND user_password = ? AND user_role = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $email, $password, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];
        $_SESSION['user_role'] = $user['user_role'];
        
        // Redirect based on role
        if ($user['user_role'] === 'admin') {
            header('Location: index1.php');
            exit();
        } else {
            header('Location: Student.php'); // student dashboard
            exit();
        }
    } else {
        $error = "Invalid credentials!";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <form action="" method="POST">
            <h2>Login</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="input-container">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="input-container">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="input-container">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="customer">Student</option>
                </select>
            </div>
            
            <button type="submit" class="login-button">Login</button>
        </form>
        <br>
        <!-- Add Logout Link -->
    </div>
</body>
</html>
