<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: auth/login.php");
    exit;
}

// Include database connection
require_once "config/db.php";

// Get search query
$search = isset($_GET['q']) ? $_GET['q'] : '';

// Include header
include "includes/header.php";

// Include sidebar
include "includes/sidebar.php";
?>

<div class="col-md-9 col-lg-10 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Search Results</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <form action="" method="GET" class="d-flex">
                <input type="text" name="q" class="form-control me-2" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>

    <?php if(!empty($search)): ?>
        <?php
        // Search in orders
        $search_param = "%$search%";
        $orders_sql = "SELECT o.*, COUNT(oi.item_id) as item_count, u.user_name 
                      FROM orders o 
                      LEFT JOIN order_items oi ON o.order_id = oi.order_id
                      LEFT JOIN users u ON o.user_id = u.user_id
                      WHERE o.order_id LIKE ? 
                         OR o.user_phone LIKE ? 
                         OR o.user_city LIKE ? 
                         OR o.user_address LIKE ?
                         OR u.user_name LIKE ?
                         OR u.user_email LIKE ?
                         OR o.order_status LIKE ?
                      GROUP BY o.order_id 
                      ORDER BY o.order_date DESC 
                      LIMIT 5";
        $stmt = $conn->prepare($orders_sql);
        $stmt->bind_param("sssssss", $search_param, $search_param, $search_param, $search_param, $search_param, $search_param, $search_param);
        $stmt->execute();
        $orders_result = $stmt->get_result();
        $stmt->close();
        
        // Search in products
        $products_sql = "SELECT * FROM products 
                        WHERE product_name LIKE ? OR product_category LIKE ? OR product_description LIKE ?
                        ORDER BY product_id DESC 
                        LIMIT 5";
        $stmt = $conn->prepare($products_sql);
        $stmt->bind_param("sss", $search_param, $search_param, $search_param);
        $stmt->execute();
        $products_result = $stmt->get_result();
        $stmt->close();
        
        // Search in users
        $users_sql = "SELECT * FROM users 
                     WHERE user_name LIKE ? OR user_email LIKE ? OR user_phone LIKE ?
                     ORDER BY user_id DESC 
                     LIMIT 5";
        $stmt = $conn->prepare($users_sql);
        $stmt->bind_param("sss", $search_param, $search_param, $search_param);
        $stmt->execute();
        $users_result = $stmt->get_result();
        $stmt->close();
        ?>

        <!-- Orders Results -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Orders</h6>
                <?php if($orders_result->num_rows > 0): ?>
                <a href="orders.php?search=<?php echo urlencode($search); ?>" class="btn btn-sm btn-outline-primary">View All</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if($orders_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $orders_result->fetch_assoc()): ?>
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
                                    <a href="order-details.php?id=<?php echo $row['order_id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">No orders found matching "<?php echo htmlspecialchars($search); ?>"</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Products Results -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Products</h6>
                <?php if($products_result->num_rows > 0): ?>
                <a href="products.php?search=<?php echo urlencode($search); ?>" class="btn btn-sm btn-outline-primary">View All</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if($products_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $products_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['product_id']; ?></td>
                                <td>
                                    <img src="assets/images/<?php echo $row['product_image']; ?>" alt="<?php echo $row['product_name']; ?>" width="50">
                                </td>
                                <td><?php echo $row['product_name']; ?></td>
                                <td><?php echo $row['product_category']; ?></td>
                                <td>$<?php echo number_format($row['product_price'], 2); ?></td>
                                <td>
                                    <a href="edit-product.php?id=<?php echo $row['product_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">No products found matching "<?php echo htmlspecialchars($search); ?>"</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Users Results -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Users</h6>
                <?php if($users_result->num_rows > 0): ?>
                <a href="users.php?search=<?php echo urlencode($search); ?>" class="btn btn-sm btn-outline-primary">View All</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if($users_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['user_id']; ?></td>
                                <td><?php echo $row['user_name']; ?></td>
                                <td><?php echo $row['user_email']; ?></td>
                                <td>
                                    <a href="edit-user.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-muted">No users found matching "<?php echo htmlspecialchars($search); ?>"</p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Enter a search term to find orders, products, or users.
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include "includes/footer.php";

// Close connection
$conn->close();
?>

