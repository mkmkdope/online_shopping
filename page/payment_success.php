<?php
// Handle Stripe success redirect: confirm payment, create order, show receipt, and award points.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /login/login.php');
    exit;
}

require_once __DIR__ . '/../sb_base.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/product_functions.php';

function calculatePointsForPurchase($userId, $purchaseAmount, $orderId, $isFirstPurchase = false) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT id, user_role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception("User not found");
        }
        
        $userRole = $user['user_role'];
        
        $stmt = $pdo->prepare("
            SELECT * FROM reward_rules 
            WHERE is_active = 1 
            AND (
                JSON_CONTAINS(user_roles, ?, '$') OR 
                JSON_CONTAINS(user_roles, '\"all\"', '$')
            )
            ORDER BY priority ASC, created_at DESC
        ");
        $stmt->execute([json_encode($userRole)]);
        $applicableRules = $stmt->fetchAll();
        
        if (empty($applicableRules)) {
            return ['success' => true, 'points_awarded' => 0, 'rule_used' => 'No applicable rules'];
        }
        
        $points = 0;
        $appliedRule = null;
        
        foreach ($applicableRules as $rule) {
            if ($purchaseAmount >= $rule['min_spend']) {
                if ($rule['rule_name'] === 'First Purchase Bonus' && !$isFirstPurchase) {
                    continue; 
                }
                
                $rulePoints = round($purchaseAmount * $rule['points_per_amount']);
                
                if ($rule['max_points_per_order'] > 0 && $rulePoints > $rule['max_points_per_order']) {
                    $rulePoints = $rule['max_points_per_order'];
                }
                
                $points = $rulePoints;
                $appliedRule = $rule;
                break; 
            }
        }
        
        if ($points > 0) {
            $stmt = $pdo->prepare("SELECT total_points FROM reward_points WHERE user_id = ?");
            $stmt->execute([$userId]);
            $currentPoints = $stmt->fetch();
            
            if ($currentPoints) {
                $newTotal = $currentPoints['total_points'] + $points;
                $stmt = $pdo->prepare("UPDATE reward_points SET total_points = ?, updated_at = NOW() WHERE user_id = ?");
                $stmt->execute([$newTotal, $userId]);
            } else {
                $newTotal = $points;
                $stmt = $pdo->prepare("INSERT INTO reward_points (user_id, total_points) VALUES (?, ?)");
                $stmt->execute([$userId, $points]);
            }
            
           
            $stmt = $pdo->prepare("INSERT INTO reward_point_transactions 
                                   (user_id, points, transaction_type, description) 
                                   VALUES (?, ?, 'earn', ?)");
            $description = "Order #" . $orderId . " - RM" . number_format($purchaseAmount, 2) . " - " . $appliedRule['rule_name'];
            $stmt->execute([$userId, $points, $description]);
            
            return [
                'success' => true,
                'points_awarded' => $points,
                'new_balance' => $newTotal,
                'rule_used' => $appliedRule['rule_name'],
                'rule_id' => $appliedRule['id']
            ];
        }
        
        return ['success' => true, 'points_awarded' => 0, 'rule_used' => 'No rules applied'];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function isFirstPurchase($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
    $stmt->execute([$userId]);
    $result = $stmt->fetch();
    return $result['order_count'] == 0;
}

$sessionId = $_GET['session_id'] ?? '';

// Fallback for old flow
$referenceNo = $_GET['ref'] ?? '';
$paymentId = $_GET['payment_id'] ?? '';

// If no session id and no legacy params, bail out
if (empty($sessionId) && (empty($referenceNo) || empty($paymentId))) {
    header('Location: /index.php');
    exit;
}

$pointsEarned = 0;
$newPointsBalance = 0;
$pointsMessage = '';

// Legacy display (if already paid by old flow)
if (empty($sessionId) && !empty($paymentId) && !empty($referenceNo)) {
    $sql = "SELECT * FROM payment WHERE payment_id = ? AND reference_no = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$paymentId, $referenceNo]);
    $payment = $stmt->fetch();
    if (!$payment) {
        header('Location: /index.php');
        exit;
    }
} else {
    // New Stripe Checkout flow
    if (empty($stripeSecretKey)) {
        die('Stripe secret key not configured.');
    }

    $pending = $_SESSION['pending_checkout'] ?? null;
    if (!$pending || ($pending['session_id'] ?? '') !== $sessionId) {
        header('Location: /cart_view.php?error=Payment session not found or expired.');
        exit;
    }

    // Fetch Checkout Session from Stripe to verify status
    $stripeResponse = null;
    $ch = curl_init("https://api.stripe.com/v1/checkout/sessions/" . urlencode($sessionId) . "?expand[]=payment_intent");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_USERPWD => $stripeSecretKey . ':',
    ]);
    $stripeRaw = curl_exec($ch);
    $stripeHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($stripeRaw === false || $stripeHttp >= 300) {
        error_log('Unable to fetch Stripe session: ' . $stripeRaw);
        header('Location: /cart_view.php?error=Unable to verify payment.');
        exit;
    }
    $stripeResponse = json_decode($stripeRaw, true);
    if (($stripeResponse['payment_status'] ?? '') !== 'paid') {
        header('Location: /cart_view.php?error=Payment not completed.');
        exit;
    }

    $paymentIntentId = $stripeResponse['payment_intent']['id'] ?? ($stripeResponse['payment_intent'] ?? $sessionId);
    $paymentAmount = isset($stripeResponse['amount_total']) ? ($stripeResponse['amount_total'] / 100) : ($pending['total_amount'] ?? 0);

    $totalAmount = $stripeResponse['metadata']['total_amount'] ?? $pending['total_amount'];
    $discountAmount = $stripeResponse['metadata']['discount_amount'] ?? $pending['discount_amount'] ?? 0;

    // Avoid double-processing if payment already stored
    $existingPaymentStmt = $pdo->prepare("SELECT * FROM payment WHERE reference_no = ? LIMIT 1");
    $existingPaymentStmt->execute([$paymentIntentId]);
    $payment = $existingPaymentStmt->fetch();

    if (!$payment) {
        try {
            $pdo->beginTransaction();

            $userId = $_SESSION['user_id'];
            
           
            $orderNumber = $pending['order_number'] ?? ('ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8)));

          
            $orderSql = "INSERT INTO orders (user_id, order_number, total_amount, status, payment_status, 
                         shipping_name, shipping_email, shipping_phone, shipping_address, created_at) 
                         VALUES (?, ?, ?, 'processing', 'paid', ?, ?, ?, ?, NOW())";

            $orderStmt = $pdo->prepare($orderSql);
            $orderStmt->execute([
                $userId,
                $orderNumber,
                $paymentAmount,
                $pending['shipping_name'] ?? '',
                $pending['shipping_email'] ?? '',
                $pending['shipping_phone'] ?? '',
                $pending['shipping_address'] ?? '',
            ]);

            $orderId = $pdo->lastInsertId();

            $selectedProductIds = $pending['selected_product_ids'] ?? [];
            $cartUserId = cart_user_id();
            $orderTotal = 0;

            foreach ($selectedProductIds as $productId) {
                $cartSql = "SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?";
                $cartStmt = $pdo->prepare($cartSql);
                $cartStmt->execute([$cartUserId, $productId]);
                $cartItem = $cartStmt->fetch();
                if (!$cartItem) {
                    continue;
                }

                $product = get_product_by_id($productId);
                if (!$product) {
                    continue;
                }

                $subtotal = $product['price'] * $cartItem['quantity'];
                $orderTotal += $subtotal;

                $itemSql = "INSERT INTO order_items (order_id, product_id, product_title, product_price, quantity, subtotal, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $itemStmt = $pdo->prepare($itemSql);
                $itemStmt->execute([
                    $orderId,
                    $productId,
                    $product['title'],
                    $product['price'],
                    $cartItem['quantity'],
                    $subtotal
                ]);

                // Update product stock
                $newquantity = $product['stock_quantity'] - $cartItem['quantity'];
                $updateQuantitySql = "UPDATE products SET stock_quantity = ? WHERE id = ?";
                $updateQuantityStmt = $pdo->prepare($updateQuantitySql);
                $updateQuantityStmt->execute([$newquantity, $productId]);
            }

            // Insert payment record
            $paymentSql = "INSERT INTO payment (order_id, amount, method, status, reference_no, transaction_time, created_at) 
                           VALUES (?, ?, ?, 'SUCCESS', ?, NOW(), NOW())";

            $paymentStmt = $pdo->prepare($paymentSql);
            $paymentStmt->execute([
                $orderId,
                $orderTotal ?: $paymentAmount,
                'Stripe Checkout',
                $paymentIntentId
            ]);

            $paymentId = $pdo->lastInsertId();

            // Remove purchased items from cart
            foreach ($selectedProductIds as $productId) {
                cart_remove_item($productId);
            }

            $pdo->commit();

            
            $purchaseAmountForPoints = $totalAmount - $discountAmount;
            if ($purchaseAmountForPoints < 0) {
                $purchaseAmountForPoints = 0;
            }
            
    
            $firstPurchase = isFirstPurchase($userId);
            
            $pointsResult = calculatePointsForPurchase($userId, $purchaseAmountForPoints, $orderId, $firstPurchase);
            
            if ($pointsResult['success'] && $pointsResult['points_awarded'] > 0) {
                $pointsEarned = $pointsResult['points_awarded'];
                $newPointsBalance = $pointsResult['new_balance'];
                $pointsMessage = "You earned " . $pointsEarned . " points! New balance: " . $newPointsBalance . " points.";
            }

            // Build payment record for display
            $payment = [
                'payment_id' => $paymentId,
                'reference_no' => $paymentIntentId,
                'method' => 'Stripe Checkout',
                'status' => 'SUCCESS',
                'amount' => $stripeResponse['amount_total'] / 100, 
                'transaction_time' => date('Y-m-d H:i:s'),
                'order_id' => $orderId,
                'order_number' => $orderNumber,
            ];
            
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Payment success handling failed: ' . $e->getMessage());
            header('Location: /cart_view.php?error=Failed to finalize order.');
            exit;
        }
    }

    // Clear pending checkout context
    unset($_SESSION['pending_checkout']);

    // Align output variables
    $referenceNo = $payment['reference_no'];
    $paymentId = $payment['payment_id'] ?? '';
}


$_title = 'Payment Successful - SB Online';
include '../sb_head.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?></title>
    <style>
        body {
            background-color: #f9f7f2;
            font-family: "Georgia", serif;
            min-height: 100vh;
        }

        .success-container {
            max-width: 600px;
            margin: 4rem auto;
            padding: 0 2rem;
        }

        .success-card {
            background: white;
            border-radius: 8px;
            padding: 3rem 2.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #27ae60;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }

            to {
                transform: scale(1);
            }
        }

        .success-icon::before {
            content: '✓';
            color: white;
            font-size: 50px;
            font-weight: bold;
        }

        h1 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 2rem;
        }

      
        .points-reward {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1rem 0 2rem;
            text-align: center;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .points-badge {
            background: gold;
            color: #333;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            display: inline-block;
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .points-reward h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.3rem;
        }

        .points-reward p {
            margin: 0;
            font-size: 0.95rem;
            opacity: 0.9;
        }

        .payment-details {
            background: #f9f7f2;
            border-radius: 4px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 0.95rem;
            color: #7f8c8d;
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .detail-value.amount {
            font-size: 1.5rem;
            color: #e74c3c;
        }

        .detail-value.status {
            color: #27ae60;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            flex: 1;
            padding: 0.9rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
            font-family: "Georgia", serif;
        }

        .btn-primary {
            background: #e74c3c;
            color: white;
        }

        .btn-primary:hover {
            background: #c0392b;
        }

        .btn-secondary {
            background: white;
            color: #2c3e50;
            border: 2px solid #2c3e50;
        }

        .btn-secondary:hover {
            background: #2c3e50;
            color: white;
        }

        .btn-rewards {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }

        .btn-rewards:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }

        @media (max-width: 640px) {
            .success-card {
                padding: 2rem 1.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon"></div>

            <h1>Payment Successful!</h1>
            <p class="subtitle">Thank you for your purchase. Your order has been confirmed.</p>

            <?php if ($pointsEarned > 0): ?>
      
                <div class="points-reward">
                    <h3>🎉 Points Earned!</h3>
                    <div class="points-badge">+<?= $pointsEarned ?> Points</div>
                    <p>New balance: <?= $newPointsBalance ?> points</p>
                    <p style="font-size: 0.85rem; margin-top: 0.5rem; opacity: 0.8;">
                        Redeem your points for discounts and rewards!
                    </p>
                </div>
            <?php endif; ?>

            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">Reference Number</span>
                    <span class="detail-value"><?= htmlspecialchars($referenceNo) ?></span>
                </div>
                <?php if (isset($payment['order_number'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Order Number</span>
                    <span class="detail-value"><?= htmlspecialchars($payment['order_number']) ?></span>
                </div>
                <?php endif; ?>
                <div class="detail-row">
                    <span class="detail-label">Payment Method</span>
                    <span class="detail-value"><?= htmlspecialchars($payment['method']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Transaction Time</span>
                    <span class="detail-value"><?= date('d M Y, h:i A', strtotime($payment['transaction_time'])) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span class="detail-value status"><?= htmlspecialchars($payment['status']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount Paid</span>
                    <span class="detail-value amount">RM<?= number_format($payment['amount'], 2) ?></span>
                </div>
            </div>

            <div class="action-buttons">
                <a href="/index.php" class="btn btn-primary">Continue Shopping</a>
                <?php if ($pointsEarned > 0): ?>
                    <a href="/page/rewards_shop.php" class="btn btn-rewards">Redeem Points</a>
                <?php endif; ?>
                <a href="cart_view.php" class="btn btn-secondary">View Cart</a>
            </div>
        </div>
    </div>
</body>

</html>

<?php include '../sb_foot.php'; ?>
