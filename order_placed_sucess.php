<?php
session_start();
include('server/connection.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

// Retrieve order details from POST (adjust variable names as needed)
$user_email = $_POST['user_email'] ?? '';
$order_id   = $_POST['order_id'] ?? '';
$order_total = $_POST['order_total'] ?? '';
$admin_email = 'np03cs4s230163@heraldcollege.edu.np'; // replace with actual admin email

// Email content for user
$user_subject = "Order Placed Successfully";
$user_body = "Thank you for your order. Your Order ID: $order_id has been placed successfully. Total Amount: $order_total.";

// Email content for admin
$admin_subject = "New Order Placed";
$admin_body = "A new order has been placed.\nOrder ID: $order_id\nUser Email: $user_email\nTotal Amount: $order_total.";

// Helper function to send email
function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'np03cs4s230163@heraldcollege.edu.np'; // replace with your Gmail
        $mail->Password = 'molz jglm dojv bnsw'; // replace with Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        
        $mail->setFrom('np03cs4s230163@heraldcollege.edu.np', 'bike rental');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        // log error if needed: $mail->ErrorInfo
        return false;
    }
}

$userEmailSent = sendMail($user_email, $user_subject, $user_body);
$adminEmailSent = sendMail($admin_email, $admin_subject, $admin_body);

if ($userEmailSent && $adminEmailSent) {
    echo "Order placed successfully. Email notifications sent.";
} else {
    echo "Order placed, but failed to send email notifications.";
}
?>
