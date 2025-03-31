<?php
session_start();

// Check if already logged in
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: ../index.php");
    exit;
}

// Include database connection
require_once "../config/db.php";

$error = "";

// Process login form
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if(empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        // Prepare a select statement
        $sql = "SELECT admin_id, admin_name, admin_email, admin_password FROM admin WHERE admin_email = ?";
        
        if($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_email);
            $param_email = $email;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()) {
                // Store result
                $stmt->store_result();
                
                // Check if email exists
                if($stmt->num_rows == 1) {
                    // Bind result variables
                    $stmt->bind_result($user_id, $user_name, $user_email, $hashed_password);
                    
                    if($stmt->fetch()) {
                        // Verify password (using password_verify for hashed passwords)
                        if(password_verify($password, $hashed_password)) {
                            // Password is correct, start a new session
                            session_regenerate_id(true); // Security measure against session fixation
                            
                            // Store data in session variables
                            $_SESSION["admin_logged_in"] = true;
                            $_SESSION["user_id"] = $user_id;
                            $_SESSION["user_name"] = $user_name;
                            $_SESSION["user_email"] = $user_email;
                            
                            // Redirect to dashboard
                            header("location: ../index.php");
                            exit;
                        } else {
                            $error = "Invalid email or password";
                        }
                    }
                } else {
                    // Generic error message to avoid revealing if email exists
                    $error = "Invalid email or password";
                }
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
            }
            
            // Close statement
            $stmt->close();
        }
    }
    
    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Biku Rental</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-form {
            max-width: 400px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo h1 {
            font-weight: 700;
            color: #212529;
        }
        .container {
            max-width: 500px;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-form">
            <div class="login-logo">
                <h1>Biku Rental System</h1>
                <p class="text-muted">Admin Panel</p>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>