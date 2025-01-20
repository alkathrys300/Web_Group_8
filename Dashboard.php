<?php
include 'db_connection.php';

$location = isset($_POST['location']) ? $_POST['location'] : 'All';
$dateFilter = isset($_POST['dateFilter']) ? $_POST['dateFilter'] : 'All';


$query = "SELECT * FROM `order` WHERE 1=1";


if ($location !== 'All') {
    $query .= " AND location = '$location'";
}


if ($dateFilter !== 'All') {
    $currentDate = date('Y-m-d');
    if ($dateFilter == 'Last Month') {
        $query .= " AND order_date >= DATE_SUB('$currentDate', INTERVAL 1 MONTH)";
    } elseif ($dateFilter == 'Last 6 Months') {
        $query .= " AND order_date >= DATE_SUB('$currentDate', INTERVAL 6 MONTH)";
    } elseif ($dateFilter == 'Last Year') {
        $query .= " AND order_date >= DATE_SUB('$currentDate', INTERVAL 1 YEAR)";
    }
}

$result = $conn->query($query);

$orderData = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $orderData[] = $row;
    }
}


$totalOrdersQuery = "SELECT COUNT(*) as total FROM `order`";
$totalOrdersResult = $conn->query($totalOrdersQuery);
$totalOrders = $totalOrdersResult->fetch_assoc()['total'];


$lastMonthQuery = "SELECT COUNT(*) as total FROM `order` WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
$lastMonthResult = $conn->query($lastMonthQuery);
$lastMonthOrders = $lastMonthResult->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .dashboard {
            padding: 20px;
        }
        .stats-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .stat-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 48%;
        }
        .stat-box h2 {
            margin: 0;
            font-size: 2rem;
            color: #333;
        }
        .stat-box p {
            margin: 5px 0 0;
            color: #777;
        }
        .search-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .search-container form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-container button {
            padding: 8px 12px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .chart-container {
            display: flex;
            gap: 20px;
        }
        .chart-box {
            flex: 1;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .order-info {
            margin-top: 30px;
        }
        .order-info table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .order-info th, .order-info td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .order-info th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
<div class="dashboard">
    <div class="stats-container">
        <div class="stat-box">
            <h2><?php echo $totalOrders; ?></h2>
            <p>Total Orders</p>
        </div>
        <div class="stat-box">
            <h2><?php echo $lastMonthOrders; ?></h2>
            <p>Orders Last Month</p>
        </div>
    </div>

    <div class="search-container">
        <form method="POST" action="Dashboard.php">
            <select name="location">
                <option value="All">All</option>
                <option value="Pekan">Pekan</option>
                <option value="Gambang">Gambang</option>
            </select>
            <button type="submit" name="searchByLocation">Search by Location</button>

            <select name="dateFilter">
                <option value="All">All</option>
                <option value="Last Month">Last Month</option>
                <option value="Last 6 Months">Last 6 Months</option>
                <option value="Last Year">Last Year</option>
            </select>
            <button type="submit" name="searchByDate">Search by Date</button>
        </form>
    </div>

    <div class="chart-container">
        <div class="chart-box">
            <canvas id="revenueChart"></canvas>
        </div>
        <div class="chart-box">
            <canvas id="ordersChart"></canvas>
        </div>
    </div>

    <div class="order-info">
        <h3>Order Information</h3>
        <?php if (!empty($orderData)) { ?>
            <table>
                <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Location</th>
                    <th>Revenue</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orderData as $order) { ?>
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo $order['order_date']; ?></td>
                        <td><?php echo $order['location']; ?></td>
                        <td><?php echo $order['revenue']; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p>No orders found for the selected filters.</p>
        <?php } ?>
    </div>
</div>

<script>
    const revenueChartCtx = document.getElementById('revenueChart').getContext('2d');
    const ordersChartCtx = document.getElementById('ordersChart').getContext('2d');

    const revenueChart = new Chart(revenueChartCtx, {
        type: 'pie',
        data: {
            labels: ['Pekan', 'Gambang'],
            datasets: [{
                data: [
                    <?php
                    $pekanRevenue = $conn->query("SELECT SUM(revenue) as total FROM `order` WHERE location='Pekan'")->fetch_assoc()['total'] ?? 0;
                    $gambangRevenue = $conn->query("SELECT SUM(revenue) as total FROM `order` WHERE location='Gambang'")->fetch_assoc()['total'] ?? 0;
                    echo "$pekanRevenue, $gambangRevenue";
                    ?>
                ],
                backgroundColor: ['#007bff', '#28a745']
            }]
        }
    });

    const ordersChart = new Chart(ordersChartCtx, {
        type: 'line',
        data: {
            labels: ['Last Month', 'Last 6 Months', 'Last Year'],
            datasets: [{
                label: 'Orders',
                data: [
                    <?php
                    $lastMonthOrders = $conn->query("SELECT COUNT(*) as total FROM `order` WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)")->fetch_assoc()['total'] ?? 0;
                    $lastSixMonthsOrders = $conn->query("SELECT COUNT(*) as total FROM `order` WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)")->fetch_assoc()['total'] ?? 0;
                    $lastYearOrders = $conn->query("SELECT COUNT(*) as total FROM `order` WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)")->fetch_assoc()['total'] ?? 0;
                    echo "$lastMonthOrders, $lastSixMonthsOrders, $lastYearOrders";
                    ?>
                ],
                borderColor: '#007bff',
                fill: false
            }]
        }
    });
</script>
</body>
</html>
