<?php
session_start();

if (isset($_POST['submit_otp'])) {
    $entered_otp = $_POST['otp'];
    
    if (time() <= $_SESSION['otp_expire'] && $entered_otp == $_SESSION['otp']) {
        $_SESSION['otp_verified'] = true;
        header("Location: reset_password.php");
    } else {
        $error = "Invalid or expired OTP.";
    }
}
?>

<?php include('layouts/header.php'); ?>

<section class="my-5 py-5">
  <div class="container text-center mt-3 pt-5">
    <h2>Verify OTP</h2>
    <hr class="mx-auto">
    <p class="text-danger"><?php if (isset($error)) echo $error; ?></p>
    <p class="text-success"><?php if (isset($_GET['message'])) echo $_GET['message']; ?></p>
  </div>
  <div class="mx-auto container">
    <form method="POST" action="verify_otp.php">
      <div class="form-group">
        <label>Enter OTP</label>
        <input type="text" class="form-control" name="otp" required />
      </div>
      <input type="submit" name="submit_otp" class="btn btn-primary" value="Verify OTP">
    </form>
  </div>
</section>

<?php include('layouts/footer.php'); ?>
