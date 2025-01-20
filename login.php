<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    
    $query = "SELECT * FROM user WHERE user_email = ? AND user_password = ? AND user_role = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $email, $password, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];
        $_SESSION['user_role'] = $user['user_role'];

        
        if ($user['user_role'] === 'admin') {
            header('Location: index1.php');
            exit();
        } else {
            header('Location: Student.php'); 
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
            <h2>LOGIN</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="input-container">
                <label for="email">EMAIL</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="input-container">
                <label for="password">PASSWORD</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="input-container">
                <label for="role">ROLE</label>
                <select id="role" name="role" required>
                    <option value="customer">STUDENT</option>
                    <option value="admin">ADMIN</option>
                    <option value="customer">STAFF</option>
                </select>
            </div>

            <button type="submit" class="login-button">Login</button>
        </form>
    </div>
</body>
</html>