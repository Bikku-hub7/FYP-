<?php
session_start();
include('layouts/header.php');
include('server/connection.php');

// Recalculate cart total for accuracy
$total_price = 0;
$cart_items = []; // Ensure $cart_items is always defined

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT c.*, p.product_price FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cart_items = [];
    while ($item = $result->fetch_assoc()) {
        $total_price += $item['product_price'] * $item['quantity'];
        $cart_items[] = $item;
    }
    // Store cart items in session for payment gateway if needed
    $_SESSION['cart'] = $cart_items;
} else {
    // fallback for guest cart (if any)
    $cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
    $total_price = 0;
    foreach ($cart_items as $item) {
        $total_price += $item['product_price'] * $item['quantity'];
    }
}

$order_id = 'ORD' . uniqid();
$order_name = 'Order #' . rand(1000, 9999);
$amount = $total_price;
?>

<!-- Checkout Page -->
<section class="my-5 py-5">
    
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <div class="container text-center mt-3 pt-5">
        <h2 class="font-weight-bold">Check Out</h2>
        <hr class="mx-auto">
    </div>
    <div class="mx-auto container">
        <form id="checkout-form" action="/server/khalti-ePayment-gateway-main/payment-request.php" method="POST">
            <p class="text-center" style="color: red;">
                <?php 
                if(isset($_SESSION['validate_msg'])) {
                    echo $_SESSION['validate_msg'];
                    unset($_SESSION['validate_msg']);
                }
                ?>
            </p>

            <!-- Hidden Fields -->
            <input type="hidden" name="inputAmount4" value="<?php echo htmlspecialchars($amount * 100); ?>">
            <input type="hidden" name="inputPurchasedOrderId4" value="<?php echo htmlspecialchars($order_id); ?>">
            <input type="hidden" name="inputPurchasedOrderName4" value="<?php echo htmlspecialchars($order_name); ?>">
            <input type="hidden" name="cart_items" value='<?php echo json_encode($_SESSION['cart']); ?>'>

            <!-- User Inputs -->
            <div class="form-group checkout-small-element">
                <label>Name</label>
                <input type="text" class="form-control" name="inputName" required>
            </div>
            <div class="form-group checkout-small-element">
                <label>Email</label>
                <input type="email" class="form-control" name="inputEmail" required>
            </div>
            <div class="form-group checkout-small-element">
                <label>Phone</label>
                <input type="tel" class="form-control" name="inputPhone" required>
            </div>
            <div class="form-group checkout-small-element">
                <label>City</label>
                <input type="text" class="form-control" name="inputCity" required>
            </div>
            <div class="form-group checkout-large-element">
                <label>Address</label>
                <input type="text" class="form-control" name="inputAddress" required>
            </div>

            <!-- Submit -->
            <div class="form-group checkout-btn-container">
                <p>Total Amount: Rs. <?php echo $amount; ?></p>
                <input type="submit" class="btn btn-primary" name="submit" value="Pay with Khalti">
            </div>
        </form>
    </div>
</section>

<?php include('layouts/footer.php'); ?>
