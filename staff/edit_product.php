<?php
session_start();
require_once 'config/db.php';

// Check if staff is logged in
if(!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

// Check if product ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];

// Get product details
$productQuery = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($productQuery);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$productResult = $stmt->get_result();

if($productResult->num_rows == 0) {
    header("Location: products.php");
    exit();
}

$product = $productResult->fetch_assoc();

// Get all categories for dropdown
$categories = $conn->query("SELECT DISTINCT product_category FROM products ORDER BY product_category");
$categoryList = array();
while($category = $categories->fetch_assoc()) {
    $categoryList[] = $category['product_category'];
}

// Handle form submission
if(isset($_POST['update_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $color = $_POST['color'];
    
    // Check if a new image is uploaded
    if($_FILES['image']['size'] > 0) {
        // Handle file upload
        $targetDir = "../uploads/";
        $fileName = basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        
        // Check if file is an actual image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            // Allow certain file formats
            $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
            if(in_array($fileType, $allowTypes)) {
                // Upload file to server
                if(move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
                    // Update product with new image
                    $updateSql = "UPDATE products SET product_name = ?, product_category = ?, product_description = ?, 
                                  product_image = ?, product_image2 = ?, product_image3 = ?, product_image4 = ?, 
                                  product_price = ?, product_color = ? WHERE product_id = ?";
                    $stmt = $conn->prepare($updateSql);
                    $stmt->bind_param("sssssssdsi", $name, $category, $description, $fileName, $fileName, $fileName, $fileName, $price, $color, $product_id);
                } else {
                    $errorMsg = "Error uploading file.";
                }
            } else {
                $errorMsg = "Only JPG, JPEG, PNG & GIF files are allowed.";
            }
        } else {
            $errorMsg = "Please upload a valid image file.";
        }
    } else {
        // Update product without changing the image
        $updateSql = "UPDATE products SET product_name = ?, product_category = ?, product_description = ?, 
                      product_price = ?, product_color = ? WHERE product_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("sssdsi", $name, $category, $description, $price, $color, $product_id);
    }
    
    // Execute the update query
    if(isset($stmt) && $stmt->execute()) {
        $successMsg = "Product updated successfully!";
        
        // Refresh product data
        $stmt = $conn->prepare($productQuery);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $productResult = $stmt->get_result();
        $product = $productResult->fetch_assoc();
    } else if(!isset($errorMsg)) {
        $errorMsg = "Error updating product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Biku Rental</title>
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
        .product-image-preview {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
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
                    <h1 class="h2">Edit Product</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="products.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Products
                        </a>
                    </div>
                </div>
                
                <?php if(isset($successMsg)): ?>
                    <div class="alert alert-success"><?php echo $successMsg; ?></div>
                <?php endif; ?>
                
                <?php if(isset($errorMsg)): ?>
                    <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Edit Product: <?php echo $product['product_name']; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $product['product_name']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="category" name="category" list="categoryList" value="<?php echo $product['product_category']; ?>" required>
                                    <datalist id="categoryList">
                                        <?php foreach($categoryList as $cat): ?>
                                            <option value="<?php echo $cat; ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required><?php echo $product['product_description']; ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo $product['product_price']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="color" class="form-label">Color</label>
                                    <input type="text" class="form-control" id="color" name="color" value="<?php echo $product['product_color']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="image" class="form-label">Product Image</label>
                                <div class="mb-2">
                                    <img src="assets/images/<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>" class="product-image-preview">
                                </div>
                                <input type="file" class="form-control" id="image" name="image">
                                <div class="form-text">Upload a new image only if you want to change the current one.</div>
                            </div>
                            
                            <button type="submit" name="update_product" class="btn btn-primary">Update Product</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>