<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: auth/login.php");
    exit;
}

// Include database connection
require_once "config/db.php";

// Check if user ID is provided
if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$user_id = $_GET['id'];

// Fetch user details
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows == 0) {
    header("Location: users.php");
    exit;
}

$user = $result->fetch_assoc();

// Handle user update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    
    // Optional password update
    $password = $_POST['password'];
    $hashed_password = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

    if (!empty($password)) {
        $update_sql = "UPDATE users SET user_name = ?, user_email = ?, user_password = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
    } else {
        $update_sql = "UPDATE users SET user_name = ?, user_email = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ssi", $name, $email, $user_id);
    }

    if($update_stmt->execute()) {
        $status_message = "User updated successfully!";
        $status_type = "success";
        
        // Refresh user data after update
        $user['user_name'] = $name;
        $user['user_email'] = $email;
    } else {
        $status_message = "Error updating user: " . $conn->error;
        $status_type = "danger";
    }

    $update_stmt->close();
}

// Include header
include "includes/header.php";

// Include sidebar
include "includes/sidebar.php";
?>

<div class="col-md-9 col-lg-10 main-content">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Edit User</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="users.php" class="btn btn-sm btn-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Users
            </a>
        </div>
    </div>

    <?php if(isset($status_message)): ?>
    <div class="alert alert-<?php echo $status_type; ?> alert-dismissible fade show" role="alert">
        <?php echo $status_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($user['user_name']); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['user_email']); ?>" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">New Password (optional)</label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Leave blank to keep current password">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm new password">
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Update User
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
include "includes/footer.php";

// Close statements and connection
$stmt->close();
$conn->close();
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('confirm_password');

    confirmPasswordField.addEventListener('input', function() {
        if (passwordField.value !== confirmPasswordField.value) {
            confirmPasswordField.setCustomValidity('Passwords do not match');
        } else {
            confirmPasswordField.setCustomValidity('');
        }
    });
});
</script>