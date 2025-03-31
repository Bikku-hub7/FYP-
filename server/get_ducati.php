<?php

include('connection.php');

$stmt = $conn->prepare("SELECT * FROM products WHERE product_category ='Ducati'  LIMIT 4");
$stmt->execute();
$ducati_Products = $stmt->get_result();

?>