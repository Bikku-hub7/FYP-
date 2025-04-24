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

// Start transaction
$conn->begin_transaction();

try {
    // Delete order items first
    $delete_items_sql = "DELETE FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($delete_items_sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();
    
    // Delete order
    $delete_order_sql = "DELETE FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($delete_order_sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    $_SESSION['status_message'] = "Order deleted successfully!";
    $_SESSION['status_type'] = "success";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    $_SESSION['status_message'] = "Error deleting order: " . $e->getMessage();
    $_SESSION['status_type'] = "danger";
}

// Close connection
$conn->close();

// Redirect back to orders page
header("Location: orders.php");
exit;
?>

