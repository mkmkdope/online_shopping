<?php

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first']);
    exit;
}

require_once __DIR__ . '/../sb_base.php';

$userId = $_SESSION['user_id'];

// Check if POST contains postcode
if (!isset($_POST['postcode'])) {
    echo json_encode(['success' => false, 'message' => 'Postcode is required']);
    exit;
}

$postcode = trim($_POST['postcode']);

// Verify user exists
$userCheckSql = "SELECT id FROM users WHERE id = ?";
$userCheckStmt = $pdo->prepare($userCheckSql);
$userCheckStmt->execute([$userId]);
$userExists = $userCheckStmt->fetch();

if (!$userExists) {
    echo json_encode(['success' => false, 'message' => 'User does not exist, please log in again']);
    exit;
}

// Validate postcode format
if (!preg_match('/^[0-9]{5}$/', $postcode)) {
    echo json_encode(['success' => false, 'message' => 'Invalid postcode format']);
    exit;
}

// ---------------------------
// LOGIC PIT HEHERE
// ---------------------------

$shippingFee = null;

// West Malaysia Zone A
if ($postcode >= 10000 && $postcode <= 39999) {
    $shippingFee = 5.90;

// West Malaysia Zone B
} elseif ($postcode >= 40000 && $postcode <= 69999) {
    $shippingFee = 7.90;

// SABAH
} elseif ($postcode >= 88000 && $postcode <= 91000) {
    $shippingFee = 12.90;

// SARAWAK
} elseif ($postcode >= 93000 && $postcode <= 97000) {
    $shippingFee = 13.90;

} else {
    echo json_encode([
        'success' => false,
        'message' => 'Shipping not available for this region'
    ]);
    exit;
}

// Success response
echo json_encode([
    'success' => true,
    'shipping_fee' => round($shippingFee, 2)
]);

exit;
