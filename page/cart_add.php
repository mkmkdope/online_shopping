<?php
require __DIR__ . '/../sb_base.php';                 // ← 同层
require __DIR__ . '/cart.php';              // ← 进入 page 目录
require_once __DIR__ . '/product_functions.php';


header('Content-Type: application/json');




//allow only post
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
    exit;
}

//validate inputs
$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'qty', FILTER_VALIDATE_INT);

if (!$productId || !$quantity || $quantity < 1) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => 'Invalid product or quantity.']);
    exit;
}

// Ensure product exists before adding
$product = get_product_by_id($productId);
if ($product === null) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Product not found.']);
    exit;
}

// -----------------------------------------
// ★ BACKEND STOCK VALIDATION (NEW)
// -----------------------------------------
$availableStock = (int)$product['stock_quantity'];

if ($quantity > $availableStock) {
    http_response_code(409); // conflict
    echo json_encode([
        'ok' => false,
        'message' => 'Insufficient stock. Only ' . $availableStock . ' unit(s) available.'
    ]);
    exit;
}

//try to adding to cart
try {
    $item = cart_add_item($productId, $quantity);
} catch (InvalidArgumentException $exception) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'message' => $exception->getMessage()]);
    exit;
} catch (Throwable $exception) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Unable to add the product to your cart.']);
    exit;
}


//success response
echo json_encode([
    'ok' => true,
    'item' => $item,
    'cartCount' => cart_item_count(),
    'subtotal' => cart_subtotal(),
]);
