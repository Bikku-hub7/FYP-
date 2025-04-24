<?php
session_start();
include('server/connection.php');

if (!isset($_SESSION['otp_verified']) || !isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit;
}

if (isset($_POST['reset_password'])) {
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_SESSION['reset_email'];

    $stmt = $conn->prepare("UPDATE users SET user_password = ? WHERE user_email = ?");
    $stmt->bind_param("ss", $password, $email);

    if ($stmt->execute()) {
        session_destroy();
        header("Location: login.php?message=Password reset successfully");
    } else {
        $error = "Failed to reset password.";
    }
}
?>

<?php include('layouts/header.php'); ?>

<section class="my-5 py-5">
  <div class="container text-center mt-3 pt-5">
    <h2>Reset Password</h2>
    <hr class="mx-auto">
    <p class="text-danger"><?php if (isset($error)) echo $error; ?></p>
  </div>
  <div class="mx-auto container">
    <form method="POST" action="reset_password.php">
      <div class="form-group">
        <label>New Password</label>
        <input type="password" class="form-control" name="password" required />
      </div>
      <input type="submit" name="reset_password" class="btn btn-primary" value="Reset Password">
    </form>
  </div>
</section>

<?php include('layouts/footer.php'); ?>
