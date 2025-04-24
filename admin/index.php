<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: auth/login.php");
    exit;
}

// Include database connection
require_once "config/db.php";

// Get total orders
$sql_orders = "SELECT COUNT(*) as total_orders FROM orders";
$result_orders = $conn->query($sql_orders);
$total_orders = $result_orders->fetch_assoc()['total_orders'];

// Get total products
$sql_products = "SELECT COUNT(*) as total_products FROM products";
$result_products = $conn->query($sql_products);
$total_products = $result_products->fetch_assoc()['total_products'];

// Get total users
$sql_users = "SELECT COUNT(*) as total_users FROM users";
$result_users = $conn->query($sql_users);
$total_users = $result_users->fetch_assoc()['total_users'];

// Get total revenue
$sql_revenue = "SELECT SUM(order_cost) as total_revenue FROM orders";
$result_revenue = $conn->query($sql_revenue);
$total_revenue = $result_revenue->fetch_assoc()['total_revenue'];

// Get recent orders
$sql_recent_orders = "SELECT o.*, COUNT(oi.item_id) as item_count 
                      FROM orders o 
                      LEFT JOIN order_items oi ON o.order_id = oi.order_id 
                      GROUP BY o.order_id 
                      ORDER BY o.order_date DESC 
                      LIMIT 5";
$result_recent_orders = $conn->query($sql_recent_orders);

// Get order status counts
$sql_status = "SELECT order_status, COUNT(*) as count FROM orders GROUP BY order_status";
$result_status = $conn->query($sql_status);
$status_data = [];
while($row = $result_status->fetch_assoc()) {
    $status_data[$row['order_status']] = $row['count'];
}

// Include header
include "includes/header.php";

// Include sidebar
include "includes/sidebar.php";
?>

<div class="col-md-9 col-lg-10 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard</h1>
    </div>

    <!-- Dashboard Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 dashboard-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_orders; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 dashboard-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Revenue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($total_revenue, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2 dashboard-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_products; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-motorcycle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2 dashboard-card">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_users; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-xl-7 col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Sales Overview</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
        </div>
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result_recent_orders->fetch_assoc()): ?>
                        <tr>
                            <td>#<?php echo $row['order_id']; ?></td>
                            <td>
                                <?php 
                                if($row['user_id'] > 0) {
                                    $user_sql = "SELECT user_name FROM users WHERE user_id = " . $row['user_id'];
                                    $user_result = $conn->query($user_sql);
                                    if($user_result->num_rows > 0) {
                                        echo $user_result->fetch_assoc()['user_name'];
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
                                <a href="order-details.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-3">
                <a href="orders.php" class="btn btn-primary">View All Orders</a>
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
