<?php
session_start();
require_once 'config/db.php';

// Check if staff is logged in
if(!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

// Handle search query
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Build WHERE clause
$whereClause = "";
$params = [];
$types = "";

if($searchQuery) {
    $whereClause = "WHERE product_category LIKE ?";
    $searchParam = "%$searchQuery%";
    $params = [$searchParam];
    $types = "s";
}

// Get all unique brands (categories)
$brandsQuery = "SELECT DISTINCT product_category, 
                COUNT(product_id) as product_count 
                FROM products 
                $whereClause 
                GROUP BY product_category 
                ORDER BY product_category";

if(!empty($params)) {
    $stmt = $conn->prepare($brandsQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $brands = $stmt->get_result();
} else {
    $brands = $conn->query($brandsQuery);
}

// Check for success or error messages
$successMsg = isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : '';
$errorMsg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : '';

// Clear session messages
unset($_SESSION['success_msg']);
unset($_SESSION['error_msg']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Brands - Biku Rental</title>
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
        .brand-card {
            transition: transform 0.2s;
        }
        .brand-card:hover {
            transform: translateY(-5px);
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
                    <h1 class="h2">Manage Brands</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="add_brand.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> Add New Brand
                        </a>
                    </div>
                </div>
                
                <?php if($successMsg): ?>
                    <div class="alert alert-success"><?php echo $successMsg; ?></div>
                <?php endif; ?>
                
                <?php if($errorMsg): ?>
                    <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
                <?php endif; ?>
                
                <!-- Search Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form action="" method="get" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search brands..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            <?php if($searchQuery): ?>
                                <div class="col-md-6">
                                    <a href="brands.php" class="btn btn-outline-secondary">Clear Search</a>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Brands Grid -->
                <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
                    <?php if($brands->num_rows > 0): ?>
                        <?php while($brand = $brands->fetch_assoc()): ?>
                            <div class="col">
                                <div class="card h-100 brand-card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $brand['product_category']; ?></h5>
                                        <p class="card-text">
                                            <span class="badge bg-info"><?php echo $brand['product_count']; ?> products</span>
                                        </p>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0">
                                        <div class="d-flex justify-content-between">
                                            <a href="products.php?category=<?php echo urlencode($brand['product_category']); ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View Products
                                            </a>
                                            <a href="edit_brand.php?brand=<?php echo urlencode($brand['product_category']); ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteBrandModal<?php echo str_replace(' ', '_', $brand['product_category']); ?>">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Delete Brand Modal -->
                                <div class="modal fade" id="deleteBrandModal<?php echo str_replace(' ', '_', $brand['product_category']); ?>" tabindex="-1" aria-labelledby="deleteBrandModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteBrandModalLabel">Confirm Delete</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to delete the brand: <strong><?php echo $brand['product_category']; ?></strong>?</p>
                                                <p class="text-danger">
                                                    <i class="bi bi-exclamation-triangle-fill"></i> 
                                                    Warning: This will affect <?php echo $brand['product_count']; ?> products in this category.
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <a href="delete_brand.php?brand=<?php echo urlencode($brand['product_category']); ?>" class="btn btn-danger">Delete Brand</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                No brands found. <a href="add_brand.php" class="alert-link">Add a new brand</a>.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>