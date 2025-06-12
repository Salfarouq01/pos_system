<?php
require_once('db.php');  // Include DB connection

$type = $_GET['type'] ?? 'daily';

$dateLabel = match ($type) {
    'daily' => 'DATE(o.created_at)',
    'weekly' => 'YEARWEEK(o.created_at)',
    'monthly' => 'DATE_FORMAT(o.created_at, "%Y-%m")',
    'annually' => 'YEAR(o.created_at)',
    default => 'DATE(o.created_at)',
};

$query = "
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
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="sales_report.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Period', 'Product', 'Total Quantity', 'Total Sales (TZS)']);

foreach ($results as $row) {
    fputcsv($output, [
        $row['period'],
        $row['product_name'],
        $row['total_quantity'],
        $row['total_sales']
    ]);
}

fclose($output);
exit;
