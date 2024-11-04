<?php
// Monnify Payment Integration Example

// Set your Monnify API credentials
define('API_KEY', getenv('MK_TEST_8V9KU01QBD')); // Use environment variables for sensitive data
define('API_SECRET', getenv('KQMM903MCCDYSWVR0381N2GLX1LZVM5N'));
define('BASE_URL', 'https://api.monify.com/api/v1/'); // Corrected URL

// Function to get the authentication token
function getAuthToken() {
    $url = BASE_URL . 'auth/login';
    $headers = [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode(API_KEY . ':' . API_SECRET)
    ];

    $response = makeRequest($url, 'POST', [], $headers);

    // Log the response for debugging
    if ($response->responseCode !== '00') {
        echo "Failed to obtain token: " . $response->message;
        return null;
    }

    return $response->responseBody->accessToken ?? null;
}


// Function to make API requests
function makeRequest($url, $method, $data = [], $headers = []) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    if (!empty($data)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        return (object)[
            'responseCode' => '99',
            'message' => 'cURL Error: ' . curl_error($ch)
        ];
    }

    curl_close($ch);
    return json_decode($response);
}

// Function to create a payment
function createPayment($token) {
    $url = BASE_URL . 'transactions/create/inline';
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ];

    // Sample data - make sure to validate inputs in a real application
    $data = [
        "amount" => 1000, // Amount in kobo (i.e., 1000 kobo = 10.00)
        "currency" => "NGN",
        "paymentMethod" => "CARD",
        "email" => "customer@example.com",
        "fullName" => "Customer Name",
        "txRef" => uniqid(), // Unique transaction reference
        "redirectUrl" => "https://yourwebsite.com/redirect", // URL to redirect after payment
        "orderId" => "ORDER123", // Your order ID
    ];

    $response = makeRequest($url, 'POST', $data, $headers);
    return $response;
}

// Main execution
$token = getAuthToken();
if ($token) {
    $paymentResponse = createPayment($token);
    if ($paymentResponse->responseCode === '00') {
        // Redirect the user to the payment link
        header('Location: ' . $paymentResponse->responseBody->paymentUrl);
        exit();
    } else {
        echo "Error creating payment: " . $paymentResponse->message;
    }
} else {
    echo "Error obtaining authentication token.";
}



?>