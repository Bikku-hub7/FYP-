<?php
session_start();
require_once 'config/db.php';

// Check if staff is logged in
if(!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

// Check if brand name is provided
if(!isset($_GET['brand']) || empty($_GET['brand'])) {
    header("Location: brands.php");
    exit();
}

$brand = $_GET['brand'];

// Check if brand exists and get product count
$checkQuery = "SELECT COUNT(*) as count FROM products WHERE product_category = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $brand);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'];

if($count == 0) {
    $_SESSION['error_msg'] = "Brand not found";
    header("Location: brands.php");
    exit();
}

// Handle form submission for confirmation
if(isset($_POST['confirm_delete'])) {
    // Delete all products with this brand/category
    $deleteSql = "DELETE FROM products WHERE product_category = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("s", $brand);
    
    if($stmt->execute()) {
        $_SESSION['success_msg'] = "Brand '$brand' and all associated products deleted successfully!";
        header("Location: brands.php");
        exit();
    } else {
        $errorMsg = "Error deleting brand: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Brand - Biku Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
        }
        .sidebar .nav-link:hover {
            color: white;
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Delete Brand</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="brands.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Brands
                        </a>
                    </div>
                </div>
                
                <?php if(isset($errorMsg)): ?>
                    <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">Confirm Deletion</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i> 
                            <strong>Warning:</strong> You are about to delete the brand <strong><?php echo htmlspecialchars($brand); ?></strong> and all associated products.
                        </div>
                        
                        <p>This action will delete:</p>
                        <ul>
                            <li>The brand "<?php echo htmlspecialchars($brand); ?>"</li>
                            <li><?php echo $count; ?> product(s) associated with this brand</li>
                        </ul>
                        
                        <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                        
                        <form method="post" action="">
                            <div class="d-flex justify-content-between mt-4">
                                <a href="brands.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" name="confirm_delete" class="btn btn-danger">
                                    <i class="bi bi-trash"></i> Confirm Delete
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Products to be deleted -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Products to be Deleted</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $productsQuery = "SELECT * FROM products WHERE product_category = ? ORDER BY product_id DESC LIMIT 10";
                                    $stmt = $conn->prepare($productsQuery);
                                    $stmt->bind_param("s", $brand);
                                    $stmt->execute();
                                    $products = $stmt->get_result();
                                    
                                    if($products->num_rows > 0):
                                        while($product = $products->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo $product['product_id']; ?></td>
                                        <td>
                                            <img src="assets/images/<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>" width="50" height="50" class="rounded">
                                        </td>
                                        <td><?php echo $product['product_name']; ?></td>
                                        <td>$<?php echo number_format($product['product_price'], 2); ?></td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                        if($count > 10):
                                    ?>
                                    <tr>
                                        <td colspan="4" class="text-center">... and <?php echo $count - 10; ?> more product(s)</td>
                                    </tr>
                                    <?php 
                                        endif;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No products found with this brand</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>