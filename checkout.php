<?php
session_start();

if(empty($_SESSION['cart'])) {
    header("Location: index.php");
    exit();
}

include('layouts/header.php');
?>

<!-- Checkout -->
<section class="my-5 py-5">
    <div class="container text-center mt-3 pt-5">
        <h2 class="font-weight-bold">Check Out</h2>
        <hr class="mx-auto">
    </div>
    <div class="mx-auto container">
        <form id="checkout-form" action="server/khalti-ePayment-gateway-main/payment-request.php" method="POST">
            <p class="text-center" style="color: red;">
                <?php 
                if(isset($_SESSION['validate_msg'])) {
                    echo $_SESSION['validate_msg'];
                    unset($_SESSION['validate_msg']);
                }
                ?>
            </p>
            
            <!-- Hidden fields for payment processing -->
            <input type="hidden" name="inputAmount4" value="<?php echo $_SESSION['total']; ?>">
            <input type="hidden" name="inputPurchasedOrderId4" value="<?php echo 'ORD'.uniqid(); ?>">
            <input type="hidden" name="inputPurchasedOrderName" value="Order #<?php echo rand(1000, 9999); ?>">
            
            <!-- Customer information -->
            <div class="form-group checkout-small-element">
                <label>Name</label>
                <input type="text" class="form-control" id="inputName" name="inputName" placeholder="Your Name" required>
            </div>
            <div class="form-group checkout-small-element">
                <label>Email</label>
                <input type="email" class="form-control" id="inputEmail" name="inputEmail" placeholder="Your Email" required>
            </div>
            <div class="form-group checkout-small-element">
                <label>Phone</label>
                <input type="tel" class="form-control" id="inputPhone" name="inputPhone" placeholder="98XXXXXXXX" required>
            </div>
            <div class="form-group checkout-small-element">
                <label>City</label>
                <input type="text" class="form-control" id="inputCity" name="inputCity" placeholder="Your City" required>
            </div>
            <div class="form-group checkout-large-element">
                <label>Address</label>
                <input type="text" class="form-control" id="inputAddress" name="inputAddress" placeholder="Your Full Address" required>
            </div>
            <div class="form-group checkout-btn-container">
                <p>Total Amount: $ <?php echo $_SESSION['total']; ?></p>
                <input type="submit" class="btn" id="checkout-btn" name="submit" value="Pay with Khalti">
            </div>
        </form>
    </div>
</section>

<?php include('layouts/footer.php'); ?>