<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: auth/login.php");
    exit;
}

// Include database connection
require_once "config/db.php";

// Check if staff ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: staff.php");
    exit;
}

$staff_id = $_GET['id'];

// Get staff details
$staff_sql = "SELECT * FROM staff WHERE staff_id = ?";
$stmt = $conn->prepare($staff_sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$staff_result = $stmt->get_result();

if($staff_result->num_rows == 0) {
    header("Location: staff.php");
    exit;
}

$staff = $staff_result->fetch_assoc();
$stmt->close();

// Process form submission
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_name = trim($_POST['staff_name']);
    $staff_email = trim($_POST['staff_email']);
    $staff_phone = isset($_POST['staff_phone']) ? trim($_POST['staff_phone']) : '';
    $staff_position = trim($_POST['staff_position']);
    $staff_status = $_POST['staff_status'];
    $new_password = trim($_POST['new_password']);
    
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
        // Check if email already exists (excluding current staff)
        $check_sql = "SELECT staff_id FROM staff WHERE staff_email = ? AND staff_id != ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("si", $staff_email, $staff_id);
        $stmt->execute();
        $stmt->store_result();
        
        if($stmt->num_rows > 0) {
            $errors[] = "Email already exists";
        }
        
        $stmt->close();
    }
    
    if(empty($staff_position)) {
        $errors[] = "Position is required";
    }
    
    // If no errors, update staff in database
    if(empty($errors)) {
        // Check if password should be updated
        if(!empty($new_password)) {
            // In a production environment, you should hash the password
            // $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $sql = "UPDATE staff SET staff_name = ?, staff_email = ?, staff_password = ?, staff_phone = ?, staff_position = ?, staff_status = ? WHERE staff_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $staff_name, $staff_email, $new_password, $staff_phone, $staff_position, $staff_status, $staff_id);
        } else {
            // Don't update password
            $sql = "UPDATE staff SET staff_name = ?, staff_email = ?, staff_phone = ?, staff_position = ?, staff_status = ? WHERE staff_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $staff_name, $staff_email, $staff_phone, $staff_position, $staff_status, $staff_id);
        }
        
        if($stmt->execute()) {
            $success = "Staff member updated successfully!";
            
            // Refresh staff data
            $staff_sql = "SELECT * FROM staff WHERE staff_id = ?";
            $stmt = $conn->prepare($staff_sql);
            $stmt->bind_param("i", $staff_id);
            $stmt->execute();
            $staff_result = $stmt->get_result();
            $staff = $staff_result->fetch_assoc();
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
        <h1 class="h2">Edit Staff</h1>
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
                        <input type="text" class="form-control" id="staff_name" name="staff_name" value="<?php echo $staff['staff_name']; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="staff_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="staff_email" name="staff_email" value="<?php echo $staff['staff_email']; ?>" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        <small class="text-muted">Leave blank to keep current password</small>
                    </div>
                    <div class="col-md-6">
                        <label for="staff_phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="staff_phone" name="staff_phone" value="<?php echo $staff['staff_phone']; ?>">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="staff_position" class="form-label">Position</label>
                        <input type="text" class="form-control" id="staff_position" name="staff_position" value="<?php echo $staff['staff_position']; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="staff_status" class="form-label">Status</label>
                        <select class="form-select" id="staff_status" name="staff_status">
                            <option value="active" <?php echo ($staff['staff_status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($staff['staff_status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                    <button type="submit" class="btn btn-primary">Update Staff</button>
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
