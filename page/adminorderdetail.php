<?php
require '../sb_base.php';
$orderId = intval($_GET['id'] ?? 0);

// Get main order information
$orderSql = "SELECT o.*, u.username, u.email
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             WHERE o.id = ?";
$orderStmt = $pdo->prepare($orderSql);
$orderStmt->execute([$orderId]);
$order = $orderStmt->fetch();

// Get all products in the order
$itemsSql = "SELECT oi.*, p.title AS product_title, p.author AS product_author, p.id AS product_id
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?";
$itemsStmt = $pdo->prepare($itemsSql);
$itemsStmt->execute([$orderId]);
$items = $itemsStmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Details | Admin</title>
<style>
table { border-collapse: collapse; width: 100%; margin-top: 2em;}
th, td { padding: 8px; border: 1px solid #ccc; }
th { background: #f2f2f2; }
tr:hover { background: #eeffee; }
</style>
</head>
<body>
<h1>Order Details (ID: <?= $orderId ?>)</h1>
<?php if(!$order): ?>
    <p>No such order.</p>
<?php else: ?>
    <ul>
        <li><strong>User:</strong> <?= htmlspecialchars($order['username']) ?> (<?= htmlspecialchars($order['email']) ?>)</li>
        <li><strong>Order Date:</strong> <?= $order['order_date'] ?></li>
        <li><strong>Total Price:</strong> <?= number_format($order['total_price'],2) ?></li>
        <li><strong>Shipping Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?></li>
        <li><strong>Status:</strong> <?= $order['status'] ?></li>
    </ul>
    <h2>Order Items</h2>
    <table>
        <tr>
            <th>Product ID</th>
            <th>Title</th>
            <th>Author</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach($items as $item): ?>
        <tr>
            <td><?= $item['product_id'] ?></td>
            <td><?= htmlspecialchars($item['product_title']) ?></td>
            <td><?= htmlspecialchars($item['product_author']) ?></td>
            <td><?= number_format($item['price'],2) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($item['price']*$item['quantity'],2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <br><a href="adminorderlist.php">Back to Order List</a>
<?php endif; ?>
</body>
</html>
