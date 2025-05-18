<?php
session_start();
// ...existing code...

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get order_id from GET or POST
$order_id = $_GET['order_id'] ?? $_POST['order_id'] ?? null;

// Get user email and name from session or database
$user_email = $_SESSION['user_email'] ?? '';
$user_name = $_SESSION['user_name'] ?? '';

if (!$user_email || !$user_name) {
    include("../server/connection.php");
    // Adjusted query: join on user_id, assuming your schema uses user_id
    $stmt = $conn->prepare("SELECT user_email, user_name FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_email = $user['user_email'];
        $user_name = $user['user_name'];
    }
}

// Send email to user
if ($user_email && $user_name && $order_id) {
    require_once '../PHPMailer-master/src/PHPMailer.php';
    require_once '../PHPMailer-master/src/SMTP.php';
    require_once '../PHPMailer-master/src/Exception.php';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'np03cs4s230163@heraldcollege.edu.np';
        $mail->Password = 'molz jglm dojv bnsw';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('np03cs4s230163@heraldcollege.edu.np', 'Bike Rental');
        $mail->addAddress($user_email, $user_name);
        $mail->isHTML(true);
        $mail->Subject = 'Order Confirmation - Bike Rental';
        $mail->Body = "
            <h3>Dear $user_name,</h3>
            <p>Thank you for your order!</p>
            <p>Your order (Order ID: <strong>$order_id</strong>) has been placed successfully.</p>
            <p>We appreciate your business.<br>Bike Rental Team</p>
        ";
        $mail->send();
    } catch (Exception $e) {
        error_log('Order confirmation mail error: ' . $mail->ErrorInfo);
    }
}

// ...existing code for UI or redirect...
?>