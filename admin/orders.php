<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: auth/login.php");
    exit;
}

// Include database connection
require_once "config/db.php";

// Handle status update
if(isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $update_sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $order_id);
    
    if($stmt->execute()) {
        $status_message = "Order status updated successfully!";
        $status_type = "success";
    } else {
        $status_message = "Error updating order status: " . $conn->error;
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
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search_condition = '';

if(!empty($search)) {
    // Use prepared statement for search to prevent SQL injection
    $search_param = "%$search%";
    
    // Build search condition to include customer name and other text fields
    $search_condition = " WHERE (o.order_id LIKE ? OR o.user_phone LIKE ? OR o.user_city LIKE ? OR o.user_address LIKE ? OR u.user_name LIKE ? OR u.user_email LIKE ? OR o.order_status LIKE ?)";
    
    // Get total records for pagination with user join for name search
    $count_sql = "SELECT COUNT(DISTINCT o.order_id) as total 
                 FROM orders o 
                 LEFT JOIN users u ON o.user_id = u.user_id";
    
    if(!empty($search)) {
        $count_sql .= $search_condition;
    }
    
    if(!empty($status_filter)) {
        if(empty($search_condition)) {
            $count_sql .= " WHERE o.order_status = ?";
        } else {
            $count_sql .= " AND o.order_status = ?";
        }
    }
    
    $count_stmt = $conn->prepare($count_sql);
    
    if(!empty($search) && !empty($status_filter)) {
        $count_stmt->bind_param("sssssss", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $status_filter);
    } else if(!empty($search)) {
        $count_stmt->bind_param("sssssss", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
    } else if(!empty($status_filter)) {
        $count_stmt->bind_param("s", $status_filter);
    }
    
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $total_records = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_records / $limit);
    $count_stmt->close();
    
    // Get orders with pagination and search, joining with users table for name search
    $sql = "SELECT o.*, COUNT(oi.item_id) as item_count, u.user_name 
            FROM orders o 
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN users u ON o.user_id = u.user_id";
    
    // Add search condition
    if(!empty($search)) {
        $sql .= $search_condition;
    }
    
    // Add status filter if present
    if(!empty($status_filter)) {
        if(empty($search_condition)) {
            $sql .= " WHERE o.order_status = ?";
        } else {
            $sql .= " AND o.order_status = ?";
        }
    }
    
    // Complete the query
    $sql .= " GROUP BY o.order_id ORDER BY o.order_date DESC LIMIT ?, ?";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters based on conditions
    if(!empty($search) && !empty($status_filter)) {
        $stmt->bind_param("ssssssssii", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $status_filter, $start, $limit);
    } else if(!empty($search)) {
        $stmt->bind_param("sssssssii", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $start, $limit);
    } else if(!empty($status_filter)) {
        $stmt->bind_param("sii", $status_filter, $start, $limit);
    } else {
        $stmt->bind_param("ii", $start, $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    // Status filter without search
    if(!empty($status_filter)) {
        $search_condition = " WHERE o.order_status = '$status_filter'";
    }
    
    // Get total records
    $count_sql = "SELECT COUNT(*) as total FROM orders o" . $search_condition;
    $count_result = $conn->query($count_sql);
    $total_records = $count_result->fetch_assoc()['total'];
    $total_pages = ceil($total_records / $limit);
    
    // Get orders with pagination, including user name
    $sql = "SELECT o.*, COUNT(oi.item_id) as item_count, u.user_name 
            FROM orders o 
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN users u ON o.user_id = u.user_id" . 
            $search_condition . 
            " GROUP BY o.order_id 
            ORDER BY o.order_date DESC 
            LIMIT $start, $limit";
    $result = $conn->query($sql);
}

// Include header
include "includes/header.php";

// Include sidebar
include "includes/sidebar.php";
?>

<div class="col-md-9 col-lg-10 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Orders Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
             
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
                <input type="text" name="search" class="form-control me-2" placeholder="Search orders by ID, customer, status, etc..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
        <div class="col-md-6">
            <form action="" method="GET" class="d-flex justify-content-end">
                <?php if(!empty($search)): ?>
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <?php endif; ?>
                <select name="status" class="form-select me-2" style="max-width: 200px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Pending" <?php echo ($status_filter == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Processing" <?php echo ($status_filter == 'Processing') ? 'selected' : ''; ?>>Processing</option>
                    <option value="Shipped" <?php echo ($status_filter == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                    <option value="Delivered" <?php echo ($status_filter == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                    <option value="Cancelled" <?php echo ($status_filter == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['order_id']; ?></td>
                                <td>
                                    <?php 
                                    if($row['user_id'] > 0) {
                                        if(isset($row['user_name']) && !empty($row['user_name'])) {
                                            echo $row['user_name'];
                                        } else {
                                            echo "User #" . $row['user_id'];
                                        }
                                    } else {
                                        echo "Guest";
                                    }
                                    ?>
                                </td>
                                <td><?php echo $row['item_count']; ?></td>
                                <td>$<?php echo number_format($row['order_cost'], 2); ?></td>
                                <td>
                                    <?php 
                                    $status_class = '';
                                    switch($row['order_status']) {
                                        case 'Delivered':
                                            $status_class = 'success';
                                            break;
                                        case 'Pending':
                                            $status_class = 'warning';
                                            break;
                                        case 'Processing':
                                            $status_class = 'info';
                                            break;
                                        case 'Shipped':
                                            $status_class = 'primary';
                                            break;
                                        case 'Cancelled':
                                            $status_class = 'danger';
                                            break;
                                        default:
                                            $status_class = 'secondary';
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>"><?php echo $row['order_status']; ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['order_date'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="order-details.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $row['order_id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['order_id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Status Update Modal -->
                                    <div class="modal fade" id="statusModal<?php echo $row['order_id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Order Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                                        <div class="mb-3">
                                                            <label for="new_status" class="form-label">Status</label>
                                                            <select name="new_status" class="form-select" required>
                                                                <option value="Pending" <?php echo ($row['order_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                                                <option value="Processing" <?php echo ($row['order_status'] == 'Processing') ? 'selected' : ''; ?>>Processing</option>
                                                                <option value="Shipped" <?php echo ($row['order_status'] == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                                                                <option value="Delivered" <?php echo ($row['order_status'] == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                                                                <option value="Cancelled" <?php echo ($row['order_status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="deleteModal<?php echo $row['order_id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    Are you sure you want to delete Order #<?php echo $row['order_id']; ?>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <a href="delete-order.php?id=<?php echo $row['order_id']; ?>" class="btn btn-danger">Delete</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No orders found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if(isset($total_pages) && $total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo (!empty($search)) ? '&search='.urlencode($search) : ''; ?><?php echo (!empty($status_filter)) ? '&status='.urlencode($status_filter) : ''; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo (!empty($search)) ? '&search='.urlencode($search) : ''; ?><?php echo (!empty($status_filter)) ? '&status='.urlencode($status_filter) : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo (!empty($search)) ? '&search='.urlencode($search) : ''; ?><?php echo (!empty($status_filter)) ? '&status='.urlencode($status_filter) : ''; ?>" aria-label="Next">
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

