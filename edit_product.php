<?php
// session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header("Location: login.php");
//     exit;
// }

require 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "Product not found.";
        exit;
    }
} else {
    header("Location: admin.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price_tzs = $_POST['price_tzs'];
    $stock_quantity = $_POST['stock_quantity'];

    $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price_tzs = ?, stock_quantity = ? WHERE id = ?");
    if ($stmt->execute([$name, $description, $price_tzs, $stock_quantity, $id])) {
        header("Location: admin.php?msg=Product+updated+successfully");
    } else {
        echo "Failed to update product.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Edit Product</h2>
    <form method="post">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control" required><?= htmlspecialchars($product['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label>Price (TZS)</label>
            <input type="number" name="price_tzs" value="<?= htmlspecialchars($product['price_tzs']) ?>" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Stock Quantity</label>
            <input type="number" name="stock_quantity" value="<?= htmlspecialchars($product['stock_quantity']) ?>" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Update Product</button>
        <a href="admin.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
