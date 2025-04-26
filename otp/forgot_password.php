<?php
session_start();
include('../server/connection.php');

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';
require '../PHPMailer-master/src/Exception.php';

if (isset($_POST['submit_email'])) {
    $email = $_POST['email'];

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['reset_email'] = $email;
        $_SESSION['otp_expire'] = time() + 300;

        $mail = new PHPMailer(true);

        try {
            // SMTP settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'np03cs4s230163@heraldcollege.edu.np';       // replace with your Gmail
            $mail->Password = 'molz jglm dojv bnsw';          // replace with Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('np03cs4s230163@heraldcollege.edu.np', 'bike rental');
            $mail->addAddress($email);
            $mail->Subject = 'Your OTP Code';
            $mail->Body = "Your OTP is $otp. It will expire in 5 minutes.";

            $mail->send();
            header("Location: verify_otp.php?message=OTP Sent to your email");
            exit;
        } catch (Exception $e) {
            $error = "Mailer Error: " . $mail->ErrorInfo;
        }
    } else {
        $error = "Email not registered.";
    }
}
?>

<?php include('../layouts/header.php'); ?>

<section class="my-5 py-5">
  <div class="container text-center mt-3 pt-5">
    <h2>Forgot Password</h2>
    <hr class="mx-auto">
    <p class="text-danger"><?php if (isset($error)) echo $error; ?></p>
  </div>
  <div class="mx-auto container">
    <form method="POST" action=" forgot_password.php">
      <div class="form-group">
        <label>Enter your email</label>
        <input type="email" class="form-control" name="email" required />
      </div>
      <input type="submit" name="submit_email" class="btn btn-primary" value="Send OTP">
    </form>
  </div>
</section>

<?php include('../layouts/footer.php'); ?>
