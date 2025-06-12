<?php
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || count($data) === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'No items in the cart.']);
    exit;
}

session_start();
$user_id = 1; // For demonstration (hardcoded admin user)

try {
    $pdo->beginTransaction();

    $total = 0;
    foreach ($data as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
    $stmt->execute([$user_id, $total]);
    $order_id = $pdo->lastInsertId();

    $item_stmt = $pdo->prepare("INSERT INTO orders_item (order_id, product_id, quantity, price_tzs, subtotal) VALUES (?, ?, ?, ?, ?)");
    $stock_stmt = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");

    foreach ($data as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $item_stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price'], $subtotal]);
        $stock_stmt->execute([$item['quantity'], $item['id']]);
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Order failed: ' . $e->getMessage()]);
}
?>
