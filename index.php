<?php
include 'db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch products
$stmt = $pdo->query("SELECT * FROM products ORDER BY name");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .product-list {
            max-height: 80vh;
            overflow-y: auto;
        }
        .cart {
            max-height: 80vh;
            overflow-y: auto;
            border-left: 1px solid #ccc;
            padding-left: 15px;
        }
    </style>
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

<div class="container">
    <h1 class="text-center mb-4">Point of Sale</h1>
    <div class="row">
        <div class="col-md-7 product-list">
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <?php if ($product['image_path']): ?>
                                <img src="images/<?php echo htmlspecialchars($product['image_path']); ?>" class="card-img-top" alt="Product Image">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/150" class="card-img-top" alt="No Image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                                <p><strong>TZS <?php echo number_format($product['price_tzs'], 2); ?></strong></p>
                                <button class="btn btn-primary add-to-cart" 
                                        data-id="<?php echo $product['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($product['name']); ?>" 
                                        data-price="<?php echo $product['price_tzs']; ?>">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-5 cart">
            <h4>Cart</h4>
            <ul id="cart" class="list-group mb-3"></ul>
            <h5>Total: TZS <span id="total">0.00</span></h5>
            <button id="checkout" class="btn btn-success">Checkout</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="main.js"></script>
</body>
</html>
