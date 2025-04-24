<?php

session_start();
if(isset($_POST['order_pay_btn'])){
    $order_status = $_POST['order_status'];
    $order_total_price = $_POST['order_total_price'];
    
}

?>

<?php

include('layouts/header.php');

?>

        <!-- Payment -->
        <section class="my-5 py-5">
            <div class="container text-center mt-3 pt-5">
                <h2 class="font-weight-bold">Payment</h2>
                <hr class="mx-auto">
            </div>
            <div class="mx-auto container text-center">

                <?php if(isset($_SESSION['total']) && $_SESSION['total'] !=0) {?>
                    <p>Total Payment: $<?php echo $_SESSION['total']; ?></p>
                    <form action="checkout.php" method="GET">
                        <input type="hidden" name="total" value="<?php echo $_SESSION['total']; ?>">
                        <input class="btn btn-primary" type="submit" value="Pay Now">
                    </form>
                <?php } else if(isset($_POST['order_status']) && $_POST['order_status'] == "Pending"){?>
                    <p>Total Payment: $ <?php echo $_POST['order_total_price']; ?></p>
                    <form action="checkout.php" method="GET">
                        <input type="hidden" name="total" value="<?php echo $_POST['order_total_price']; ?>">
                        <input class="btn btn-primary" type="submit" value="Pay Now">
                    </form>
                <?php } else { ?>

                    <p style="color:red;">You don't have an order. Your cart is empty</p>


                <?php } ?>


            

            </div>
        </section>


<?php 

include('layouts/footer.php');

?>





