<?php
session_start();
include('db.php');

// Kill session function
function kill_session() {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

// Handle logout action
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    kill_session();
    header("Location: login.php");
    exit();
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Retrieve the logged-in user's ID

// Initialize variables
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchType = isset($_GET['search_type']) ? trim($_GET['search_type']) : 'order_number';
$totalOrders = 0;
$totalRevenue = 0.00;
$recentOrders = [];
$chartData = [];

try {
    // Base WHERE clause for user_id
    $searchWhere = "WHERE `order`.user_id = ?";
    $searchParams = [$user_id];
    $searchTypes = "s";

    if (!empty($search)) {
        switch ($searchType) {
            case 'order_id':
                $searchWhere .= " AND `order`.order_id LIKE ?";
                $searchParams[] = "%$search%";
                $searchTypes .= "s";
                break;
            case 'price':
                $searchWhere .= " AND `order`.Order_Grand_Total = ?";
                $searchParams[] = $search;
                $searchTypes .= "d";
                break;
            default: // 'order_number'
                $searchWhere .= " AND `order`.order_number LIKE ?";
                $searchParams[] = "%$search%";
                $searchTypes .= "s";
                break;
        }
    }

    // Total orders query with JOIN
    $totalOrdersQuery = "
        SELECT COUNT(*) as total_orders
        FROM `order`
        INNER JOIN `user` ON `order`.user_id = `user`.user_id
        $searchWhere
    ";
    $stmt = $conn->prepare($totalOrdersQuery);
    if ($stmt === false) {
        die("Error preparing statement (totalOrdersQuery): " . $conn->error);
    }
    $stmt->bind_param($searchTypes, ...$searchParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalOrders = $result->fetch_assoc()['total_orders'] ?? 0;
    $stmt->close();

    // Total revenue query with JOIN
    $totalRevenueQuery = "
        SELECT SUM(`order`.Order_Grand_Total) as total_revenue
        FROM `order`
        INNER JOIN `user` ON `order`.user_id = `user`.user_id
        $searchWhere
    ";
    $stmt = $conn->prepare($totalRevenueQuery);
    if ($stmt === false) {
        die("Error preparing statement (totalRevenueQuery): " . $conn->error);
    }
    $stmt->bind_param($searchTypes, ...$searchParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $totalRevenue = $result->fetch_assoc()['total_revenue'] ?? 0.00;
    $stmt->close();

    // Recent orders query with JOIN
$recentOrdersQuery = "
SELECT 
    `order`.order_id,
    `order`.order_number,
    `order`.order_date,
    `order`.Order_Grand_Total,
    `membershipcard`.Card_Number,
    `membershipcard`.card_status
FROM `order`
INNER JOIN `user` ON `order`.user_id = `user`.user_id
LEFT JOIN `membershipcard` ON `user`.user_id = `membershipcard`.user_id
$searchWhere
ORDER BY `order`.order_date DESC
";
$stmt = $conn->prepare($recentOrdersQuery);
if ($stmt === false) {
die("Error preparing statement (recentOrdersQuery): " . $conn->error);
}
$stmt->bind_param($searchTypes, ...$searchParams);
$stmt->execute();
$recentOrdersResult = $stmt->get_result();
while ($row = $recentOrdersResult->fetch_assoc()) {
$recentOrders[] = $row;
}
$stmt->close();

    // Monthly data for charts with JOIN
    $chartDataQuery = "
        SELECT 
            DATE_FORMAT(`order`.order_date, '%Y-%m') as month,
            COUNT(*) as order_count,
            SUM(`order`.Order_Grand_Total) as monthly_revenue
        FROM `order`
        INNER JOIN `user` ON `order`.user_id = `user`.user_id
        WHERE `order`.user_id = ?
        GROUP BY DATE_FORMAT(`order`.order_date, '%Y-%m')
        ORDER BY month DESC
        LIMIT 6
    ";
    $stmt = $conn->prepare($chartDataQuery);
    if ($stmt === false) {
        die("Error preparing statement (chartDataQuery): " . $conn->error);
    }
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $chartDataResult = $stmt->get_result();
    while ($row = $chartDataResult->fetch_assoc()) {
        $chartData[] = $row;
    }
    $stmt->close();

} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    echo "An error occurred while fetching data. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RapidPrint User Dashboard</title>
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-light">
<header class="bg-dark text-white text-center py-3">
    <h1>RapidPrint User Dashboard</h1>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="user_management.php">User Management</a></li>
                <li class="nav-item"><a class="nav-link" href="Student.php">Student Home</a></li>
                <li class="nav-item"><a class="nav-link" href="admin_approval.php">Admin Approval</a></li>
                <li class="nav-item"><a class="nav-link text-danger" href="?logout=true">Logout</a></li>
            </ul>
        </div>
    </nav>
</header>


    <main class="container mt-4">
        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-md-6 offset-md-3">
                <form class="d-flex" method="GET">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search by order number, ID, or price" value="<?php echo htmlspecialchars($search); ?>">
                    <select name="search_type" class="form-select me-2">
                        <option value="order_number" <?php echo $searchType == 'order_number' ? 'selected' : ''; ?>>Order Number</option>
                        <option value="order_id" <?php echo $searchType == 'order_id' ? 'selected' : ''; ?>>Order ID</option>
                        <option value="price" <?php echo $searchType == 'price' ? 'selected' : ''; ?>>Price</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row text-center mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white shadow">
                    <div class="card-body">
                        <h4>Total Orders</h4>
                        <h2><?php echo $totalOrders; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white shadow">
                    <div class="card-body">
                        <h4>Total Revenue</h4>
                        <h2>RM <?php echo number_format($totalRevenue, 2); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white shadow">
                    <div class="card-body">
                        <h4>Average Order Value</h4>
                        <h2>RM <?php echo $totalOrders > 0 ? number_format($totalRevenue / $totalOrders, 2) : '0.00'; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Monthly Revenue</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Monthly Orders</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="orderChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Average Order Value</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="averageOrderChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="card">
            <div class="card-header">
                <h4>Recent Orders</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Order Number</th>
                                <th>Date</th>
                                <th>Grand Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                <td><?php echo htmlspecialchars($order['order_date']); ?></td>
                                <td>RM <?php echo number_format($order['Order_Grand_Total'], 2); ?></td>
                                <td><?php echo htmlspecialchars($order['card_status']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white text-center py-3 mt-4">
        <p>&copy; 2025 RapidPrint</p>
    </footer>

    <script>
        // Chart Data
        const chartData = <?php echo json_encode($chartData); ?>;

        // Revenue Chart
        new Chart(document.getElementById('revenueChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: chartData.map(data => data.month),
                datasets: [{
                    label: 'Monthly Revenue (RM)',
                    data: chartData.map(data => data.monthly_revenue),
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Order Count Chart
        new Chart(document.getElementById('orderChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: chartData.map(data => data.month),
                datasets: [{
                    label: 'Number of Orders',
                    data: chartData.map(data => data.order_count),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Average Order Value Chart
        new Chart(document.getElementById('averageOrderChart').getContext('2d'), {
            type: 'bar', // Updated to bar chart
            data: {
                labels: ['Average Order Value'],
                datasets: [{
                    label: 'Average Order Value (RM)',
                    data: [<?php echo $totalOrders > 0 ? number_format($totalRevenue / $totalOrders, 2) : '0.00'; ?>],
                    backgroundColor: 'rgba(255, 193, 7, 0.8)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Value (RM)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Metric'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'RM ' + context.raw;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
