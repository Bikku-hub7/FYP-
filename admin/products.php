<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: auth/login.php");
    exit;
}

// Include database connection
require_once "config/db.php";

// Handle product deletion
if(isset($_GET['delete']) && !empty($_GET['delete'])) {
    $product_id = $_GET['delete'];
    
    // Delete product
    $delete_sql = "DELETE FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $product_id);
    
    if($stmt->execute()) {
        $status_message = "Product deleted successfully!";
        $status_type = "success";
    } else {
        $status_message = "Error deleting product: " . $conn->error;
        $status_type = "danger";
    }
    
    $stmt->close();
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if(!empty($search)) {
    // Use prepared statement for search to prevent SQL injection
    $search_param = "%$search%";
    $search_condition = " WHERE (product_name LIKE ? OR product_category LIKE ? OR product_description LIKE ?)";
    
    // Category filter
    $category_filter = isset($_GET['category']) ? $_GET['category'] : '';
    if(!empty($category_filter)) {
        $search_condition .= " AND product_category = ?";
    }
    
    // Get total records
    $count_sql = "SELECT COUNT(*) as total FROM products" . $search_condition;
    $count_stmt = $conn->prepare($count_sql);
    
    if(!empty($category_filter)) {
        $count_stmt->bind_param("ssss", $search_param, $search_param, $search_param, $category_filter);
    } else {
        $count_stmt->bind_param("sss", $search_param, $search_param, $search_param);
    }
    
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_records = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_records / $limit);
    $count_stmt->close();
    
    // Get products with pagination
    $sql = "SELECT * FROM products" . $search_condition . " ORDER BY product_id DESC LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    
    if(!empty($category_filter)) {
        $stmt->bind_param("ssssii", $search_param, $search_param, $search_param, $category_filter, $start, $limit);
    } else {
        $stmt->bind_param("ssii", $search_param, $search_param, $search_param, $start, $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Only category filter
    if(!empty($category_filter)) {
        $search_condition = " WHERE product_category = ?";
        
        // Get total records
        $count_sql = "SELECT COUNT(*) as total FROM products" . $search_condition;
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param("s", $category_filter);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total_records = $count_result->fetch_assoc()['total'];
        $total_pages = ceil($total_records / $limit);
        $count_stmt->close();
        
        // Get products with pagination
        $sql = "SELECT * FROM products" . $search_condition . " ORDER BY product_id DESC LIMIT ?, ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $category_filter, $start, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // No search or category filter
        // Get total records
        $count_sql = "SELECT COUNT(*) as total FROM products";
        $count_result = $conn->query($count_sql);
        $total_records = $count_result->fetch_assoc()['total'];
        $total_pages = ceil($total_records / $limit);
        
        // Get products with pagination
        $sql = "SELECT * FROM products ORDER BY product_id DESC LIMIT $start, $limit";
        $result = $conn->query($sql);
    }
}

// Get all categories for filter
$categories_sql = "SELECT DISTINCT product_category FROM products ORDER BY product_category";
$categories_result = $conn->query($categories_sql);

// Include header
include "includes/header.php";

// Include sidebar
include "includes/sidebar.php";
?>

<div class="col-md-9 col-lg-10 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Products Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="add-product.php" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Product
            </a>
        </div>
    </div>

    <?php if(isset($status_message)): ?>
    <div class="alert alert-<?php echo $status_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $status_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Search and Filter -->
    <div class="row mb-3">
        <div class="col-md-6">
            <form action="" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search products..." value="<?php echo $search; ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
        <div class="col-md-6">
            <form action="" method="GET" class="d-flex justify-content-end">
                <select name="category" class="form-select me-2" style="max-width: 200px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php while($category = $categories_result->fetch_assoc()): ?>
                    <option value="<?php echo $category['product_category']; ?>" <?php echo ($category_filter == $category['product_category']) ? 'selected' : ''; ?>>
                        <?php echo $category['product_category']; ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Color</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['product_id']; ?></td>
                                <td>
                                    <img src="../uploads/<?php echo $row['product_image']; ?>" alt="<?php echo $row['product_name']; ?>" width="50">
                                </td>
                                <td><?php echo $row['product_name']; ?></td>
                                <td><?php echo $row['product_category']; ?></td>
                                <td>$<?php echo number_format($row['product_price'], 2); ?></td>
                                <td><?php echo $row['product_color']; ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit-product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['product_id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal<?php echo $row['product_id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete "<?php echo $row['product_name']; ?>"?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <a href="?delete=<?php echo $row['product_id']; ?>" class="btn btn-danger">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No products found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo (!empty($search)) ? '&search='.$search : ''; ?><?php echo (!empty($category_filter)) ? '&category='.$category_filter : ''; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo (!empty($search)) ? '&search='.$search : ''; ?><?php echo (!empty($category_filter)) ? '&category='.$category_filter : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo (!empty($search)) ? '&search='.$search : ''; ?><?php echo (!empty($category_filter)) ? '&category='.$category_filter : ''; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include "includes/footer.php";

// Close connection
$conn->close();
?>
