<?php
session_start();
include('server/connection.php');

if(isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param('i',$product_id);
    $stmt->execute();
    $Product = $stmt->get_result();

    // Redirect if no product found
    if($Product->num_rows == 0) {
        header('location: index.php');
        exit;
    }
} else {    
    header('location: index.php');
    exit;
}

// Handle add to cart
if(isset($_POST['add_to_cart'])) {
    if(!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('location: login.php?message=Please login to add items to cart');
        exit;
    }

    $product_id = $_POST['product_id'];
    $quantity = 1;
    $user_id = $_SESSION['user_id'];

    // Check if product is available first
    $avail_check = $conn->prepare("SELECT availability FROM products WHERE product_id = ?");
    $avail_check->bind_param("i", $product_id);
    $avail_check->execute();
    $avail_result = $avail_check->get_result();
    $is_available = $avail_result->fetch_assoc()['availability'] ?? 0;
    
    if(!$is_available) {
        // Product is unavailable, redirect back with error
        header('location: single_product.php?product_id=' . $product_id . '&error=Product is currently unavailable');
        exit;
    }

    // Check if user_id exists in users table before inserting into cart
    $user_check = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
    $user_check->bind_param("i", $user_id);
    $user_check->execute();
    $user_check_result = $user_check->get_result();

    if($user_check_result->num_rows == 0) {
        // User does not exist, force logout and redirect to login
        session_destroy();
        header('location: login.php?message=Your account does not exist. Please login again.');
        exit;
    }

    // Check if product already in cart
    $check_stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $check_stmt->bind_param("ii", $user_id, $product_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if($result->num_rows > 0) {
        // Update quantity
        $update_stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?");
        $update_stmt->bind_param("iii", $quantity, $user_id, $product_id);
        $update_stmt->execute();
    } else {
        // Add new item
        $insert_stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $insert_stmt->execute();
    }

    header('location: cart.php?message=Product added to cart');
    exit;
}

include('layouts/header.php');
?>

<!-- Display error message if product is unavailable -->
<?php if(isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($_GET['error']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Single Product -->
<section class="container single-product my-5 pt-5">
    <div class="row mt-5">
        <?php while($row = $Product->fetch_assoc()){ ?>
            <div class="col-lg-5 col-md-6 col-sm-12">
                <img class="img-fluid w-100 pb-1" src="uploads/<?php echo $row['product_image']; ?>" id="mainImg"/>
                <div class="small-img-group">
                    <div class="small-img-col">
                        <img src="uploads/<?php echo $row['product_image']; ?>" width="100%" class="small-img"/>
                    </div>
                    <div class="small-img-col">
                        <img src="uploads/<?php echo $row['product_image2']; ?>" width="100%" class="small-img"/>
                    </div>
                    <div class="small-img-col">
                        <img src="uploads/<?php echo $row['product_image3']; ?>" width="100%" class="small-img"/>
                    </div>
                    <div class="small-img-col">
                        <img src="uploads/<?php echo $row['product_image4']; ?>" width="100%" class="small-img"/>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 col-md-12 col-12">
                <h6>BIKES</h6>
                <h3 class="py-4"><?php echo $row['product_name']; ?></h3>
                <h2>NPR-<?php echo $row['product_price']; ?></h2>
                
                <!-- Display availability status -->
                <div class="mb-3">
                    <span class="badge <?php echo isset($row['availability']) && $row['availability'] ? 'bg-success' : 'bg-danger'; ?> p-2">
                        <?php echo isset($row['availability']) && $row['availability'] ? 'Available' : 'Currently Unavailable'; ?>
                    </span>
                </div>
                
                <form method="POST" action="">
                    <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>"/>
                    <input type="hidden" name="product_image" value="<?php echo $row['product_image']; ?>"/>
                    <input type="hidden" name="product_name" value="<?php echo $row['product_name']; ?>"/>
                    <input type="hidden" name="product_price" value="<?php echo $row['product_price']; ?>"/>
                    <button class="buy-btn" type="submit" name="add_to_cart" <?php echo isset($row['availability']) && !$row['availability'] ? 'disabled' : ''; ?>>
                        <?php echo isset($row['availability']) && $row['availability'] ? 'Add to Cart' : 'Unavailable'; ?>
                    </button>
                </form>

                <h4 class="mt-5 mb-5">Product Details</h4>
                <span><?php echo $row['product_description']; ?></span>
            </div>
        <?php } ?>
    </div>
</section>

<!-- Related Products -->
<section id="related-products" class="my-5 pb-5">
    <div class="container text-center mt-5 py-5">
        <h3>Related Brand Bikes</h3>
        <hr class="mx-auto">
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla nec purus feugiat,</p>
    </div>
    <div class="row mx-auto container-fluid">
        <div class="product text-center col-lg-3 col-3 col-md-4 col-sm-12">
            <img class="img-fluid mb-3" src="assets/image/p5.jpg"/>
            <div class="star">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
            </div>
            <h5 class="p-name">Yamaha R1</h5>
            <h4 class="p-price">NPR120</h4>
            <button class="buy-btn">Buy Now</button>
        </div>
        <div class="product text-center col-lg-3 col-3 col-md-4 col-sm-12">
            <img class="img-fluid mb-3" src="assets/image/p6.jpg"/>
            <div class="star">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
            </div>
            <h5 class="p-name">Yamaha MT 125</h5>
            <h4 class="p-price">NPR-120</h4>
            <button class="buy-btn">Buy Now</button>
        </div>
        <div class="product text-center col-lg-3 col-3 col-md-4 col-sm-12">
            <img class="img-fluid mb-3" src="assets/image/p7.jpg"/>
            <div class="star">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
            </div>
            <h5 class="p-name">Yamaha-YZF-R6</h5>
            <h4 class="p-price">NPR-120</h4>
            <button class="buy-btn">Buy Now</button>
        </div>
        <div class="product text-center col-lg-3 col-3 col-md-4 col-sm-12">
            <img class="img-fluid mb-3" src="assets/image/p8.jpg"/>
            <div class="star">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
            </div>
            <h5 class="p-name">Yamaha-MT-03</h5>
            <h4 class="p-price">NPR-120</h4>
            <button class="buy-btn">Buy Now</button>
        </div>
    </div>
</section>

<script>
    var mainImg = document.getElementById("mainImg");
    var smallImg = document.getElementsByClassName("small-img");

    for(let i=0; i<4; i++){
        smallImg[i].onclick = function(){
            mainImg.src = smallImg[i].src;
        }
    }
</script>

<?php include('layouts/footer.php'); ?>