<?php

session_start();
include('server/connection.php'); // Include database connection

if (isset($_POST['edit_quantity']) && isset($_POST['cart_id']) && isset($_POST['quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['quantity'];

    // Validate quantity
    if ($quantity <= 0) {
        echo "<script>alert('Quantity must be greater than zero');</script>";
        echo "<script>window.location.href = 'cart.php';</script>";
        exit;
    }

    // Prepare and execute the query to update the quantity
    $query = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $quantity, $cart_id);

    if ($stmt->execute()) {
        echo "<script>alert('Cart updated successfully');</script>";
    } else {
        echo "<script>alert('Failed to update cart');</script>";
    }

    // Redirect back to the cart page
    echo "<script>window.location.href = 'cart.php';</script>";
} else {
    // Redirect to cart page if accessed directly
    echo "<script>window.location.href = 'cart.php';</script>";
}

?>
