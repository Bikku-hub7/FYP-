<?php
session_start();

// Validate form submission
if (!isset($_POST['submit']) || empty($_SESSION['cart'])) {
    $_SESSION['validate_msg'] = '<script>
        Swal.fire({
            icon: "error",
            title: "'.(empty($_SESSION['cart']) ? "Your cart is empty" : "Invalid form submission").'",
            showConfirmButton: false,
            timer: 1500
        });
    </script>';
    header("Location: ../../checkout.php");
    exit();
}

// Collect and sanitize data
$amount = floatval($_POST['inputAmount4'] ?? 0) * 100; // Convert to paisa
$purchase_order_id = htmlspecialchars($_POST['inputPurchasedOrderId4'] ?? '');
$purchase_order_name = htmlspecialchars($_POST['inputPurchasedOrderName'] ?? '');
$name = htmlspecialchars($_POST['inputName'] ?? '');
$email = filter_var($_POST['inputEmail'] ?? '', FILTER_SANITIZE_EMAIL);
$phone = preg_replace('/[^0-9]/', '', $_POST['inputPhone'] ?? '');
$city = htmlspecialchars($_POST['inputCity'] ?? '');
$address = htmlspecialchars($_POST['inputAddress'] ?? '');

// Store checkout data in session for later use
$_SESSION['checkout_data'] = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'city' => $city,
    'address' => $address,
    'cart' => $_SESSION['cart'],
    'total' => $amount / 100
];

// Validate inputs
$errors = [];

if ($amount <= 100) { // Minimum 1 rupee (100 paisa)
    $errors[] = "Amount must be at least Rs. 1";
}

if (empty($purchase_order_id)) {
    $errors[] = "Order ID is required";
}

if (empty(trim($name))) {
    $errors[] = "Name is required";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Valid email is required";
}

if (strlen($phone) != 10 || !is_numeric($phone)) {
    $errors[] = "Valid 10-digit phone number is required";
}

if (!empty($errors)) {
    $_SESSION['validate_msg'] = '<script>
        Swal.fire({
            icon: "error",
            title: "'.implode("\\n", $errors).'",
            showConfirmButton: false,
            timer: 3000
        });
    </script>';
    header("Location: ../../checkout.php");
    exit();
}

// Prepare Khalti payment request
$postFields = [
    "return_url" => (isset($_SERVER['HTTPS']) ? "https://" : "http://") . 
                  $_SERVER['HTTP_HOST'] . "/server/khalti-ePayment-gateway-main/payment-response.php",
    "website_url" => (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/",
    "amount" => $amount,
    "purchase_order_id" => $purchase_order_id,
    "purchase_order_name" => $purchase_order_name,
    "customer_info" => [
        "name" => $name,
        "email" => $email,
        "phone" => $phone
    ]
];

// Initiate cURL request
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/initiate/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($postFields),
    CURLOPT_HTTPHEADER => [
        'Authorization: Key 8e10987a17a747129ad50756a5e43de5', // Replace with your actual key
        'Content-Type: application/json',
    ],
    CURLOPT_TIMEOUT => 15, // 15 second timeout
    CURLOPT_SSL_VERIFYPEER => true // Always verify SSL certificate
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Handle cURL errors
if ($curlError) {
    error_log("Khalti Payment Error: " . $curlError);
    $_SESSION['validate_msg'] = '<script>
        Swal.fire({
            icon: "error",
            title: "Connection Error",
            text: "Could not connect to payment gateway. Please try again.",
            showConfirmButton: false,
            timer: 3000
        });
    </script>';
    header("Location: ../../checkout.php");
    exit();
}

// Handle HTTP errors
if ($httpCode !== 200) {
    error_log("Khalti Payment HTTP Error: " . $httpCode . " - " . $response);
    $_SESSION['validate_msg'] = '<script>
        Swal.fire({
            icon: "error",
            title: "Payment Error ('.$httpCode.')",
            text: "Please try again later",
            showConfirmButton: false,
            timer: 3000
        });
    </script>';
    header("Location: ../../checkout.php");
    exit();
}

// Process response
$responseData = json_decode($response, true);

if (isset($responseData['payment_url'])) {
    // Store transaction reference in session
    $_SESSION['transaction_reference'] = $responseData['pidx'] ?? null;
    header("Location: " . $responseData['payment_url']);
    exit();
} else {
    $errorMsg = $responseData['detail'] ?? ($responseData['error_key'] ?? 'Payment processing failed');
    error_log("Khalti Payment Error: " . $errorMsg);
    $_SESSION['validate_msg'] = '<script>
        Swal.fire({
            icon: "error",
            title: "Payment Error",
            text: "'.htmlspecialchars($errorMsg).'",
            showConfirmButton: false,
            timer: 3000
        });
    </script>';
    header("Location: ../../checkout.php");
    exit();
}
?>