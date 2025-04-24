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

// Get user's order count
$orderCountQuery = "SELECT COUNT(*) as count FROM orders WHERE user_id = ?";
$stmt = $conn->prepare($orderCountQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orderCount = $stmt->get_result()->fetch_assoc()['count'];

// Get user's recent orders
$recentOrdersQuery = "SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 5";
$stmt = $conn->prepare($recentOrdersQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recentOrders = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - Biku Rental</title>
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
        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #6c757d;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin-right: 20px;
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
                    <h1 class="h2">User Details</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="users.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Users
                        </a>
                    </div>
                </div>
                
                <!-- User Profile -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">User Profile</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($user['user_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h4><?php echo $user['user_name']; ?></h4>
                                <p class="text-muted mb-0">Customer ID: #<?php echo $user['user_id']; ?></p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Contact Information</h5>
                                <p><strong>Email:</strong> <?php echo $user['user_email']; ?></p>
                                <p><strong>Phone:</strong> <?php echo $user['user_phone'] ? $user['user_phone'] : 'N/A'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Address Information</h5>
                                <p><strong>City:</strong> <?php echo $user['user_city'] ? $user['user_city'] : 'N/A'; ?></p>
                                <p><strong>Address:</strong> <?php echo $user['user_address'] ? $user['user_address'] : 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- User Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Orders</h5>
                                <h2 class="display-4"><?php echo $orderCount; ?></h2>
                                <a href="user_orders.php?id=<?php echo $user_id; ?>" class="btn btn-sm btn-primary">View All Orders</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Orders</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($recentOrders->num_rows > 0): ?>
                                        <?php while($order = $recentOrders->fetch_assoc()): ?>
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
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>