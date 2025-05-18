<?php
session_start();
require_once 'config/db.php';

// Check if staff is logged in
if(!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

// Handle availability toggle
if(isset($_GET['toggle_availability']) && !empty($_GET['toggle_availability'])) {
    $product_id = $_GET['toggle_availability'];
    
    // Get current availability status
    $check_sql = "SELECT availability FROM products WHERE product_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $product_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $current_status = $check_result->fetch_assoc()['availability'];
    
    // Toggle availability
    $new_status = $current_status ? 0 : 1;
    $update_sql = "UPDATE products SET availability = ? WHERE product_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $new_status, $product_id);
    
    if($update_stmt->execute()) {
        $message = "Product availability updated successfully!";
        $message_type = "success";
    } else {
        $message = "Error updating availability: " . $conn->error;
        $message_type = "danger";
    }
    
    $update_stmt->close();
    
    // Redirect to maintain the current filters and page
    $redirect = "products.php?page=" . $page;
    if($categoryFilter) {
        $redirect .= "&category=" . urlencode($categoryFilter);
    }
    header("Location: $redirect");
    exit();
}

// Handle category filter
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
$whereClause = $categoryFilter ? "WHERE product_category = '$categoryFilter'" : "";

// Get all categories for filter
$categories = $conn->query("SELECT DISTINCT product_category FROM products ORDER BY product_category");

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1; // Ensure page is at least 1
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Get total records
$totalRecords = $conn->query("SELECT COUNT(*) as count FROM products $whereClause")->fetch_assoc()['count'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get products with pagination
$products = $conn->query("SELECT * FROM products $whereClause ORDER BY product_id DESC LIMIT $offset, $recordsPerPage");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Biku Rental</title>
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
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
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
                    <h1 class="h2">Manage Products</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="dropdown me-2">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo $categoryFilter ? "Category: $categoryFilter" : "Filter by Category"; ?>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                <li><a class="dropdown-item" href="products.php">All Categories</a></li>
                                <?php while($category = $categories->fetch_assoc()): ?>
                                <li><a class="dropdown-item" href="products.php?category=<?php echo urlencode($category['product_category']); ?>"><?php echo $category['product_category']; ?></a></li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                        <a href="add_product.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg"></i> Add New Product
                        </a>
                    </div>
                </div>
                
                <!-- Products Table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-table me-1"></i>
                        Products
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Color</th>
                                        <th>Availability</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($products->num_rows > 0): ?>
                                        <?php while($product = $products->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $product['product_id']; ?></td>
                                            <td>
                                                <img src="../uploads/<?php echo $product['product_image']; ?>" alt="<?php echo $product['product_name']; ?>" class="product-image">
                                            </td>
                                            <td><?php echo $product['product_name']; ?></td>
                                            <td><?php echo $product['product_category']; ?></td>
                                            <td>$<?php echo number_format($product['product_price'], 2); ?></td>
                                            <td><?php echo $product['product_color']; ?></td>
                                            <td>
                                                <a href="?toggle_availability=<?php echo $product['product_id']; ?>&page=<?php echo $page; ?><?php echo $categoryFilter ? '&category='.urlencode($categoryFilter) : ''; ?>" 
                                                   class="btn btn-sm <?php echo isset($product['availability']) && $product['availability'] ? 'btn-success' : 'btn-secondary'; ?>">
                                                    <?php echo isset($product['availability']) && $product['availability'] ? 'Available' : 'Unavailable'; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="edit_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                    <a href="view_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $product['product_id']; ?>">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal<?php echo $product['product_id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the product: <strong><?php echo $product['product_name']; ?></strong>?
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <a href="delete_product.php?id=<?php echo $product['product_id']; ?>" class="btn btn-danger">Delete</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No products found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($totalPages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo $categoryFilter ? "&category=$categoryFilter" : ""; ?>">Previous</a>
                                </li>
                                
                                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $categoryFilter ? "&category=$categoryFilter" : ""; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo $categoryFilter ? "&category=$categoryFilter" : ""; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>