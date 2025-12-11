<?php

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first']);
    exit;
}

require_once __DIR__ . '/../sb_base.php';

$userId = $_SESSION['user_id'];

// Check postcode
if (!isset($_POST['postcode'])) {
    echo json_encode(['success' => false, 'message' => 'Postcode is required']);
    exit;
}

$postcode = trim($_POST['postcode']);

// Verify user exists
$userCheckStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$userCheckStmt->execute([$userId]);
if (!$userCheckStmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'User does not exist, please log in again']);
    exit;
}

// Validate postcode (5-digit number)
if (!preg_match('/^[0-9]{5}$/', $postcode)) {
    echo json_encode(['success' => false, 'message' => 'Invalid postcode format']);
    exit;
}

// ---------------------------
// LOGICHEREHE
// ---------------------------
$shippingZones = [
    // WEST MALAYSIA
    ['start' => 1000, 'end' => 1999, 'fee' => 5.90],   // Perlis
    ['start' => 2000, 'end' => 9999, 'fee' => 5.90],   // Kedah
    ['start' => 10000, 'end' => 14999, 'fee' => 5.90],   // Penang
    ['start' => 15000, 'end' => 18599, 'fee' => 6.90],   // Kelantan
    ['start' => 18600, 'end' => 19999, 'fee' => 6.90],   // Kelantan / Terengganu border
    ['start' => 20000, 'end' => 24999, 'fee' => 7.90],   // Terengganu
    ['start' => 25000, 'end' => 28999, 'fee' => 7.90],   // Pahang
    ['start' => 30000, 'end' => 39999, 'fee' => 7.90],   // Perak
    ['start' => 40000, 'end' => 48999, 'fee' => 6.90],   // Selangor
    ['start' => 50000, 'end' => 59999, 'fee' => 7.90],   // Kuala Lumpur / Negeri Sembilan / Melaka
    ['start' => 60000, 'end' => 69999, 'fee' => 7.90],   // Pahang / Terengganu extended
    ['start' => 70000, 'end' => 79999, 'fee' => 6.90],   // Kelantan extended
    ['start' => 80000, 'end' => 86999, 'fee' => 8.90],   // Johor
    // EAST MALAYSIA
    ['start' => 87000, 'end' => 87000, 'fee' => 12.90],  // Labuan
    ['start' => 88000, 'end' => 91999, 'fee' => 9.90],   // Sabah
    ['start' => 93000, 'end' => 97999, 'fee' => 10.90],  // Sarawak
];

// Convert postcode to integer
$postcodeInt = (int) $postcode;
$shippingFee = null;

foreach ($shippingZones as $zone) {
    if ($postcodeInt >= $zone['start'] && $postcodeInt <= $zone['end']) {
        $shippingFee = $zone['fee'];
        break;
    }
}

// Return response
if ($shippingFee === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Shipping not available for this region'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'shipping_fee' => round($shippingFee, 2)
    ]);
}

exit;
