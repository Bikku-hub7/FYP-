<?php
session_start();
include('server/connection.php');

// Redirect if not logged in
if(!isset($_SESSION['logged_in'])){
    header('location: login.php');
    exit;
}

// Logout functionality
if(isset($_GET['logout'])){
    session_destroy();
    header('location: login.php');
    exit;
}

// Change password
if(isset($_POST['change_password'])){
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $user_email = $_SESSION['user_email'];

    // Validate passwords
    if($password !== $confirmPassword){
        header('location: account.php?error=Passwords do not match');
        exit;
    } else if(strlen($password) < 8){
        header('location: account.php?error=Password must be at least 8 characters');
        exit;
    } else {
        // Hash the password before storing
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("UPDATE users SET user_password = ? WHERE user_email = ?");
        $stmt->bind_param("ss", $hashed_password, $user_email);
        
        if($stmt->execute()){
            header('location: account.php?message=Password changed successfully');
        } else {
            header('location: account.php?error=Failed to change password');
        }
        exit;
    }
}

// Update profile information
if(isset($_POST['update_profile'])){
    $user_name = $_POST['user_name'];
    $user_phone = $_POST['user_phone'];
    $user_city = $_POST['user_city'];
    $user_address = $_POST['user_address'];
    $user_email = $_SESSION['user_email'];

    $stmt = $conn->prepare("UPDATE users SET user_name = ?, user_phone = ?, user_city = ?, user_address = ? WHERE user_email = ?");
    $stmt->bind_param("sssss", $user_name, $user_phone, $user_city, $user_address, $user_email);
    
    if($stmt->execute()){
        // Update session variables
        $_SESSION['user_name'] = $user_name;
        header('location: account.php?message=Profile updated successfully');
    } else {
        header('location: account.php?error=Failed to update profile');
    }
    exit;
}

// Get user data for pre-filling the form
$user_data = [];
if(isset($_SESSION['logged_in']) && isset($_SESSION['user_email'])){
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_email = ?");
    $stmt->bind_param("s", $_SESSION['user_email']);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();
}

// Get orders if logged in
$orders = [];
if(isset($_SESSION['logged_in']) && isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $orders = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .tab-content {
            display: none;
            background: white;
            border-radius: 0 8px 8px 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
        }
        .tab-content.active {
            display: block;
        }
        .tab-links {
            display: flex;
            margin: 30px 0 0 0;
        }
        .tab-link {
            padding: 12px 25px;
            cursor: pointer;
            background: #f8f9fa;
            margin-right: 5px;
            border-radius: 8px 8px 0 0;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #495057;
            border: 1px solid #dee2e6;
            border-bottom: none;
        }
        .tab-link.active {
            background: white;
            color: #dc3545;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-control:focus {
            border-color: #dc3545;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(220,53,69,0.25);
        }
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        .btn.shop-buy-btn {
            background-color: #dc3545 ;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        .btn.shop-buy-btn:hover {
            background-color: #c82333;
        }
        .alert {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <?php include('layouts/header.php'); ?>

    <!-- Account Section -->
    <section class="my-5 py-5">
        <div class="row container mx-auto">
            <div class="text-center mt-3 pt-5 col-lg-6 col-md-12 col-sm-12">
                <?php if(isset($_GET['register_success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_GET['register_success']) ?></div>
                <?php endif; ?>
                <?php if(isset($_GET['login_success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_GET['login_success']) ?></div>
                <?php endif; ?>
                <h2 class="font-weight-bold">Account Info</h2>
                <hr class="mx-auto">
                <div class="account-info">
                    <p>Name: <span><?= htmlspecialchars($_SESSION['user_name'] ?? 'N/A') ?></span></p>
                    <p>Email: <span><?= htmlspecialchars($_SESSION['user_email'] ?? 'N/A') ?></span></p>
                    <p><a href="#orders" id="order-btn">Your Orders</a></p>
                    <p><a href="account.php?logout=1" id="logout-btn">Logout</a></p>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-sm-12">
                <div class="tab-links">
                    <div class="tab-link active" onclick="openTab('profile-tab')">Edit Profile</div>
                    <div class="tab-link" onclick="openTab('password-tab')">Change Password</div>
                </div>
                
                <!-- Profile Edit Tab -->
                <div id="profile-tab" class="tab-content active">
                    <?php if(isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                    <?php endif; ?>
                    <?php if(isset($_GET['message'])): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($_GET['message']) ?></div>
                    <?php endif; ?>
                    <h3 class="mb-4">Edit Profile</h3>
                    <form id="profile-form" method="POST" action="account.php">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" class="form-control" name="user_name" 
                                   value="<?= htmlspecialchars($user_data['user_name'] ?? '') ?>" required/>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" class="form-control" name="user_phone" 
                                   value="<?= htmlspecialchars($user_data['user_phone'] ?? '') ?>"/>
                        </div>
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" class="form-control" name="user_city" 
                                   value="<?= htmlspecialchars($user_data['user_city'] ?? '') ?>"/>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea class="form-control" name="user_address"><?= htmlspecialchars($user_data['user_address'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <input type="submit" name="update_profile" class="btn shop-buy-btn" value="Update Profile"/>
                        </div>
                    </form>
                </div>
                
                <!-- Change Password Tab -->
                <div id="password-tab" class="tab-content text-start">
                    <?php if(isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                    <?php endif; ?>
                    <?php if(isset($_GET['message'])): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($_GET['message']) ?></div>
                    <?php endif; ?>
                    <h3 class="mb-4">Change Password</h3>
                    <form id="account-form" method="POST" action="account.php">
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Password" required minlength="8"/>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" class="form-control" name="confirmPassword" placeholder="Confirm Password" required minlength="8"/>
                        </div>
                        <div class="form-group">
                            <input type="submit" name="change_password" class="btn shop-buy-btn" value="Change Password"/>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Orders Section -->
    <section id="orders" class="order container my-5 py-3">
        <div class="container mt-2">
            <h2 class="font-weight-bold text-center">Your Orders</h2>
            <hr class="mx-auto">
        </div>

        <?php if($orders->num_rows > 0): ?>
            <table class="mt-5 pt-5 table table-striped">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Cost</th>
                        <th>Order Status</th>
                        <th>Order Date</th>
                        <th>Order Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['order_id']) ?></td>
                            <td>$<?= number_format($row['order_cost'], 2) ?></td>
                            <td><?= htmlspecialchars($row['order_status']) ?></td>
                            <td><?= htmlspecialchars($row['order_date']) ?></td>
                            <td>
                                <form method="POST" action="order_details.php">
                                    <input type="hidden" name="order_status" value="<?= htmlspecialchars($row['order_status']) ?>">
                                    <input type="hidden" name="order_id" value="<?= htmlspecialchars($row['order_id']) ?>">
                                    <input class="btn order-details-btn" name="order_details_btn" type="submit" value="View Details">
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="text-center py-5">
                <p class="lead">You have no orders yet!</p>
                <a href="bikeslist.php" class="btn shop-buy-btn">Start Shopping</a>
            </div>
        <?php endif; ?>
    </section>

    <script>
        function openTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab links
            document.querySelectorAll('.tab-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Show the selected tab content
            document.getElementById(tabId).classList.add('active');
            
            // Add active class to the clicked tab link
            event.currentTarget.classList.add('active');
        }
    </script>

    <?php include('layouts/footer.php'); ?>
</body>
</html>