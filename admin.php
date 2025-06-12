<?php
// session_start();
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
//     header("Location: login.php");
//     exit;
// }

require 'db.php';
$stmt = $pdo->query("SELECT * FROM products ORDER BY name");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-3">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">POS System</a>
    <?php if (isset($_SESSION['user_id'])): ?>
    <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    <?php endif; ?>
  </div>
</nav>
<div class="container mt-5">
    <h2>Admin Panel</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Back to POS</a>
    <h4>Products</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th><th>Description</th><th>Price</th><th>Stock</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['description']) ?></td>
                <td><?= number_format($product['price_tzs'], 2) ?></td>
                <td><?= $product['stock_quantity'] ?></td>
                <td>
                    <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="delete_product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="add_product.php" class="btn btn-primary">Add New Product</a>
</div>
</body>
</html>
