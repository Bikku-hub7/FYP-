<?php

session_start();
include('server/connection.php');

//if user have already register take user to account page
if(isset($_SESSION['logged_in'])){
  header('location: account.php');
  exit;
}

if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmpassword = $_POST['confirmpassword'];
    
    //if password and confirm password does not match
  if($password !== $confirmpassword){
        header('location: register.php?error=Password does not match');
        exit();
    //if password is less than 8 characters
  }else if(strlen($password) < 8){
        header('location: register.php?error=Password must be at least 8 characters');
        exit();
    //if password does not meet criteria: at least one symbol, one number, one capital, one small letter and no spaces
    } else if(!preg_match('/^(?=\S+$)(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/', $password)){
        header('location: register.php?error=Password must include one symbol, one number, one capital letter, one small letter and have no spaces');
        exit();
  } else {
    //check wether the email already exists
    $stmt1 = $conn->prepare("SELECT count(*) FROM users WHERE user_email = ?");
    $stmt1->bind_param("s", $email);
    $stmt1->execute();
    $stmt1->bind_result($num_rows);
    $stmt1->store_result();
    $stmt1->fetch();

    //if there is a user already with the email
    if($num_rows != 0){
        header('location: register.php?error=User with this email already exists');
    }else{
    //Create a new user
    $stmt = $conn->prepare("INSERT INTO users (user_name, user_email, user_password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email,$password);

    //if the user is successfully created
    if($stmt->execute()){
      $user_id = $stmt->insert_id;
      $_SESSION['user_id'] = $user_id;
      $_SESSION['user_email'] = $email;
      $_SESSION['user_name'] = $name;
      $_SESSION['logged_in'] = true;
      header('location: account.php?register_sucess=You have successfully registered');
      //account could not be created
  }else{
      header('location: register.php?error=Failed to register or create account');
    }
}
}
}

?>

<?php

include('layouts/header.php');

?>

          <!--Register-->
        <section class="my-5 py-5">
            <div class="container text-center mt-3 pt-5">
                <h2 class="font-weight-bold">Register</h2>
                <hr class="mx-auto">
            </div>
            <div class="mx-auto container">
                <form id="register-form" method="POST" action="register.php">
                  <p style="color:red;"><?php if(isset($_GET['error'])) {echo $_GET['error']; }?></p>
                    <div class = "form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" id="register-name" name="name" placeholder="Name" required/>
                    </div>
                    <div class = "form-group">
                        <label>Email</label>
                        <input type="text" class="form-control" id="register-email" name="email" placeholder="Email" required/>
                    </div>
                    <div class = "form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" id="register-password" name="password" placeholder="Password" required/>
                    </div>
                    <div class = "form-group">
                        <label>Confirm Password</label>
                        <input type="password" class="form-control" id="register-confirm-password" name="confirmpassword" placeholder="Confirm Password" required/>
                    </div>
                    <div class = "form-group">
                        <input type="Submit" class="btn" id="register-btn" name="register" value="Register"/>
                    </div>
                    <div class = "form-group">
                       <a id="login-url" href="login.php" class="btn">Do have an account? Login</a>
                    </div>
                </form>
            </div>
        </section>



<?php 

include('layouts/footer.php');

?>