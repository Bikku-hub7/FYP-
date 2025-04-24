<?php
session_start();
require_once 'config/db.php';

// Check if staff is logged in
if(!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

// Handle status filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$whereClause = $statusFilter ? "WHERE order_status = '$statusFilter'" : "";

// Handle status update
if(isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['new_status'];
    
    $updateSql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("si", $new_status, $order_id);
    
    if($stmt->execute()) {
        $statusMsg = "Order status updated successfully!";
    } else {
        $errorMsg = "Error updating order status: " . $conn->error;
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Get total records
$totalRecords = $conn->query("SELECT COUNT(*) as count FROM orders $whereClause")->fetch_assoc()['count'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Get orders with pagination
$orders = $conn->query("SELECT * FROM orders $whereClause ORDER BY order_date DESC LIMIT $offset, $recordsPerPage");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Biku Rental</title>
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
                    <h1 class="h2">Manage Orders</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo $statusFilter ? "Status: $statusFilter" : "Filter by Status"; ?>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                <li><a class="dropdown-item" href="orders.php">All Orders</a></li>
                                <li><a class="dropdown-item" href="orders.php?status=Pending">Pending</a></li>
                                <li><a class="dropdown-item" href="orders.php?status=Processing">Processing</a></li>
                                <li><a class="dropdown-item" href="orders.php?status=Delivered">Delivered</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <?php if(isset($statusMsg)): ?>
                    <div class="alert alert-success"><?php echo $statusMsg; ?></div>
                <?php endif; ?>
                
                <?php if(isset($errorMsg)): ?>
                    <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
                <?php endif; ?>
                
                <!-- Orders Table -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-table me-1"></i>
                        Orders
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Contact</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($orders->num_rows > 0): ?>
                                        <?php while($order = $orders->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $order['order_id']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                            <td>
                                                <?php 
                                                if($order['user_id'] > 0) {
                                                    $user = $conn->query("SELECT user_name FROM users WHERE user_id = " . $order['user_id'])->fetch_assoc();
                                                    echo $user ? $user['user_name'] : 'Unknown';
                                                } else {
                                                    echo 'Guest';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if($order['user_id'] > 0) {
                                                    $userContact = $conn->query("SELECT user_phone FROM users WHERE user_id = " . $order['user_id'])->fetch_assoc();
                                                    echo $userContact ? $userContact['user_phone'] : 'Unknown';
                                                } else {
                                                    echo 'N/A';
                                                }
                                                ?>
                                            </td>
                                            <td>$<?php echo number_format($order['order_cost'], 2); ?></td>
                                            <td>
                                                <span class="badge <?php 
                                                    if($order['order_status'] == 'Delivered') echo 'bg-success';
                                                    else if($order['order_status'] == 'Processing') echo 'bg-primary';
                                                    else echo 'bg-warning';
                                                ?>">
                                                    <?php echo $order['order_status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="view_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="bi bi-eye"></i> View
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateStatusModal<?php echo $order['order_id']; ?>">
                                                        <i class="bi bi-pencil"></i> Update
                                                    </button>
                                                </div>
                                                
                                                <!-- Update Status Modal -->
                                                <div class="modal fade" id="updateStatusModal<?php echo $order['order_id']; ?>" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="updateStatusModalLabel">Update Order Status</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form method="post" action="">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                                                    <div class="mb-3">
                                                                        <label for="new_status" class="form-label">New Status</label>
                                                                        <select class="form-select" id="new_status" name="new_status" required>
                                                                            <option value="Pending" <?php echo $order['order_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                                            <option value="Processing" <?php echo $order['order_status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                                                            <option value="Delivered" <?php echo $order['order_status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
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
                        <?php if($totalPages > 1): ?>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo $statusFilter ? "&status=$statusFilter" : ""; ?>">Previous</a>
                                </li>
                                
                                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $statusFilter ? "&status=$statusFilter" : ""; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo $statusFilter ? "&status=$statusFilter" : ""; ?>">Next</a>
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