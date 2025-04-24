<?php
session_start();

// Verify payment with Khalti when returning from payment gateway
if (isset($_GET['pidx'])) {
    $pidx = $_GET['pidx'];
    
    // Initiate cURL to verify payment
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://a.khalti.com/api/v2/epayment/lookup/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['pidx' => $pidx]),
        CURLOPT_HTTPHEADER => [
            'Authorization: Key 8e10987a17a747129ad50756a5e43de5',  
            'Content-Type: application/json',
        ],
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $paymentData = json_decode($response, true);
        
        if ($paymentData['status'] === 'Completed') {
            // Payment successful - store order information
            $_SESSION['order_details'] = [
                'transaction_id' => $pidx,
                'amount' => $paymentData['total_amount'] / 100, // Convert back to rupees
                'date' => $paymentData['created_at'],
                'products' => $_SESSION['cart'],
                'customer' => [
                    'name' => $_SESSION['checkout_data']['name'] ?? '',
                    'email' => $_SESSION['checkout_data']['email'] ?? '',
                    'phone' => $_SESSION['checkout_data']['phone'] ?? '',
                    'address' => $_SESSION['checkout_data']['address'] ?? ''
                ]
            ];
            
            // Clear cart and temporary data
            unset($_SESSION['cart']);
            unset($_SESSION['checkout_data']);
            
            // Redirect to success page
            header("Location: payment-success.php");
            exit();
        }
    }
}

// If payment verification fails
$_SESSION['validate_msg'] = '<script>
    Swal.fire({
        icon: "error",
        title: "Payment Verification Failed",
        text: "We couldn\'t verify your payment. Please contact support with transaction ID: '.($_GET['pidx'] ?? 'N/A').'",
        showConfirmButton: true
    });
</script>';
header("Location: ../../checkout.php");
exit();
?>