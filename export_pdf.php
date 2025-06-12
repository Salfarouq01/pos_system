<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('vendor/autoload.php');
require_once('db.php');  // Include DB connection

$type = $_GET['type'] ?? 'daily';

// Use the same query logic as admin_dashboard.php
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

use TCPDF;

$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 12);

$html = "<h2>Sales Report ($type)</h2>";
$html .= "<table border='1' cellpadding='4'>
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Product</th>
                    <th>Total Quantity</th>
                    <th>Total Sales (TZS)</th>
                </tr>
            </thead>
            <tbody>";

foreach ($results as $row) {
    $html .= "<tr>
                <td>{$row['period']}</td>
                <td>{$row['product_name']}</td>
                <td>{$row['total_quantity']}</td>
                <td>{$row['total_sales']}</td>
              </tr>";
}

$html .= "</tbody></table>";

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output("sales_report.pdf", 'D');  // D = Download
exit;
