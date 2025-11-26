<?php
require __DIR__ . '/../sb_base.php';
require __DIR__ . '/cart.php';
require_once __DIR__ . '/product_functions.php';

header('Content-Type: application/json');

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    //set the heep become 405
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

//check the input
if(!$productId || !$quantity || $quantity < 1){
    http_response_code(405);
    echo json_encode(['ok' => false,'message' => 'Method not allowed.']);
    exit;
}


$product = get_product_by_id($productId);
//the /// is more advanced checking
if($product === null){
http_response_code(404);
echo json_encode(['ok' => false, 'message' => 'Product not found.']);
exit;
}


//check the stock
$availableStock = (int)$product['stock_quantity'];
if ($quantity > $availableStock) {
    http_response_code(409);
    echo json_encode([
        'ok' => false,
        'message' => 'Insufficient stock. Only ' . $availableStock . ' unit(s) available.'
    ]);
    exit;
}

// update the cart
try {
    $userId = cart_user_id();
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$quantity, $userId, $productId]);

    echo json_encode([
        'ok' => true,
        'message' => 'Quantity updated successfully.',
        'cartCount' => cart_item_count(),
        'subtotal' => cart_subtotal(),
    ]);
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Unable to update cart.']);
    exit;
}


?>