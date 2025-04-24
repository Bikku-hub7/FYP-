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
    $user_name = trim($_POST['user_name']);
    $user_email = trim($_POST['user_email']);
    $user_password = trim($_POST['user_password']);
    $user_phone = isset($_POST['user_phone']) ? trim($_POST['user_phone']) : '';
    $user_address = isset($_POST['user_address']) ? trim($_POST['user_address']) : '';
    $user_city = isset($_POST['user_city']) ? trim($_POST['user_city']) : '';
    
    // Validate input
    $errors = [];
    
    if(empty($user_name)) {
        $errors[] = "Name is required";
    }
    
    if(empty($user_email)) {
        $errors[] = "Email is required";
    } elseif(!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $check_sql = "SELECT user_id FROM users WHERE user_email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $user_email);
        $stmt->execute();
        $stmt->store_result();
        
        if($stmt->num_rows > 0) {
            $errors[] = "Email already exists";
        }
        
        $stmt->close();
    }
    
    if(empty($user_password)) {
        $errors[] = "Password is required";
    } elseif(strlen($user_password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    // If no errors, insert user into database
    if(empty($errors)) {
        // In a production environment, you should hash the password
        // $hashed_password = password_hash($user_password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (user_name, user_email, user_password, user_phone, user_address, user_city) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $user_name, $user_email, $user_password, $user_phone, $user_address, $user_city);
        
        if($stmt->execute()) {
            $success = "User added successfully!";
            
            // Clear form data
            $user_name = $user_email = $user_password = $user_phone = $user_address = $user_city = "";
        } else {
            $error = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        $error = implode("<br>", $errors);
    }
}

// Include header
include "includes/header.php";

// Include sidebar
include "includes/sidebar.php";
?>

<div class="col-md-9 col-lg-10 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Add New User</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="users.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Users
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
            <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="user_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="user_name" name="user_name" value="<?php echo isset($user_name) ? $user_name : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="user_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="user_email" name="user_email" value="<?php echo isset($user_email) ? $user_email : ''; ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="user_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="user_password" name="user_password" required>
                        <small class="text-muted">Password must be at least 6 characters</small>
                    </div>
                    <div class="col-md-6">
                        <label for="user_phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="user_phone" name="user_phone" value="<?php echo isset($user_phone) ? $user_phone : ''; ?>">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="user_city" class="form-label">City</label>
                        <input type="text" class="form-control" id="user_city" name="user_city" value="<?php echo isset($user_city) ? $user_city : ''; ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="user_address" class="form-label">Address</label>
                        <textarea class="form-control" id="user_address" name="user_address" rows="3"><?php echo isset($user_address) ? $user_address : ''; ?></textarea>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
include "includes/footer.php";

// Close connection
$conn->close();
?>
