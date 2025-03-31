<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: auth/login.php");
    exit;
}

// Include database connection
require_once "config/db.php";

// Get date range for filtering
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Add time to end date to include the entire day
$end_date_with_time = $end_date . ' 23:59:59';

// Get total sales by date
$sales_sql = "SELECT DATE(order_date) as date, SUM(order_cost) as total 
              FROM orders 
              WHERE order_date BETWEEN ? AND ? 
              GROUP BY DATE(order_date) 
              ORDER BY date";
$stmt = $conn->prepare($sales_sql);
$stmt->bind_param("ss", $start_date, $end_date_with_time);
$stmt->execute();
$sales_result = $stmt->get_result();
$stmt->close();

// Prepare data for chart
$dates = [];
$sales = [];
$total_sales = 0;

while($row = $sales_result->fetch_assoc()) {
    $dates[] = date('M d', strtotime($row['date']));
    $sales[] = $row['total'];
    $total_sales += $row['total'];
}

// Get sales by product category
$category_sql = "SELECT p.product_category, SUM(oi.product_price * oi.product_quantity) as total 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.product_id 
                WHERE oi.order_date BETWEEN ? AND ? 
                GROUP BY p.product_category 
                ORDER BY total DESC";
$stmt = $conn->prepare($category_sql);
$stmt->bind_param("ss", $start_date, $end_date_with_time);
$stmt->execute();
$category_result = $stmt->get_result();
$stmt->close();

// Get top selling products
$top_products_sql = "SELECT p.product_name, SUM(oi.product_quantity) as quantity 
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.product_id 
                    WHERE oi.order_date BETWEEN ? AND ? 
                    GROUP BY p.product_id 
                    ORDER BY quantity DESC 
                    LIMIT 5";
$stmt = $conn->prepare($top_products_sql);
$stmt->bind_param("ss", $start_date, $end_date_with_time);
$stmt->execute();
$top_products_result = $stmt->get_result();
$stmt->close();

// Get order status counts
$status_sql = "SELECT order_status, COUNT(*) as count 
              FROM orders 
              WHERE order_date BETWEEN ? AND ? 
              GROUP BY order_status";
$stmt = $conn->prepare($status_sql);
$stmt->bind_param("ss", $start_date, $end_date_with_time);
$stmt->execute();
$status_result = $stmt->get_result();
$stmt->close();

// Include header
include "includes/header.php";

// Include sidebar
include "includes/sidebar.php";
?>

<div class="col-md-9 col-lg-10 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Sales Reports</h1>
         
    </div>

    <!-- Date Range Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter by Date Range</h6>
        </div>
        <div class="card-body">
            <form action="" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Sales</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">$<?php echo number_format($total_sales, 2); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Orders</div>
                            <?php
                            $orders_count_sql = "SELECT COUNT(*) as count FROM orders WHERE order_date BETWEEN ? AND ?";
                            $stmt = $conn->prepare($orders_count_sql);
                            $stmt->bind_param("ss", $start_date, $end_date_with_time);
                            $stmt->execute();
                            $orders_count = $stmt->get_result()->fetch_assoc()['count'];
                            $stmt->close();
                            ?>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $orders_count; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg. Order Value</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                $<?php echo ($orders_count > 0) ? number_format($total_sales / $orders_count, 2) : '0.00'; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Products Sold</div>
                            <?php
                            $products_count_sql = "SELECT SUM(product_quantity) as count FROM order_items WHERE order_date BETWEEN ? AND ?";
                            $stmt = $conn->prepare($products_count_sql);
                            $stmt->bind_param("ss", $start_date, $end_date_with_time);
                            $stmt->execute();
                            $products_count = $stmt->get_result()->fetch_assoc()['count'];
                            $stmt->close();
                            ?>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $products_count ?? 0; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-motorcycle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="row">
        <div class="col-xl-8 col-lg-7">
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

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Order Status</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Products -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Selling Products</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity Sold</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($product = $top_products_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $product['product_name']; ?></td>
                                    <td><?php echo $product['quantity']; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales by Category -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sales by Category</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Total Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($category = $category_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $category['product_category']; ?></td>
                                    <td>$<?php echo number_format($category['total'], 2); ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Sales Chart
document.addEventListener('DOMContentLoaded', function() {
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{
                label: 'Daily Sales',
                data: <?php echo json_encode($sales); ?>,
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderColor: 'rgba(78, 115, 223, 1)',
                pointRadius: 3,
                pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointBorderColor: 'rgba(78, 115, 223, 1)',
                pointHoverRadius: 5,
                pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                pointHitRadius: 10,
                pointBorderWidth: 2,
                tension: 0.3
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '$' + context.parsed.y;
                        }
                    }
                }
            }
        }
    });

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusLabels = [];
    const statusData = [];
    const statusColors = [
        'rgba(255, 99, 132, 0.7)',
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 206, 86, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(153, 102, 255, 0.7)'
    ];

    <?php 
    while($status = $status_result->fetch_assoc()) {
        echo "statusLabels.push('" . $status['order_status'] . "');\n";
        echo "statusData.push(" . $status['count'] . ");\n";
    }
    ?>

    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: statusColors,
                hoverOffset: 4
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Export to CSV
    document.getElementById('exportBtn').addEventListener('click', function() {
        // Create CSV content
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Date,Sales\n";
        
        <?php
        foreach($sales_result as $row) {
            echo "csvContent += '" . $row['date'] . "," . $row['total'] . "\\n';\n";
        }
        ?>
        
        // Create download link
        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "sales_report_<?php echo $start_date; ?>_to_<?php echo $end_date; ?>.csv");
        document.body.appendChild(link);
        
        // Trigger download
        link.click();
    });
});
</script>

<?php
// Include footer
include "includes/footer.php";

// Close connection
$conn->close();
?>

