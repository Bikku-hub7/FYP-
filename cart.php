<?php
session_start();
include('server/connection.php');

// Handle add to cart from product page
if(isset($_POST['add_to_cart'])) {
    if(!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('location: login.php?message=Please login to add items to cart');
        exit;
    }

    $product_id = $_POST['product_id'];
    $quantity = max(1, intval($_POST['product_quantity'])); // Ensure quantity is at least 1
    $user_id = $_SESSION['user_id'];

    // Check if product already exists in cart
    $check_cart = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $check_cart->bind_param("ii", $user_id, $product_id);
    $check_cart->execute();
    $cart_result = $check_cart->get_result();
    
    if($cart_result->num_rows > 0) {
        // Update quantity if product exists
        $update_cart = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
        $update_cart->bind_param("iii", $quantity, $user_id, $product_id);
        $update_cart->execute();
    } else {
        // Insert new cart item
        $insert_cart = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert_cart->bind_param("iii", $user_id, $product_id, $quantity);
        $insert_cart->execute();
    }
    
    header('location: cart.php?message=Product added to cart');
    exit;
}

// Handle remove product from cart
if(isset($_POST['remove_product'])) {
    if(isset($_SESSION['user_id'])) {
        $product_id = $_POST['product_id'];
        $user_id = $_SESSION['user_id'];
        
        $delete_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $delete_stmt->bind_param("ii", $user_id, $product_id);
        $delete_stmt->execute();
    }
    header('location: cart.php');
    exit;
}

// Handle edit quantity
if(isset($_POST['edit_quantity'])) {
    if(isset($_SESSION['user_id'])) {
        $product_id = $_POST['product_id'];
        $quantity = max(1, intval($_POST['product_quantity'])); // Ensure quantity is at least 1
        $user_id = $_SESSION['user_id'];
        
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $update_stmt->bind_param("iii", $quantity, $user_id, $product_id);
        $update_stmt->execute();
    }
    header('location: cart.php');
    exit;
}

// Get cart items for logged in user
$cart_items = [];
$total_price = 0;
$total_quantity = 0;

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT c.*, p.product_name, p.product_price, p.product_image 
                           FROM cart c 
                           JOIN products p ON c.product_id = p.product_id 
                           WHERE c.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_items = $stmt->get_result();
    
    // Calculate totals
    while($item = $cart_items->fetch_assoc()) {
        $total_price += $item['product_price'] * $item['quantity'];
        $total_quantity += $item['quantity'];
    }
    
    // Store totals in session
    $_SESSION['total'] = $total_price;
    $_SESSION['quantity'] = $total_quantity;
    
    // Reset pointer for display
    $cart_items->data_seek(0);
}

include('layouts/header.php');
?>

<!-- Cart -->
<section class="cart container my-5 py-5">
    <div class="container mt-5">
        <h2 class="font-weight-bold">Your Cart</h2>
        <hr>
    </div>

    <table class="mt-5 pt-5">
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>

        <?php if($cart_items && $cart_items->num_rows > 0) { ?>
            <?php while($item = $cart_items->fetch_assoc()) { ?>
                <tr>
                    <td>
                        <div class="product-info">
                            <img src="uploads/<?php echo $item['product_image']; ?>"/>
                            <div>
                                <p><?php echo $item['product_name']; ?></p>
                                <small><span>$</span><?php echo $item['product_price']; ?></small>
                                <br>
                                <form method="POST" action="cart.php">
                                    <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                    <input type="submit" name="remove_product" class="remove-btn" value="Remove">
                                </form>
                            </div>
                        </div>
                    </td>
                    <td>
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                            <input type="number" name="product_quantity" value="<?php echo $item['quantity']; ?>" min="1">
                            <input type="submit" class="edit-btn" name="edit_quantity" value="Edit">
                        </form>
                    </td>
                    <td>
                        <span>$</span>
                        <span class="product-price"><?php echo $item['quantity'] * $item['product_price']; ?></span>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="3">Your cart is empty.</td>
            </tr>
        <?php } ?>
    </table>
    
    <div class="cart-total">
        <?php if($cart_items && $cart_items->num_rows > 0) { ?>
            <table>
                <tr>
                    <td>Total</td>
                    <td>$<?php echo $total_price; ?></td>
                </tr>
            </table>
        <?php } ?>
    </div>
    
    <div class="checkout-container">
        <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) { ?>
            <form method="POST" action="checkout.php">
                <input type="submit" class="btn checkout-btn" value="Checkout" name="checkout">
            </form>
        <?php } else { ?>
            <button class="btn checkout-btn" onclick="alert('You must log in to proceed to checkout.');">Checkout</button>
        <?php } ?>
    </div>
</section>

<?php include('layouts/footer.php'); ?>