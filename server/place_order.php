<?php

session_start();
include('connection.php');

//if user is not logged in
if(!isset($_SESSION['logged_in'])){
    header('Location: ../checkout.php');
    exit;
    
//if user is logged in    
}else{

        if(isset($_POST['place_order'])){


            //Get user info and store in database
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $city = $_POST['city'];
            $address = $_POST['address'];
            $order_cost = $_SESSION['total'];
            $order_status = "Pending";
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
            $order_date = date("Y-m-d H:i:s");

            //Store order and its info in database
            $stmt = $conn->prepare("INSERT INTO orders (order_cost, order_status, user_id, user_phone, user_city, user_address, order_date) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param("isiisss", $order_cost, $order_status, $user_id, $phone, $city, $address, $order_date);
            $stmt_status = $stmt->execute();
            
            if(!$stmt_status){
                header('Location: index.php');
                exit;
            }

            $order_id = $stmt->insert_id;


            //Get products from cart from session
            foreach($_SESSION['cart'] as $key => $value){
                
                $product = $_SESSION['cart'][$key];
                $product_id = $product['product_id'];
                $product_name = $product['product_name'];
                $product_image = $product['product_image'];
                $product_price = $product['product_price'];
                $product_quantity = $product['product_quantity'];

                //Store each single item in order_items in database
                $stmt1 = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_image, product_price, product_quantity, user_id, order_date) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt1->bind_param("iissiiis", $order_id, $product_id, $product_name, $product_image, $product_price, $product_quantity, $user_id, $order_date);    
                $stmt1->execute();            

            }

            // Remove everything from cart --> afetr payment is done
            // unset($_SESSION['cart']);


            // Inform user about the status of the order
            header('Location: ../payment.php?order_status= Order Placed Successfully');

        }else{
            header('Location: ../checkout.php');
        }



}

?>