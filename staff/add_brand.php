<?php
session_start();
require_once 'config/db.php';

// Check if staff is logged in
if(!isset($_SESSION['staff_id'])) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if(isset($_POST['add_brand'])) {
    $brand_name = trim($_POST['brand_name']);
    
    // Validate input
    if(empty($brand_name)) {
        $errorMsg = "Brand name cannot be empty";
    } else {
        // Check if brand already exists in the brands table
        $checkQuery = "SELECT COUNT(*) as count FROM products WHERE product_category = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("s", $brand_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['count'];
        
        if($count > 0) {
            $errorMsg = "Brand '$brand_name' already exists";
        } else {
            // Insert the new brand into the brands table
            $insertSql = "INSERT INTO products (product_category) VALUES (?)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("s", $brand_name);
            
            if($stmt->execute()) {
                $_SESSION['success_msg'] = "Brand '$brand_name' added successfully!";
                header("Location: brands.php");
                exit();
            } else {
                $errorMsg = "Error adding brand: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Brand - Biku Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
        }
        .sidebar .nav-link:hover {
            color: white;
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Add New Brand</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="brands.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Brands
                        </a>
                    </div>
                </div>
                
                <?php if(isset($errorMsg)): ?>
                    <div class="alert alert-danger"><?php echo $errorMsg; ?></div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Brand Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="brand_name" class="form-label">Brand Name</label>
                                <input type="text" class="form-control" id="brand_name" name="brand_name" required>
                                <div class="form-text">Enter the name of the motorcycle brand (e.g., Honda, Yamaha, Ducati).</div>
                            </div>
                            
                            <button type="submit" name="add_brand" class="btn btn-primary">Add Brand</button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>