<?php
session_start();
require_once 'config/db.php';

// Check if staff is logged in
if(!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

// Check if user ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

// Get user details
$userQuery = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$userResult = $stmt->get_result();

if($userResult->num_rows == 0) {
    header("Location: users.php");
    exit();
}

$user = $userResult->fetch_assoc();

// Handle status filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$whereClause = "WHERE user_id = $user_id";
if($statusFilter) {
    $whereClause .= " AND order_status = '$statusFilter'";
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
    <title>User Orders - Biku Rental</title>
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
                    <h1 class="h2">Orders for <?php echo $user['user_name']; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="dropdown me-2">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php echo $statusFilter ? "Status: $statusFilter" : "Filter by Status"; ?>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterDropdown">
                                <li><a class="dropdown-item" href="user_orders.php?id=<?php echo $user_id; ?>">All Orders</a></li>
                                <li><a class="dropdown-item" href="user_orders.php?id=<?php echo $user_id; ?>&status=Pending">Pending</a></li>
                                <li><a class="dropdown-item" href="user_orders.php?id=<?php echo $user_id; ?>&status=Processing">Processing</a></li>
                                <li><a class="dropdown-item" href="user_orders.php?id=<?php echo $user_id; ?>&status=Delivered">Delivered</a></li>
                            </ul>
                        </div>
                        <a href="view_user.php?id=<?php echo $user_id; ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to User
                        </a>
                    </div>
                </div>
                
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
                                                <a href="view_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No orders found</td>
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
                                    <a class="page-link" href="?id=<?php echo $user_id; ?>&page=<?php echo $page-1; ?><?php echo $statusFilter ? "&status=$statusFilter" : ""; ?>">Previous</a>
                                </li>
                                
                                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?id=<?php echo $user_id; ?>&page=<?php echo $i; ?><?php echo $statusFilter ? "&status=$statusFilter" : ""; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?id=<?php echo $user_id; ?>&page=<?php echo $page+1; ?><?php echo $statusFilter ? "&status=$statusFilter" : ""; ?>">Next</a>
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