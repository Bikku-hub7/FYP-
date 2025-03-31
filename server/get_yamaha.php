<?php

include('connection.php');

$stmt = $conn->prepare("SELECT * FROM products WHERE product_category ='Yamaha'  LIMIT 4");
$stmt->execute();
$yamaha_Products = $stmt->get_result();

?>