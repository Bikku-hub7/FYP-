<?php
session_start();
require_once 'config/db.php';

// Check if staff is logged in
if(!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

// Check if order ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['id'];

// Get order details
$orderQuery = "SELECT * FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$orderResult = $stmt->get_result();

if($orderResult->num_rows == 0) {
    header("Location: orders.php");
    exit();
}

$order = $orderResult->fetch_assoc();

// Get order items
$itemsQuery = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($itemsQuery);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$orderItems = $stmt->get_result();

// Get customer details if registered user
$customer = null;
if($order['user_id'] > 0) {
    $customerQuery = "SELECT * FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($customerQuery);
    $stmt->bind_param("i", $order['user_id']);
    $stmt->execute();
    $customerResult = $stmt->get_result();
    if($customerResult->num_rows > 0) {
        $customer = $customerResult->fetch_assoc();
    }
}

// Handle status update
if(isset($_POST['update_status'])) {
    $new_status = $_POST['new_status'];
    
    $updateSql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("si", $new_status, $order_id);
    
    if($stmt->execute()) {
        $order['order_status'] = $new_status;
        $statusMsg = "Order status updated successfully!";
    } else {
        $errorMsg = "Error updating order status: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Biku Rental</title>
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
        .order-summary {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
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
                    <h1 class="h2">Order #<?php echo $order_id; ?> Details</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="orders.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Orders
                        </a>
                    </div>
                </div>
                
                <?php if(isset($statusMsg)): ?>
                    <div class="alert alert-success"><?php echo $statusMsg; ?></div>
                <?php endif; ?>
                
                <?php if(isset($errorMsg)): ?>
                    <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Order Summary -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="order-summary">
                                    <p><strong>Order Date:</strong> <?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></p>
                                    <p><strong>Order Total:</strong> $<?php echo number_format($order['order_cost'], 2); ?></p>
                                    <p>
                                        <strong>Status:</strong> 
                                        <span class="badge <?php 
                                            if($order['order_status'] == 'Delivered') echo 'bg-success';
                                            else if($order['order_status'] == 'Processing') echo 'bg-primary';
                                            else echo 'bg-warning';
                                        ?>">
                                            <?php echo $order['order_status']; ?>
                                        </span>
                                    </p>
                                    
                                    <form method="post" action="" class="mt-3">
                                        <div class="mb-3">
                                            <label for="new_status" class="form-label">Update Status</label>
                                            <select class="form-select" id="new_status" name="new_status">
                                                <option value="Pending" <?php echo $order['order_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="Processing" <?php echo $order['order_status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                                <option value="Delivered" <?php echo $order['order_status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer Information -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <?php if($customer): ?>
                                    <p><strong>Name:</strong> <?php echo $customer['user_name']; ?></p>
                                    <p><strong>Email:</strong> <?php echo $customer['user_email']; ?></p>
                                <?php else: ?>
                                    <p><strong>Customer Type:</strong> Guest</p>
                                <?php endif; ?>
                                <!-- Changed phone data to fetch from user table if registered -->
                                <p><strong>Phone:</strong> <?php echo ($customer ? $customer['user_phone'] : $order['user_phone']); ?></p>
                                <p><strong>City:</strong> <?php echo $order['user_city']; ?></p>
                                <p><strong>Address:</strong> <?php echo $order['user_address']; ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Information -->
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Delivery Information</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Delivery Address:</strong> <?php echo $order['user_address']; ?></p>
                                <p><strong>City:</strong> <?php echo $order['user_city']; ?></p>
                                <!-- Changed contact phone to fetch from user table if registered -->
                                <p><strong>Contact Phone:</strong> <?php echo ($customer ? $customer['user_phone'] : $order['user_phone']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Items</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Image</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total = 0;
                                    while($item = $orderItems->fetch_assoc()): 
                                        $subtotal = $item['product_price'] * $item['product_quantity'];
                                        $total += $subtotal;
                                    ?>
                                    <tr>
                                        <td><?php echo $item['product_name']; ?></td>
                                        <td>
                                            <img src="../uploads/<?php echo $item['product_image']; ?>" alt="<?php echo $item['product_name']; ?>" width="50">
                                        </td>
                                        <td>$<?php echo number_format($item['product_price'], 2); ?></td>
                                        <td><?php echo $item['product_quantity']; ?></td>
                                        <td>$<?php echo number_format($subtotal, 2); ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Total:</th>
                                        <th>$<?php echo number_format($total, 2); ?></th>
                                    </tr>
                                </tfoot>
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