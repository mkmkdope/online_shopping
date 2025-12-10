<?php

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in first']);
    exit;
}

// Include necessary files
require_once __DIR__ . '/../sb_base.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/product_functions.php'; // Ensure get_product_by_id is available

header('Content-Type: application/json');

// Check if required POST parameters exist
if (!isset($_POST['code'], $_POST['selected_items'])) {
    echo json_encode(['success' => false, 'message' => 'Incomplete parameters']);
    exit;
}

$userId = $_SESSION['user_id'];
$code = trim($_POST['code']);
$selectedItems = json_decode($_POST['selected_items'], true);

if (empty($selectedItems)) {
    echo json_encode(['success' => false, 'message' => 'No products selected']);
    exit;
}

//  Verify that the user exists in the database
$userCheckSql = "SELECT id FROM users WHERE id = ?";
$userCheckStmt = $pdo->prepare($userCheckSql);
$userCheckStmt->execute([$userId]);
$userExists = $userCheckStmt->fetch();

if (!$userExists) {
    echo json_encode(['success' => false, 'message' => 'User does not exist, please log in again']);
    exit;
}

// Recalculate cart total to prevent user tampering, and check stock
$total = 0;
foreach ($selectedItems as $productId) {
    $sql = "SELECT quantity FROM cart_items WHERE user_id = ? AND product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $productId]);
    $cartItem = $stmt->fetch();

    if ($cartItem) {
        $product = get_product_by_id($productId);
        if ($product) {
            // Check stock
            if ($cartItem['quantity'] > $product['stock_quantity']) {
                echo json_encode([
                    'success' => false,
                    'message' => "Product {$product['title']} is out of stock"
                ]);
                exit;
            }

            $total += $product['price'] * $cartItem['quantity'];
        }
    }
}

if ($total <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid cart']);
    exit;
}

// Validate discount code
$sql = "SELECT * FROM discount_codes 
        WHERE code = ? 
        AND status = 'active'
        AND (valid_from IS NULL OR valid_from <= NOW())
        AND (valid_until IS NULL OR valid_until >= NOW())
        AND times_used < usage_limit
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$code]);
$discount = $stmt->fetch();

if (!$discount) {
    echo json_encode(['success' => false, 'message' => 'Discount code is invalid or expired']);
    exit;
}

//Calculate discount amount, ensuring it is not negative
if ($discount['discount_type'] === 'percentage') {
    $discountAmount = $total * ($discount['discount_value'] / 100);
} else {
    $discountAmount = $discount['discount_value'];
}

$discountAmount = min($discountAmount, $total);
$newTotal = $total - $discountAmount;

//Update discount code usage count
$updateSql = "UPDATE discount_codes SET times_used = times_used + 1 WHERE id = ?";
$updateStmt = $pdo->prepare($updateSql);
$updateStmt->execute([$discount['id']]);

echo json_encode([
    'success' => true,
    'discount_amount' => round($discountAmount, 2),
    'new_total' => round($newTotal, 2)
]);
