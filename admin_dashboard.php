<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require 'db.php';

// Get report type
$reportType = $_GET['type'] ?? 'daily'; // daily, weekly, monthly, annually

// Determine groupBy and dateLabel
switch ($reportType) {
    case 'weekly':
        $groupBy = "YEARWEEK(o.created_at, 1)";
        $dateLabel = "YEARWEEK(o.created_at, 1)";
        break;
    case 'monthly':
        $groupBy = "YEAR(o.created_at), MONTH(o.created_at)";
        $dateLabel = "DATE_FORMAT(o.created_at, '%Y-%m')";
        break;
    case 'annually':
        $groupBy = "YEAR(o.created_at)";
        $dateLabel = "YEAR(o.created_at)";
        break;
    default:
        $groupBy = "DATE(o.created_at)";
        $dateLabel = "DATE(o.created_at)";
        break;
}

// Fetch report data
$stmt = $pdo->prepare("
    SELECT 
        $dateLabel as period,
        p.name as product_name,
        SUM(oi.quantity) as total_quantity,
        SUM(oi.price_tzs * oi.quantity) as total_sales
    FROM orders o
    JOIN orders_item oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    GROUP BY period, p.name
    ORDER BY period DESC;
");
$stmt->execute();
$report = $stmt->fetchAll(PDO::FETCH_ASSOC);

$productData = [];
foreach ($report as $row) {
    $productName = $row['product_name'];
    $period = $row['period'];
    $totalQuantity = (int)$row['total_quantity'];
    $totalSales = (float)$row['total_sales'];

    if (!isset($productData[$productName])) {
        $productData[$productName] = [
            'periods' => [],
            'quantities' => [],
            'sales' => 0
        ];
    }

    $productData[$productName]['periods'][] = $period;
    $productData[$productName]['quantities'][] = $totalQuantity;
    $productData[$productName]['sales'] += $totalSales;
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report (<?= ucfirst($reportType) ?>)</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 130vh;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            padding: 15px 20px;
            transition: background 0.3s;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
        }
        .content {
            padding: 20px;
            flex-grow: 1;
        }
    </style>
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="#"><i class="fas fa-users"></i> Manage Users</a>
        <a href="products.php"><i class="fas fa-box"></i> Products</a>
        <a href="#"><i class="fas fa-cash-register"></i> Sales</a>
        <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Reports</a>
        <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>Sales Report - <?= ucfirst($reportType) ?></h2>

    <h3 align="center">Product Statistics</h3>
    <div class="d-flex flex-wrap justify-content-between mb-4">
<div class="mb-4">
    <canvas id="frequencyPolygonChart" width="360" height="300"></canvas>
</div>
<div class="mb-4">
    <canvas id="histogramChart" width="360" height="300"></canvas>
</div>
<div class="mb-4">
    <canvas id="pieChart" width="300" height="200"></canvas>
</div>
    </div>

        <div class="mb-3">
            <a href="?type=daily" class="btn btn-outline-primary <?= $reportType=='daily'?'active':'' ?>">Daily</a>
            <a href="?type=weekly" class="btn btn-outline-primary <?= $reportType=='weekly'?'active':'' ?>">Weekly</a>
            <a href="?type=monthly" class="btn btn-outline-primary <?= $reportType=='monthly'?'active':'' ?>">Monthly</a>
            <a href="?type=annually" class="btn btn-outline-primary <?= $reportType=='annually'?'active':'' ?>">Annually</a>
            <a href="admin_dashboard.php" class="btn btn-secondary">Back to Admin</a>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Product Name</th>
                    <th>Total Quantity Sold</th>
                    <th>Total Sales (TZS)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($report)): ?>
                    <?php foreach ($report as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['period']) ?></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= htmlspecialchars($row['total_quantity']) ?></td>
                            <td><?= number_format($row['total_sales'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No data available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <a href="export_pdf.php?type=<?= $reportType ?>&format=pdf" class="btn btn-danger">Download PDF</a>
        <a href="export_csv.php?type=<?= $reportType ?>&format=csv" class="btn btn-success">Download CSV</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Prepare data from PHP
    const productData = <?php echo json_encode($productData); ?>;

    // Frequency Polygon: Quantity over periods
    const freqLabels = [...new Set(Object.values(productData).flatMap(p => p.periods))].sort();
    const freqDatasets = Object.keys(productData).map(product => {
        const quantities = freqLabels.map(label => {
            const index = productData[product].periods.indexOf(label);
            return index !== -1 ? productData[product].quantities[index] : 0;
        });
        return {
            label: product,
            data: quantities,
            fill: false,
            borderColor: getRandomColor(),
            tension: 0.3
        };
    });

    const freqCtx = document.getElementById('frequencyPolygonChart').getContext('2d');
    new Chart(freqCtx, {
        type: 'line',
        data: {
            labels: freqLabels,
            datasets: freqDatasets
        },
        options: {
            plugins: { title: { display: true, text: 'Frequency Polygon (Product Quantities Over Time)' } },
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Histogram: Total Quantity by Product
    const histLabels = Object.keys(productData);
    const histData = histLabels.map(product => productData[product].quantities.reduce((a, b) => a + b, 0));

    const histCtx = document.getElementById('histogramChart').getContext('2d');
    new Chart(histCtx, {
        type: 'bar',
        data: {
            labels: histLabels,
            datasets: [{
                label: 'Total Quantity Sold',
                data: histData,
                backgroundColor: histLabels.map(() => getRandomColor())
            }]
        },
        options: {
            plugins: { title: { display: true, text: 'Histogram (Total Quantity Sold per Product)' } },
            responsive: true,
            scales: { y: { beginAtZero: true } }
        }
    });

    // Pie Chart: Total Sales by Product
    const pieLabels = Object.keys(productData);
    const pieData = pieLabels.map(product => productData[product].sales);

    const pieCtx = document.getElementById('pieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: pieLabels,
            datasets: [{
                label: 'Total Sales (TZS)',
                data: pieData,
                backgroundColor: pieLabels.map(() => getRandomColor())
            }]
        },
        options: {
            plugins: { title: { display: true, text: 'Pie Chart (Product Sales Distribution)' } },
            responsive: true
        }
    });

    // Helper: Random Color Generator
    function getRandomColor() {
        const r = Math.floor(Math.random() * 200);
        const g = Math.floor(Math.random() * 200);
        const b = Math.floor(Math.random() * 200);
        return `rgb(${r}, ${g}, ${b})`;
    }
</script>

</body>
</html>
