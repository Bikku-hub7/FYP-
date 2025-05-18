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
    $quantity = 1;
    $user_id = $_SESSION['user_id'];
    
    // Check product availability first
    $avail_check = $conn->prepare("SELECT availability FROM products WHERE product_id = ?");
    $avail_check->bind_param("i", $product_id);
    $avail_check->execute();
    $avail_result = $avail_check->get_result();
    $product_availability = $avail_result->fetch_assoc()['availability'] ?? 0;
    
    if(!$product_availability) {
        header('location: cart.php?error=Product is currently unavailable and cannot be added to cart');
        exit;
    }

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

// Handle checkout validation
if(isset($_POST['validate_checkout'])) {
    if(isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        
        // Check if any product in the cart is unavailable
        $unavailable_check = $conn->prepare("
            SELECT c.product_id, p.product_name, p.availability 
            FROM cart c
            JOIN products p ON c.product_id = p.product_id
            WHERE c.user_id = ? AND p.availability = 0
        ");
        $unavailable_check->bind_param("i", $user_id);
        $unavailable_check->execute();
        $unavailable_result = $unavailable_check->get_result();
        
        if($unavailable_result->num_rows > 0) {
            // Some products are unavailable
            $unavailable_products = [];
            while($row = $unavailable_result->fetch_assoc()) {
                $unavailable_products[] = $row['product_name'];
            }
            
            $error_message = "Cannot checkout. The following products are unavailable: " . implode(", ", $unavailable_products);
            header("Location: cart.php?error=" . urlencode($error_message));
            exit;
        } else {
            // All products available, proceed to checkout
            header("Location: checkout.php");
            exit;
        }
    }
}

// Get cart items for logged in user
$cart_items = [];
$total_price = 0;
$total_quantity = 0;

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT c.*, p.product_name, p.product_price, p.product_image, p.availability 
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

// Check if any product is unavailable
$has_unavailable_products = false;
if(isset($cart_items) && $cart_items->num_rows > 0) {
    $cart_items->data_seek(0); // Reset pointer
    while($item = $cart_items->fetch_assoc()) {
        if(isset($item['availability']) && !$item['availability']) {
            $has_unavailable_products = true;
            break;
        }
    }
    // Reset pointer again for display
    $cart_items->data_seek(0);
}

include('layouts/header.php');
?>

<!-- Error/Success Messages -->
<?php if(isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show container mt-3" role="alert">
    <?php echo htmlspecialchars($_GET['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<?php if(isset($_GET['message'])): ?>
<div class="alert alert-success alert-dismissible fade show container mt-3" role="alert">
    <?php echo htmlspecialchars($_GET['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

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
            <th>Status</th>
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
                        <!-- Display static quantity instead of editable form -->
                        <?php echo $item['quantity']; ?>
                    </td>
                    <td>
                        <span class="badge <?php echo isset($item['availability']) && $item['availability'] ? 'bg-success' : 'bg-danger'; ?> p-2">
                            <?php echo isset($item['availability']) && $item['availability'] ? 'Available' : 'Unavailable'; ?>
                        </span>
                    </td>
                    <td>
                        <span>$</span>
                        <span class="product-price"><?php echo $item['quantity'] * $item['product_price']; ?></span>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="4">Your cart is empty.</td>
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
    
    <div class="checkout-container mt-4">
        <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) { ?>
            <div class="row">
                <div class="col-12 mb-3">
                    <form method="POST" action="cart.php">
                        <button type="submit" 
                               class="btn checkout-btn w-100 py-2 <?php echo $has_unavailable_products ? 'btn-outline-secondary' : 'btn-primary'; ?>" 
                               style="<?php echo $has_unavailable_products ? 'cursor: not-allowed; opacity: 0.7;' : ''; ?>"
                               name="validate_checkout" 
                               <?php if($has_unavailable_products): ?>
                               disabled title="Cannot checkout: Some products are unavailable"
                               <?php endif; ?>>
                            <?php if($has_unavailable_products): ?>
                                <i class="fas fa-ban me-2"></i> Checkout Unavailable
                            <?php else: ?>
                                <i class="fas fa-shopping-cart me-2"></i> Proceed to Checkout
                            <?php endif; ?>
                        </button>
                    </form>
                </div>
                
                <?php if($has_unavailable_products): ?>
                <div class="col-12">
                    <div class="alert alert-warning py-2 mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i> 
                        <strong>Note:</strong> Please remove unavailable items to proceed with checkout
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php } else { ?>
            <button class="btn checkout-btn btn-primary w-100 py-2" onclick="alert('You must log in to proceed to checkout.');">
                <i class="fas fa-lock me-2"></i> Login to Checkout
            </button>
        <?php } ?>
    </div>
</section>

<?php include('layouts/footer.php'); ?>