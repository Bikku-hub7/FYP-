<?php

include('connection.php');

$stmt = $conn->prepare("SELECT * FROM products WHERE product_category ='Aprilia'  LIMIT 4");
$stmt->execute();
$aprilia_Products = $stmt->get_result();

?>