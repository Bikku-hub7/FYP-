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
    $staff_name = trim($_POST['staff_name']);
    $staff_email = trim($_POST['staff_email']);
    $staff_password = trim($_POST['staff_password']);
    $staff_phone = isset($_POST['staff_phone']) ? trim($_POST['staff_phone']) : '';
    $staff_address = $_POST['staff_address'];
    
    // Validate input
    $errors = [];
    
    if(empty($staff_name)) {
        $errors[] = "Name is required";
    }
    
    if(empty($staff_email)) {
        $errors[] = "Email is required";
    } elseif(!filter_var($staff_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $check_sql = "SELECT staff_id FROM staff WHERE staff_email = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $staff_email);
        $stmt->execute();
        $stmt->store_result();
        
        if($stmt->num_rows > 0) {
            $errors[] = "Email already exists";
        }
        
        $stmt->close();
    }
    
    if(empty($staff_password)) {
        $errors[] = "Password is required";
    } elseif(strlen($staff_password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if(empty($staff_address)) {
        $errors[] = "Address is required";
    }
    
    // If no errors, insert staff into database
    if(empty($errors)) {
        // In a production environment, you should hash the password
        // $hashed_password = password_hash($staff_password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO staff (staff_name, staff_email, staff_password, staff_phone, staff_address) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $staff_name, $staff_email, $staff_password, $staff_phone, $staff_address);
        
        if($stmt->execute()) {
            $success = "Staff member added successfully!";
            
            // Clear form data
            $staff_name = $staff_email = $staff_password = $staff_phone = $staff_address = '';
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
        <h1 class="h2">Add New Staff</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="staff.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Staff
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
            <h6 class="m-0 font-weight-bold text-primary">Staff Information</h6>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="staff_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="staff_name" name="staff_name" value="<?php echo isset($staff_name) ? $staff_name : ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="staff_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="staff_email" name="staff_email" value="<?php echo isset($staff_email) ? $staff_email : ''; ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="staff_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="staff_password" name="staff_password" required>
                        <small class="text-muted">Password must be at least 6 characters</small>
                    </div>
                    <div class="col-md-6">
                        <label for="staff_phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="staff_phone" name="staff_phone" value="<?php echo isset($staff_phone) ? $staff_phone : ''; ?>">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="staff_address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="staff_address" name="staff_address" value="<?php echo isset($staff_address) ? $staff_address : ''; ?>" required>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                    <button type="submit" class="btn btn-primary">Add Staff</button>
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
