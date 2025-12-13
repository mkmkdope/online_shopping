<?php
require_once __DIR__ . '/../sb_base.php';

function get_product_by_id($id) {
    global $pdo;

    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function get_top_selling_products(PDO $pdo, int $limit = 5): array {
    $sql = "
        SELECT p.id, p.title, p.cover_image, p.price,
               COALESCE(AVG(r.rating),0) AS avg_rating,
               COUNT(r.id) AS review_count,
               SUM(oi.quantity) AS total_sold
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        JOIN products p ON oi.product_id = p.id
        LEFT JOIN product_reviews r ON r.product_id = p.id
        WHERE o.status = 'completed' OR o.payment_status = 'paid'
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function get_product_rating(PDO $pdo, int $productId): array {
    $sql = "SELECT COUNT(*) AS cnt, IFNULL(AVG(rating),0) AS avg_rating FROM product_reviews WHERE product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return ['count' => (int)$row['cnt'], 'avg' => round((float)$row['avg_rating'], 2)];
}


function get_product_reviews(PDO $pdo, int $productId, int $limit = 10, int $offset = 0): array {
    $sql = "
        SELECT r.*, u.username
        FROM product_reviews r
        LEFT JOIN users u ON r.user_id = u.id
        WHERE r.product_id = ?
        ORDER BY r.created_at DESC
        LIMIT ? OFFSET ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(1, $productId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function add_product_review(PDO $pdo, int $productId, int $userId, ?int $rating = null, string $review = ''): bool {
    $sql = "INSERT INTO product_reviews (product_id, user_id, rating, review, created_at)
            VALUES (?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$productId, $userId, $rating, $review]);
}
