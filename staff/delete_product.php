<?php
session_start();
require_once 'config/db.php';

// Check if staff is logged in
if(!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

// Check if product ID is provided
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: products.php");
    exit();
}

$product_id = $_GET['id'];

// Delete the product
$deleteSql = "DELETE FROM products WHERE product_id = ?";
$stmt = $conn->prepare($deleteSql);
$stmt->bind_param("i", $product_id);

if($stmt->execute()) {
    // Set success message in session
    $_SESSION['success_msg'] = "Product deleted successfully!";
} else {
    // Set error message in session
    $_SESSION['error_msg'] = "Error deleting product: " . $conn->error;
}

// Redirect back to products page
header("Location: products.php");
exit();
?>