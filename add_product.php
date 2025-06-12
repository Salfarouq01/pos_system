<?php
include 'db.php'; // Make sure this file contains your PDO connection

// Enable error reporting during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price_tzs = $_POST['price_tzs'] ?? '';
    $stock_quantity = $_POST['stock_quantity'] ?? '';
    $image_path = null;

    // Basic validation
    $errors = [];
    if (empty($name)) {
        $errors[] = "Product name is required.";
    }
    if (empty($price_tzs) || !is_numeric($price_tzs)) {
        $errors[] = "Valid price is required.";
    }
    if (empty($stock_quantity) || !is_numeric($stock_quantity)) {
        $errors[] = "Valid stock quantity is required.";
    }

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'images/';
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('product_', true) . '.' . $ext;
        $uploadFile = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $errors[] = "Failed to upload the image.";
        } else {
            $image_path = $fileName;
        }
    }

    // Insert into the database if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products (name, description, price_tzs, stock_quantity, image_path)
                VALUES (:name, :description, :price_tzs, :stock_quantity, :image_path)
            ");
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price_tzs' => $price_tzs,
                ':stock_quantity' => $stock_quantity,
                ':image_path' => $image_path
            ]);

            $success = "Product added successfully!";
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Product Management</h1>

    <!-- Add Product Button -->
    <div class="mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus"></i> Add Product
        </button>
        <a href="index.php" class="btn btn-secondary">Back to POS</a>
    </div>

    <!-- Display Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form action="add_product.php" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
              <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Product Name *</label>
                    <input type="text" name="name" class="form-control" id="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" class="form-control" id="description"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="price_tzs" class="form-label">Price (TZS) *</label>
                    <input type="number" step="0.01" name="price_tzs" class="form-control" id="price_tzs" required value="<?php echo htmlspecialchars($_POST['price_tzs'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="stock_quantity" class="form-label">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" class="form-control" id="stock_quantity" required value="<?php echo htmlspecialchars($_POST['stock_quantity'] ?? ''); ?>">
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Product Image (optional)</label>
                    <input type="file" name="image" class="form-control" id="image" accept="image/*">
                </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Add Product</button>
            </div>
          </form>
        </div>
      </div>
    </div>
</div>

<!-- Optional: Automatically open modal on error -->
<?php if (!empty($errors)): ?>
<script>
    const addProductModal = new bootstrap.Modal(document.getElementById('addProductModal'));
    addProductModal.show();
</script>
<?php endif; ?>

</body>
</html>
