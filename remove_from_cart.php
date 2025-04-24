<?php

session_start();
include('server/connection.php'); // Include database connection

if (isset($_POST['remove_product']) && isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id'];

    // Prepare and execute the query to remove the item from the cart
    $query = "DELETE FROM cart WHERE cart_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $cart_id);

    if ($stmt->execute()) {
        echo "<script>alert('Product removed from cart successfully');</script>";
    } else {
        echo "<script>alert('Failed to remove product from cart');</script>";
    }

    // Redirect back to the cart page
    echo "<script>window.location.href = 'cart.php';</script>";
} else {
    // Redirect to cart page if accessed directly
    echo "<script>window.location.href = 'cart.php';</script>";
}

?>
