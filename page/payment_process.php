<?php
// Create Stripe Checkout Session and return sessionId for redirect.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /login/login.php?error=Please login to proceed');
    exit;
}

require_once __DIR__ . '/../sb_base.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/product_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cart_view.php');
    exit;
}

$selectedItemsRaw = $_POST['selected_items'] ?? '';
$selectedProductIds = json_decode($selectedItemsRaw, true);
$fullName = $_POST['full_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';

if (empty($selectedProductIds) || !is_array($selectedProductIds)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No items selected.']);
    exit;
}

// Build line items from DB to avoid client tampering
$cartUserId = cart_user_id();
$lineItems = [];
$cartSummary = [];
$totalAmount = 0;

foreach ($selectedProductIds as $productId) {
    $cartStmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?");
    $cartStmt->execute([$cartUserId, $productId]);
    $cartItem = $cartStmt->fetch();
    if (!$cartItem) {
        continue;
    }

    $product = get_product_by_id($productId);
    if (!$product) {
        continue;
    }

    $quantity = (int) $cartItem['quantity'];
    $unitPrice = (float) $product['price'];
    $lineTotal = $unitPrice * $quantity;
    $totalAmount += $lineTotal;

    $lineItems[] = [
        'price_data' => [
            'currency' => 'myr',
            'product_data' => ['name' => $product['title']],
            'unit_amount' => (int) round($unitPrice * 100),
        ],
        'quantity' => $quantity,
    ];

    $cartSummary[] = [
        'product_id' => $productId,
        'title' => $product['title'],
        'price' => $unitPrice,
        'quantity' => $quantity,
    ];
}

if (empty($lineItems)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No valid items found.']);
    exit;
}

// Build absolute URLs for redirect
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $scheme . '://' . $host;
$successUrl = $baseUrl . '/page/payment_success.php?session_id={CHECKOUT_SESSION_ID}';
$cancelUrl = $baseUrl . '/page/cart_view.php?cancel=1';

// Flatten payload for Stripe Checkout (without stripe-php)
$payload = [
    'mode' => 'payment',
    'payment_method_types[0]' => 'card',
    'success_url' => $successUrl,
    'cancel_url' => $cancelUrl,
    'customer_email' => $email,
];

foreach ($lineItems as $index => $item) {
    $payload["line_items[$index][price_data][currency]"] = $item['price_data']['currency'];
    $payload["line_items[$index][price_data][product_data][name]"] = $item['price_data']['product_data']['name'];
    $payload["line_items[$index][price_data][unit_amount]"] = $item['price_data']['unit_amount'];
    $payload["line_items[$index][quantity]"] = $item['quantity'];
}

// Optional metadata
$payload['metadata[user_id]'] = (string) $_SESSION['user_id'];

// Create Stripe Checkout Session via cURL
$ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($payload),
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_USERPWD => $stripeSecretKey . ':',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

header('Content-Type: application/json');

if ($response === false || $httpCode >= 300) {
    error_log('Stripe session creation failed: ' . $curlErr . ' Response: ' . $response);
    echo json_encode(['error' => 'Unable to create Stripe Checkout Session. Please try again.']);
    exit;
}

$sessionData = json_decode($response, true);
if (empty($sessionData['id'])) {
    error_log('Stripe session missing id: ' . $response);
    echo json_encode(['error' => 'Invalid response from payment gateway.']);
    exit;
}

// Save checkout context for success page to finalize order
$_SESSION['pending_checkout'] = [
    'session_id' => $sessionData['id'],
    'selected_product_ids' => $selectedProductIds,
    'full_name' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'address' => $address,
    'total_amount' => $totalAmount,
];

echo json_encode(['sessionId' => $sessionData['id']]);
exit;
