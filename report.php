<?php
require 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

$reportType = $_GET['type'] ?? 'daily';

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

$sql = "
    SELECT 
        $dateLabel AS period,
        p.name AS product_name,
        SUM(oi.quantity) AS total_quantity,
        SUM(oi.subtotal) AS total_sales
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    GROUP BY period, product_name
    ORDER BY period DESC;
";

$stmt = $pdo->query($sql);
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
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>Period</th>
                <th>Product Name</th>
                <th>Total Quantity Sold</th>
                <th>Total Sales (TZS)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($report) > 0): ?>
                <?php foreach ($report as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['period'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['product_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['total_quantity'] ?? '0') ?></td>
                    <td><?= number_format($row['total_sales'] ?? 0, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">No sales data available.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <a href="export_report.php?type=<?= $reportType ?>&format=pdf" class="btn btn-danger">Download PDF</a>
    <a href="export_report.php?type=<?= $reportType ?>&format=csv" class="btn btn-success">Download CSV</a>
</div>
</body>
</html>
