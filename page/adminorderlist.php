<?php
require '../sb_base.php';

// Query all orders along with user information
$sql = "SELECT o.id, o.order_date, o.status, o.total_price, o.shipping_address, u.username, u.email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.order_date DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Management (All Orders)</title>
<style>
table { border-collapse: collapse; width: 100%; margin-top: 2em;}
th, td { padding: 8px; border: 1px solid #ccc; }
th { background: #f2f2f2; }
tr:hover { background: #ffffdd; }
.action { text-align: center; }
</style>
</head>
<body>
<h1>All Orders (Admin)</h1>
<table>
    <tr>
        <th>Order ID</th>
        <th>User</th>
        <th>Email</th>
        <th>Order Date</th>
        <th>Status</th>
        <th>Total Price</th>
        <th>Shipping Address</th>
        <th>Details</th>
    </tr>
<?php foreach($orders as $o): ?>
    <tr>
        <td><?= $o['id'] ?></td>
        <td><?= htmlspecialchars($o['username']) ?></td>
        <td><?= htmlspecialchars($o['email']) ?></td>
        <td><?= $o['order_date'] ?></td>
        <td><?= $o['status'] ?></td>
        <td><?= number_format($o['total_price'],2) ?></td>
        <td><?= htmlspecialchars($o['shipping_address']) ?></td>
        <td class="action"><a href="adminorderdetail.php?id=<?= $o['id'] ?>">View</a></td>
    </tr>
<?php endforeach; ?>
</table>
</body>
</html>
