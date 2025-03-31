<?php

session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biku Bike Rental System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400..700&display=swap" rel="stylesheet">

    
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Navbar -->

    <nav class="navbar navbar-expand-lg navbar-light bg-white py-3 fixed-top">
        <div class="container-fluid">
          <img class="logo" src="assets/image/logo.png"/>
          <h2 class="brand">Biku Bike Rentals</h2>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse nav-buttons" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

              <li class="nav-item">
                <a class="nav-link" href="index.php">Home</a>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="about.php">About Us</a>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="bikeslist.php">Bikes List</a>
              </li>

              <li class="nav-item">
                <a class="nav-link" href="contact.php">Contact Us</a>
              </li>

              
              <li class="nav-item">
               <a href="cart.php"><i class="fas fa-shopping-bag">
                <?php if(isset($_SESSION['quantity']) && $_SESSION['quantity'] !=0) { ?>
                  <span class="cart-quantity"> <?php echo $_SESSION['quantity']; ?> </span>
                  <?php } ?>
              </i>
            </a>
                <a href="account.php"><i class="fas fa-user"></i></a>
              </li>

            </ul>
          </div>
        </div>
      </nav>