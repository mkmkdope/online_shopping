<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../sb_base.php';
require_once __DIR__ . '/product_functions.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$productId = intval($_POST['product_id'] ?? 0);
$reviewId = intval($_POST['review_id'] ?? 0);

// rating can be empty string → convert to null
$rating = ($_POST['rating'] ?? '') !== '' ? intval($_POST['rating']) : null;
$review = trim($_POST['review'] ?? '');


if ($reviewId > 0) {

    if ($rating === null && $review === '') {
        echo json_encode(['success' => false, 'message' => 'Please provide at least a rating or review content']);
        exit;
    }
    
    rating → convert to null
    if ($rating === 0) $rating = null; 

    $sql = "UPDATE product_reviews
            SET rating = ?, review = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([$rating, $review, $reviewId, $userId]);

    echo json_encode(['success' => $ok, 'message' => $ok ? 'Review updated' : 'Update failed']);
    exit;
} else {

    if ($productId <= 0 || ($rating === null && $review === '')) {
        echo json_encode(['success' => false, 'message' => 'Please provide at least a rating or review content']);
        exit;
    }


    $ok = add_product_review($pdo, $productId, $userId, $rating, $review);

    echo json_encode([
        'success' => $ok,
        'message' => $ok ? 'Review submitted' : 'Failed'
    ]);
    exit;
}
?>
