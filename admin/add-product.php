<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: auth/login.php");
    exit;
}

// Include database connection
require_once "config/db.php";

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $product_category = $_POST['product_category'];
    $product_description = $_POST['product_description'];
    $product_price = $_POST['product_price'];
    $product_color = $_POST['product_color'];
    $product_special_offer = isset($_POST['product_special_offer']) ? $_POST['product_special_offer'] : 0;
    
    // Handle file upload
    $product_image = '';
    $upload_dir = '../uploads/'; // Changed to root uploads directory
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $filename = $_FILES['product_image']['name'];
        $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if(in_array(strtolower($file_ext), $allowed)) {
            $new_filename = uniqid() . '.' . $file_ext;
            if(move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_dir . $new_filename)) {
                $product_image = $new_filename;
            } else {
                $error = "Failed to upload image";
            }
        } else {
            $error = "Invalid file type. Only JPG, JPEG, PNG and GIF are allowed.";
        }
    } else {
        $product_image = 'placeholder.jpg'; // Default image
    }
    
    // Use the same image for all image fields for simplicity
    $product_image2 = $product_image;
    $product_image3 = $product_image;
    $product_image4 = $product_image;
    
    if(!isset($error)) {
        // Insert product into database
        $sql = "INSERT INTO products (product_name, product_category, product_description, product_image, product_image2, product_image3, product_image4, product_price, product_special_offer, product_color) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssdis", $product_name, $product_category, $product_description, $product_image, $product_image2, $product_image3, $product_image4, $product_price, $product_special_offer, $product_color);
        
        if($stmt->execute()) {
            $success = "Product added successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}

// Get all categories for dropdown
$categories_sql = "SELECT DISTINCT product_category FROM products ORDER BY product_category";
$categories_result = $conn->query($categories_sql);

// Include header
include "includes/header.php";

// Include sidebar
include "includes/sidebar.php";
?>

<div class="col-md-9 col-lg-10 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Add New Product</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="products.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Products
            </a>
        </div>
    </div>

    <?php if(isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <?php if(isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Product Information</h6>
        </div>
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="product_name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="product_name" name="product_name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="product_category" class="form-label">Category</label>
                        <select class="form-select" id="product_category" name="product_category">
                            <?php while($category = $categories_result->fetch_assoc()): ?>
                            <option value="<?php echo $category['product_category']; ?>">
                                <?php echo $category['product_category']; ?>
                            </option>
                            <?php endwhile; ?>
                            <option value="new">Add New Category</option>
                        </select>
                        <div id="new_category_div" class="mt-2" style="display: none;">
                            <input type="text" class="form-control" id="new_category" name="new_category" placeholder="Enter new category">
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="product_description" class="form-label">Description</label>
                    <textarea class="form-control" id="product_description" name="product_description" rows="3"></textarea>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="product_price" class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="product_price" name="product_price" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="product_color" class="form-label">Color</label>
                        <input type="text" class="form-control" id="product_color" name="product_color">
                    </div>
                    <div class="col-md-4">
                        <label for="product_special_offer" class="form-label">Special Offer (%)</label>
                        <input type="number" class="form-control" id="product_special_offer" name="product_special_offer" min="0" max="100" value="0">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="product_image" class="form-label">Product Image</label>
                    <input class="form-control" type="file" id="product_image" name="product_image">
                    <small class="text-muted">Recommended size: 800x600 pixels</small>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('product_category').addEventListener('change', function() {
    if (this.value === 'new') {
        document.getElementById('new_category_div').style.display = 'block';
    } else {
        document.getElementById('new_category_div').style.display = 'none';
    }
});
</script>

<?php
// Include footer
include "includes/footer.php";

// Close connection
$conn->close();
?>
