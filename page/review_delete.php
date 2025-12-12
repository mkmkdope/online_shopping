<?php
session_start();
require_once '../sb_base.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$reviewId = intval($_POST['id'] ?? 0);

if ($reviewId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid review']);
    exit;
}

$sql = "DELETE FROM product_reviews WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($sql);
$ok = $stmt->execute([$reviewId, $userId]);

echo json_encode(['success' => $ok, 'message' => $ok ? 'Review deleted' : 'Delete failed']);
