<?php
session_start();
require_once('../connection.php'); // adjust to your DB config path

// Check if order details exist
if (!isset($_SESSION['order_details'])) {
    header("Location: ../../checkout.php");
    exit();
}

$order = $_SESSION['order_details'];
$userId = $_SESSION['user_id'] ?? null; // You must have user session for cart cleanup

// Insert order into database when payment is successful
$status = "Processing"; 
$userCity = isset($order['customer']['city']) ? $order['customer']['city'] : 'Unknown';
$userAddress = $order['customer']['address'];
$orderDate = !empty($order['date']) ? date("Y-m-d H:i:s", strtotime($order['date'])) : date("Y-m-d H:i:s");

$stmt = $conn->prepare("INSERT INTO orders (order_cost, order_status, user_id, user_city, user_address, order_date) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("dsisss", $order['amount'], $status, $userId, $userCity, $userAddress, $orderDate);
$stmt->execute();
$generatedOrderId = $stmt->insert_id;
$stmt->close();

// Insert cart items into order_items table
if ($userId) {
    // Fetch cart items for the user
    $stmt = $conn->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $cartItems = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    foreach ($cartItems as $item) {
        $prodId = $item['product_id'];
        $prodQuantity = $item['quantity'];
        
        // Fetch product details
        $stmtProd = $conn->prepare("SELECT product_name, product_image, product_price FROM products WHERE product_id = ?");
        $stmtProd->bind_param("i", $prodId);
        $stmtProd->execute();
        $resultProd = $stmtProd->get_result();
        $prodDetails = $resultProd->fetch_assoc();
        $stmtProd->close();
        if (!$prodDetails) continue;
        
        $prodName = $prodDetails['product_name'];
        $prodImage = $prodDetails['product_image'];
        $prodPrice = $prodDetails['product_price'];
        
        $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, product_price, product_quantity, user_id, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmtItem->bind_param("isssdiis", $generatedOrderId, $prodId, $prodName, $prodImage, $prodPrice, $prodQuantity, $userId, $orderDate);
        $stmtItem->execute();
        $stmtItem->close();
    }
}

// Clear ordered items from cart
if ($userId) {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
}

// After order and order_items insertion, send confirmation email to user
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once '../../PHPMailer-master/src/PHPMailer.php';
require_once '../../PHPMailer-master/src/SMTP.php';
require_once '../../PHPMailer-master/src/Exception.php';

// Prepare email details for user
$user_email = $order['customer']['email'];
$user_name = $order['customer']['name'];
$order_amount = $order['amount'];
$order_txn = $order['transaction_id'];
$order_date = $formattedDate ?? date('F j, Y, g:i a');
$order_address = $order['customer']['address'];

// Send email to user
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'np03cs4s230163@heraldcollege.edu.np';
    $mail->Password = 'molz jglm dojv bnsw';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('np03cs4s230163@heraldcollege.edu.np', 'Biku Bike Rentals');
    $mail->addAddress($user_email, $user_name);
    $mail->isHTML(true);
    $mail->Subject = 'Order Confirmation - Biku Bike Rentals';
    $mail->Body = "
        <h3>Dear $user_name,</h3>
        <p>Thank you for your order!</p>
        <p>Your order has been placed successfully.</p>
        <ul>
            <li><strong>Transaction ID:</strong> $order_txn</li>
            <li><strong>Date:</strong> $order_date</li>
            <li><strong>Total Paid:</strong> Rs. $order_amount</li>
            <li><strong>Delivery Address:</strong> $order_address</li>
        </ul>
        <p>We appreciate your business.<br>Biku Bike Rentals Team</p>
    ";
    $mail->send();
} catch (Exception $e) {
    error_log('Order confirmation mail error: ' . $mail->ErrorInfo);
}

// Send email to admin(s)
try {
    $admin_email = 'np03cs4s230163@heraldcollege.edu.np';  
    $mail_admin = new PHPMailer(true);
    $mail_admin->isSMTP();
    $mail_admin->Host = 'smtp.gmail.com';
    $mail_admin->SMTPAuth = true;
    $mail_admin->Username = 'np03cs4s230163@heraldcollege.edu.np';
    $mail_admin->Password = 'molz jglm dojv bnsw';
    $mail_admin->SMTPSecure = 'tls';
    $mail_admin->Port = 587;

    $mail_admin->setFrom('np03cs4s230163@heraldcollege.edu.np', 'Biku Bike Rentals');
    $mail_admin->addAddress($admin_email, 'Admin');
    $mail_admin->isHTML(true);
    $mail_admin->Subject = 'New Order Placed - Biku Bike Rentals';
    $mail_admin->Body = "
        <h3>New Order Received</h3>
        <ul>
            <li><strong>Customer Name:</strong> $user_name</li>
            <li><strong>Customer Email:</strong> $user_email</li>
            <li><strong>Transaction ID:</strong> $order_txn</li>
            <li><strong>Date:</strong> $order_date</li>
            <li><strong>Total Paid:</strong> Rs. $order_amount</li>
            <li><strong>Delivery Address:</strong> $order_address</li>
        </ul>
    ";
    $mail_admin->send();
} catch (Exception $e) {
    error_log('Admin order notification mail error: ' . $mail_admin->ErrorInfo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation - Biku Bike Rentals</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #dc3545; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 10px; border: none; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .alert-success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
        .btn-primary:hover { background-color: #c82333; border-color: #bd2130; }
        .btn-outline-secondary { color: var(--primary-color); border-color: var(--primary-color); }
        .btn-outline-secondary:hover { background-color: var(--primary-color); color: white; }
        .table th { background-color: #f1f1f1; }
        h2, h4, h5 { color: var(--primary-color); }
    </style>
</head>
<body>
<section class="my-5 py-5">
    <div class="container text-center mt-3 pt-5">
        <h2 class="font-weight-bold">Order Confirmed</h2>
        <hr class="mx-auto" style="border-top: 2px solid var(--primary-color); width: 100px;">
    </div>
    <div class="mx-auto container" style="max-width: 1200px;">
        <div class="card shadow">
            <div class="card-body">
                <div class="alert alert-success">
                    <h4><i class="fas fa-check-circle"></i> Payment Successful</h4>
                    <p>Thank you for your purchase! We've received your payment.</p>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>Order Summary</h5>
                        <table class="table">
                            <tr>
                                <th>Transaction ID:</th>
                                <td><?php echo $order['transaction_id']; ?></td>
                            </tr>
                            <tr>
                                <th>Date:</th>
                                <td>
                                    <?php 
                                    $formattedDate = strtotime($order['date']) ? date('F j, Y, g:i a', strtotime($order['date'])) : 'Invalid Date';
                                    echo $formattedDate; 
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Total Paid:</th>
                                <td>Rs. <?php echo number_format($order['amount'], 2); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Customer Information</h5>
                        <table class="table">
                            <tr>
                                <th>Name:</th>
                                <td><?php echo htmlspecialchars($order['customer']['name']); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($order['customer']['email']); ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?php echo htmlspecialchars($order['customer']['phone']); ?></td>
                            </tr>
                            <tr>
                                <th>Address:</th>
                                <td><?php echo htmlspecialchars($order['customer']['address']); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                 
                
                <div class="text-center mt-4">
                    <a href="../../index.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-secondary ml-2">
                        <i class="fas fa-print"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Clear the order details from session after showing
unset($_SESSION['order_details']);
?>
