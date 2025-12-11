<?php
// Create Stripe Checkout Session and return sessionId for redirect.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login/login.php?error=Please login to proceed');
    exit;
}

require_once __DIR__ . '/../sb_base.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/product_functions.php';

// Only allow POST requests
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

$discountAmount = floatval($_POST['discount_amount'] ?? 0);
$discountCode = $_POST['discount_code_used'] ?? '';

$shippingFee = floatval($_POST['shipping_fee'] ?? 0);   // ⭐ Added: Shipping fee

if (empty($selectedProductIds) || !is_array($selectedProductIds)) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No items selected.']);
    exit;
}

// Calculate total & generate metadata items
$cartUserId = cart_user_id();
$totalAmount = 0;
$lineItemsForMetadata = [];

foreach ($selectedProductIds as $productId) {
    $cartStmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?");
    $cartStmt->execute([$cartUserId, $productId]);
    $cartItem = $cartStmt->fetch();
    if (!$cartItem) continue;

    $product = get_product_by_id($productId);
    if (!$product) continue;

    $quantity = (int) $cartItem['quantity'];
    $unitPrice = (float) $product['price'];
    $lineTotal = $unitPrice * $quantity;

    $totalAmount += $lineTotal;

    $lineItemsForMetadata[] = [
        'id' => $productId,
        'title' => $product['title'],
        'quantity' => $quantity,
        'unit_price' => $unitPrice
    ];
}

if ($totalAmount <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid cart total.']);
    exit;
}

// ⭐ Final amount after discount + shipping fee
$finalAmount = $totalAmount - $discountAmount + $shippingFee;
$finalAmountCents = (int) round($finalAmount * 100);
$finalAmountCents = max(0, $finalAmountCents); // Prevent negative

// Stripe Checkout payload
$payload = [
    'mode' => 'payment',
    'payment_method_types[0]' => 'card',
    'success_url' => (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/page/payment_success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/page/cart_view.php?cancel=1',
    'customer_email' => $email,

    // Stripe only needs 1 line item (total amount)
    'line_items[0][price_data][currency]' => 'myr',
    'line_items[0][price_data][product_data][name]' => 'Order Total',
    'line_items[0][price_data][unit_amount]' => $finalAmountCents,
    'line_items[0][quantity]' => 1,

    // metadata stores details, discount, shipping
    'metadata[user_id]' => (string) $_SESSION['user_id'],
    'metadata[discount_code]' => $discountCode,
    'metadata[discount_amount]' => $discountAmount,
    'metadata[shipping_fee]' => $shippingFee,
    'metadata[items]' => json_encode($lineItemsForMetadata),
];

// Create Stripe Session
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

// Save pending_checkout session data
$_SESSION['pending_checkout'] = [
    'session_id' => $sessionData['id'],
    'selected_product_ids' => $selectedProductIds,
    'full_name' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'address' => $address,
    'total_amount' => $totalAmount,
    'discount_amount' => $discountAmount,
    'shipping_fee' => $shippingFee,   // ⭐ Save shipping fee
    'final_amount' => $finalAmount,
    'discount_code' => $discountCode,
];

echo json_encode(['sessionId' => $sessionData['id']]);
exit;
