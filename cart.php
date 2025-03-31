<?php

session_start();

if(isset($_POST['add_to_cart'])){
  //if user has already added a product to cart
    if(isset($_SESSION['cart'])){
      $products_array_ids = array_column($_SESSION['cart'], 'product_id');
      //if the product has already been added to cart or not      
      if( !in_array($_POST['product_id'], $products_array_ids)){

        $product_id = $_POST['product_id'];

            $product_array = array(
              'product_id' => $_POST['product_id'],
              'product_name' => $_POST['product_name'],
              'product_price' => $_POST['product_price'],
              'product_image' => $_POST['product_image'],
              'product_quantity' => $_POST['product_quantity']
            );

            $_SESSION['cart'][$product_id] = $product_array;

        //product has alredy been added to cart
      }else{
        echo "<script>alert('Product was already added to cart')</script>";
      }




//if this is the first product
    }else{
      $product_id = $_POST['product_id'];
      $product_name = $_POST['product_name'];
      $product_price = $_POST['product_price'];
      $product_image = $_POST['product_image'];
      $product_quantity = $_POST['product_quantity'];

      $product_array = array(
                        'product_id' => $product_id,
                        'product_name' => $product_name,
                        'product_price' => $product_price,
                        'product_image' => $product_image,
                        'product_quantity' => $product_quantity
      );

      $_SESSION['cart'][$product_id] = $product_array;
    }

    //Calculate total price of cart
    calculateTotalCart();




//Remove product from cart
}else if(isset($_POST['remove_product'])){
    $product_id = $_POST['product_id'];
    unset($_SESSION['cart'][$product_id]);


    //calculate total price of cart
    calculateTotalCart();


//Edit quantity of product in cart    
}else if( isset($_POST['edit_quantity'])){
  //we get id and quantity of product from the form
    $product_id = $_POST['product_id'];
    $product_quantity = $_POST['product_quantity'];

    //we get the product array from the session
    $product_array = $_SESSION['cart'][$product_id];

    //we update the quantity of the product
    $product_array['product_quantity'] = $product_quantity;
    
    //Return array back to session
    $_SESSION['cart'][$product_id] = $product_array;

    //calculate total price of cart
    calculateTotalCart();


}else{
      // header('location:index.php');
}


//Calculate total price cart
function calculateTotalCart(){

  $total_price = 0;
  $total_quantity = 0;

  foreach($_SESSION['cart'] as $key => $product){
    $price = $product['product_price'];
    $quantity = $product['product_quantity'];
    $total_price += $price * $quantity;
    $total_quantity += $quantity;
  }

  if (empty($_SESSION['cart'])) {
    $total_price = 0;
    $total_quantity = 0;
  }

  $_SESSION['total'] = $total_price;
  $_SESSION['quantity'] = $total_quantity;
  
}


?>

include 'layouts/header.php';
<?php

include('layouts/header.php');

?>

      <!-- Cart -->
       <section class="cart container my-5 py-5">
        <div class="container mt-5">
          <h2 class="font-weight-bold">Your Cart</h2>
          <hr>
        </div>

        <table class="mt-5 pt-5">
          <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Total</th>
          </tr>

          <?php if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) { ?>
          <?php foreach($_SESSION['cart'] as $key => $value){ ?>

          <tr>
            <td>
              <div class="product-info">
                <img src="assets/image/<?php echo $value['product_image'];  ?>"/>
                <div>
                  <p><?php echo $value['product_name'];  ?></p>
                  <small><span>$</span><?php echo $value['product_price'];  ?></small>
                  <br>
                  <form method="POST" action="cart.php">
                    <input type="hidden" name="product_id" value="<?php echo $value['product_id'];  ?>">
                    <input type="submit" name="remove_product" class="remove-btn" value="Remove">

                  </form>
  
                </div>
              </div>
            </td>
            <td>
              <form method="POST" action="cart.php">
                <input type="hidden" name="product_id" value="<?php echo $value['product_id'];  ?>">
                <input type="number" name="product_quantity" value="<?php echo $value['product_quantity'];  ?>"/>
                <input type="submit" class="edit-btn" name="edit_quantity" value="Edit" >

              </form>
            </td>
            <td>
              <span>$</span>
              <span class="product-price"><?php echo $value['product_quantity'] * $value['product_price']; ?></span>
            </td>
          </tr>
          <?php } ?>
          <?php } else { ?>
          <tr>
            <td colspan="3">Your cart is empty.</td>
          </tr>
          <?php } ?> 

        </table>
        
        <div class="cart-total">
          <?php if (!empty($_SESSION['cart'])) { ?>
                <table>
                <tr>
                  <td>Total</td>
                  <td>$<?php echo $_SESSION['total']; ?></td>
                </tr>
                </table>
          <?php } ?>
        </div>
        <div class="checkout-container">
          <form method="POST" action="checkout.php">
            <!-- <input type="hidden" name="total" value="<?php echo $_SESSION['total']; ?>"> -->
            <input type="submit" class="btn checkout-btn" value="Checkout" name="checkout">
          </form>
        </div>



       </section>
 
<?php 

include('layouts/footer.php');

?>