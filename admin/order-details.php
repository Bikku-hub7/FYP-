<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: auth/login.php");
    exit;
}

// Include database connection
require_once "config/db.php";

// Check if order ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: orders.php");
    exit;
}

$order_id = $_GET['id'];

// Get order details
$order_sql = "SELECT * FROM orders WHERE order_id = ?";
$stmt = $conn->prepare($order_sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_result = $stmt->get_result();

if($order_result->num_rows == 0) {
    header("Location: orders.php");
    exit;
}

$order = $order_result->fetch_assoc();
$stmt->close();

// Get order items
$items_sql = "SELECT * FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($items_sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$stmt->close();

// Handle status update
if(isset($_POST['update_status'])) {
    $new_status = $_POST['new_status'];
    
    $update_sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $order_id);
    
    if($stmt->execute()) {
        $status_message = "Order status updated successfully!";
        $status_type = "success";
        
        // Refresh order data
        $order['order_status'] = $new_status;
    } else {
        $status_message = "Error updating order status: " . $conn->error;
        $status_type = "danger";
    }
    
    $stmt->close();
}

// Include header
include "includes/header.php";

// Include sidebar
include "includes/sidebar.php";
?>

<div class="col-md-9 col-lg-10 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Order Details #<?php echo $order_id; ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="orders.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Orders
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <?php if(isset($status_message)): ?>
    <div class="alert alert-<?php echo $status_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $status_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header">
                    <h5 class="m-0 font-weight-bold text-primary">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Order ID:</div>
                        <div class="col-md-8">#<?php echo $order['order_id']; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Order Date:</div>
                        <div class="col-md-8"><?php echo date('F d, Y H:i', strtotime($order['order_date'])); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Order Total:</div>
                        <div class="col-md-8">$<?php echo number_format($order['order_cost'], 2); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Status:</div>
                        <div class="col-md-8">
                            <?php 
                            $status_class = '';
                            switch($order['order_status']) {
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
                            <span class="badge bg-<?php echo $status_class; ?>"><?php echo $order['order_status']; ?></span>
                            <button type="button" class="btn btn-sm btn-outline-primary ms-2" data-bs-toggle="modal" data-bs-target="#statusModal">
                                Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header">
                    <h5 class="m-0 font-weight-bold text-primary">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Customer:</div>
                        <div class="col-md-8">
                            <?php 
                            if($order['user_id'] > 0) {
                                $user_sql = "SELECT user_name, user_email, user_phone FROM users WHERE user_id = " . $order['user_id'];
                                $user_result = $conn->query($user_sql);
                                if($user_result->num_rows > 0) {
                                    $user = $user_result->fetch_assoc();
                                    echo $user['user_name'] . " (" . $user['user_email'] . ")";
                                } else {
                                    echo "User #" . $order['user_id'];
                                    $user = null;
                                }
                            } else {
                                echo "Guest";
                                $user = null;
                            }
                            ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Phone:</div>
                        <div class="col-md-8"><?php echo $user['user_phone'] ?? 'N/A'; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">City:</div>
                        <div class="col-md-8"><?php echo $order['user_city']; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Address:</div>
                        <div class="col-md-8"><?php echo $order['user_address']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="m-0 font-weight-bold text-primary">Order Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
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
                        while($item = $items_result->fetch_assoc()): 
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
    
    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="new_status" class="form-label">Status</label>
                            <select name="new_status" class="form-select" required>
                                <option value="Pending" <?php echo ($order['order_status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="Processing" <?php echo ($order['order_status'] == 'Processing') ? 'selected' : ''; ?>>Processing</option>
                                <option value="Shipped" <?php echo ($order['order_status'] == 'Shipped') ? 'selected' : ''; ?>>Shipped</option>
                                <option value="Delivered" <?php echo ($order['order_status'] == 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                                <option value="Cancelled" <?php echo ($order['order_status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
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
</div>

<?php
// Include footer
include "includes/footer.php";

// Close connection
$conn->close();
?>