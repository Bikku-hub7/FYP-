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

$old_brand = $_GET['brand'];

// Check if brand exists
$checkQuery = "SELECT COUNT(*) as count FROM products WHERE product_category = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $old_brand);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()['count'];

if($count == 0) {
    $_SESSION['error_msg'] = "Brand not found";
    header("Location: brands.php");
    exit();
}

// Handle form submission
if(isset($_POST['update_brand'])) {
    $new_brand = trim($_POST['brand_name']);
    
    // Validate input
    if(empty($new_brand)) {
        $errorMsg = "Brand name cannot be empty";
    } else if($new_brand != $old_brand) {
        // Check if new brand name already exists
        $checkQuery = "SELECT COUNT(*) as count FROM products WHERE product_category = ? AND product_category != ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ss", $new_brand, $old_brand);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        if($count > 0) {
            $errorMsg = "Brand '$new_brand' already exists";
        } else {
            // Update all products with this brand/category
            $updateSql = "UPDATE products SET product_category = ? WHERE product_category = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("ss", $new_brand, $old_brand);
            
            if($stmt->execute()) {
                $_SESSION['success_msg'] = "Brand updated from '$old_brand' to '$new_brand' successfully!";
                header("Location: brands.php");
                exit();
            } else {
                $errorMsg = "Error updating brand: " . $conn->error;
            }
        }
    } else {
        // No change in brand name
        $_SESSION['success_msg'] = "No changes made to brand name";
        header("Location: brands.php");
        exit();
    }
}

// Get product count for this brand
$countQuery = "SELECT COUNT(*) as count FROM products WHERE product_category = ?";
$stmt = $conn->prepare($countQuery);
$stmt->bind_param("s", $old_brand);
$stmt->execute();
$productCount = $stmt->get_result()->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Brand - Biku Rental</title>
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
                    <h1 class="h2">Edit Brand</h1>
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
                    <div class="card-header">
                        <h5 class="card-title mb-0">Edit Brand: <?php echo htmlspecialchars($old_brand); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle-fill"></i> 
                            This brand is currently used by <?php echo $productCount; ?> product(s). Changing the brand name will update all associated products.
                        </div>
                        
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="brand_name" class="form-label">Brand Name</label>
                                <input type="text" class="form-control" id="brand_name" name="brand_name" value="<?php echo htmlspecialchars($old_brand); ?>" required>
                            </div>
                            
                            <button type="submit" name="update_brand" class="btn btn-primary">Update Brand</button>
                        </form>
                    </div>
                </div>
                
                <!-- Products with this brand -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Products with this Brand</h5>
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
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $productsQuery = "SELECT * FROM products WHERE product_category = ? ORDER BY product_id DESC LIMIT 5";
                                    $stmt = $conn->prepare($productsQuery);
                                    $stmt->bind_param("s", $old_brand);
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
                                        <td>
                                            <a href="view_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No products found with this brand</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            
                            <?php if($productCount > 5): ?>
                            <div class="text-center mt-3">
                                <a href="products.php?category=<?php echo urlencode($old_brand); ?>" class="btn btn-outline-primary">
                                    View All <?php echo $productCount; ?> Products
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>