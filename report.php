<?php
// session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header("Location: login.php");
//     exit;
// }

require 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$reportType = $_GET['type'] ?? 'daily'; // daily, weekly, monthly, annually

switch ($reportType) {
    case 'weekly':
        $groupBy = "YEARWEEK(created_at, 1)";
        $dateLabel = "YEARWEEK(created_at, 1)";
        break;
    case 'monthly':
        $groupBy = "YEAR(created_at), MONTH(created_at)";
        $dateLabel = "DATE_FORMAT(created_at, '%Y-%m')";
        break;
    case 'annually':
        $groupBy = "YEAR(created_at)";
        $dateLabel = "YEAR(created_at)";
        break;
    default:
        $groupBy = "DATE(created_at)";
        $dateLabel = "DATE(created_at)";
        break;
}

$stmt = $pdo->query("
   SELECT 
    DATE(o.created_at) as order_date, 
    SUM(o.total) as daily_total
FROM orders o
LEFT JOIN orders_item oi ON o.id = oi.order_id
GROUP BY order_date
ORDER BY order_date DESC;

");
$report = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Report (<?= ucfirst($reportType) ?>)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Sales Report - <?= ucfirst($reportType) ?></h2>
    <div class="mb-3">
        <a href="?type=daily" class="btn btn-outline-primary <?= $reportType=='daily'?'active':'' ?>">Daily</a>
        <a href="?type=weekly" class="btn btn-outline-primary <?= $reportType=='weekly'?'active':'' ?>">Weekly</a>
        <a href="?type=monthly" class="btn btn-outline-primary <?= $reportType=='monthly'?'active':'' ?>">Monthly</a>
        <a href="?type=annually" class="btn btn-outline-primary <?= $reportType=='annually'?'active':'' ?>">Annually</a>
        <a href="admin.php" class="btn btn-secondary">Back to Admin</a>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Period</th><th>Product Name</th><th>Total Quantity Sold</th><th>Total Sales (TZS)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['period']) ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= htmlspecialchars($row['total_quantity']) ?></td>
                <td><?= number_format($row['total_sales'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="export_report.php?type=<?= $reportType ?>&format=pdf" class="btn btn-danger">Download PDF</a>
    <a href="export_report.php?type=<?= $reportType ?>&format=csv" class="btn btn-success">Download CSV</a>
</div>
</body>
</html>
