<div class="col-md-3 col-lg-2 px-0 sidebar">
    <div class="d-flex flex-column flex-shrink-0 p-3 text-white">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="index.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="orders.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'orders.php') ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart me-2"></i> Orders
                </a>
            </li>
            <li>
                <a href="products.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : ''; ?>">
                    <i class="fas fa-motorcycle me-2"></i> Products
                </a>
            </li>
            <li>
                <a href="users.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'active' : ''; ?>">
                    <i class="fas fa-users me-2"></i> Users
                </a>
            </li>
            <li>
                <a href="reports.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar me-2"></i> Reports
                </a>
            </li>
           
        </ul>
    </div>
</div>

