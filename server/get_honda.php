<?php

include('connection.php');

$stmt = $conn->prepare("SELECT * FROM products WHERE product_category ='Honda'  LIMIT 4");
$stmt->execute();
$honda_Products = $stmt->get_result();

?>