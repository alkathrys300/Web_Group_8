<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "print");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for adding/editing users
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $user_id = uniqid('STU');
            $name = $_POST['user_name'];
            $email = $_POST['user_email'];
            $phone = $_POST['user_phone'];
            // Store password directly as per your login system
            $password = $_POST['user_password'];
            $student_card = '';

            // Handle file upload
            if (isset($_FILES['student_card']) && $_FILES['student_card']['error'] == 0) {
                $target_dir = "uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $target_file = $target_dir . basename($_FILES["student_card"]["name"]);
                if (move_uploaded_file($_FILES["student_card"]["tmp_name"], $target_file)) {
                    $student_card = $target_file;
                }
            }

            $sql = "INSERT INTO user (user_id, user_name, user_email, user_password, user_role, student_card, status, User_Phone) 
                    VALUES (?, ?, ?, ?, 'customer', ?, 'inactive', ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $user_id, $name, $email, $password, $student_card, $phone);
            $stmt->execute();
        }

        // Handle delete action
        elseif ($_POST['action'] == 'delete' && isset($_POST['user_id'])) {
            $sql = "DELETE FROM user WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $_POST['user_id']);
            $stmt->execute();
        }

        // Handle edit action
        elseif ($_POST['action'] == 'edit' && isset($_POST['user_id'])) {
            $name = $_POST['user_name'];
            $email = $_POST['user_email'];
            $phone = $_POST['user_phone'];
            $status = $_POST['status'];
            
            $sql = "UPDATE user SET user_name = ?, user_email = ?, User_Phone = ?, status = ? WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $name, $email, $phone, $status, $_POST['user_id']);
            $stmt->execute();
        }
    }
}

// Fetch all users
$sql = "SELECT * FROM user WHERE user_role = 'customer' ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="user_management.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>User Management</h1>
            <div class="user-info">
                Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> (Admin)
                <a href="logout.php" class="btn-logout">Logout</a>
                <style>padding: 10px 15px;
    background-color: #ff4d4d;
    color: white;
    text-decoration: none;
    font-size: 14px;
    font-weight: bold;
    border-radius: 5px;
    transition: background-color 0.3s ease;</style>
            </div>
        </div>
        
        <!-- Add User Form -->
        <div class="form-container">
            <h2>Add New User</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label for="user_name">Name:</label>
                    <input type="text" id="user_name" name="user_name" required>
                </div>

                <div class="form-group">
                    <label for="user_email">Email:</label>
                    <input type="email" id="user_email" name="user_email" required>
                </div>

                <div class="form-group">
                    <label for="user_phone">Phone:</label>
                    <input type="tel" id="user_phone" name="user_phone" required>
                </div>

                <div class="form-group">
                    <label for="user_password">Password:</label>
                    <input type="password" id="user_password" name="user_password" required>
                </div>

                <div class="form-group">
                    <label for="student_card">Student Card:</label>
                    <input type="file" id="student_card" name="student_card" required>
                </div>

                <button type="submit" class="btn-submit">Add User</button>
            </form>
        </div>

        <!-- Users List -->
        <div class="users-list">
            <h2>Registered Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['User_Phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td class="actions">
                            <button onclick="viewUser('<?php echo $row['user_id']; ?>')" class="btn-view">View</button>
                            <button onclick="editUser('<?php echo $row['user_id']; ?>', 
                                '<?php echo htmlspecialchars($row['user_name']); ?>', 
                                '<?php echo htmlspecialchars($row['user_email']); ?>', 
                                '<?php echo htmlspecialchars($row['User_Phone']); ?>', 
                                '<?php echo htmlspecialchars($row['status']); ?>')" 
                                class="btn-edit">Edit</button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                <button type="submit" class="btn-delete" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Edit User</h2>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="form-group">
                    <label for="edit_user_name">Name:</label>
                    <input type="text" id="edit_user_name" name="user_name" required>
                </div>

                <div class="form-group">
                    <label for="edit_user_email">Email:</label>
                    <input type="email" id="edit_user_email" name="user_email" required>
                </div>

                <div class="form-group">
                    <label for="edit_user_phone">Phone:</label>
                    <input type="tel" id="edit_user_phone" name="user_phone" required>
                </div>

                <div class="form-group">
                    <label for="edit_status">Status:</label>
                    <select id="edit_status" name="status" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Update User</button>
            </form>
        </div>
    </div>

    <script>
        var modal = document.getElementById("editModal");
        var span = document.getElementsByClassName("close")[0];

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        function editUser(userId, name, email, phone, status) {
            modal.style.display = "block";
            document.getElementById("edit_user_id").value = userId;
            document.getElementById("edit_user_name").value = name;
            document.getElementById("edit_user_email").value = email;
            document.getElementById("edit_user_phone").value = phone;
            document.getElementById("edit_status").value = status;
        }

        function viewUser(userId) {
            window.location.href = 'view_user.php?id=' + userId;
        }
    </script>
</body>
</html>