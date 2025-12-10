<?php
// Handle Stripe success redirect: confirm payment, create order, and show receipt.
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

$sessionId = $_GET['session_id'] ?? '';

// Fallback for old flow
$referenceNo = $_GET['ref'] ?? '';
$paymentId = $_GET['payment_id'] ?? '';

// If no session id and no legacy params, bail out
if (empty($sessionId) && (empty($referenceNo) || empty($paymentId))) {
    header('Location: /index.php');
    exit;
}

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

    // Avoid double-processing if payment already stored
    $existingPaymentStmt = $pdo->prepare("SELECT * FROM payment WHERE reference_no = ? LIMIT 1");
    $existingPaymentStmt->execute([$paymentIntentId]);
    $payment = $existingPaymentStmt->fetch();

    if (!$payment) {
        try {
            $pdo->beginTransaction();

            $userId = $_SESSION['user_id'];
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

            $orderSql = "INSERT INTO orders (user_id, order_number, total_amount, status, payment_status, 
                         shipping_name, shipping_email, shipping_phone, shipping_address, created_at) 
                         VALUES (?, ?, ?, 'processing', 'paid', ?, ?, ?, ?, NOW())";

            $orderStmt = $pdo->prepare($orderSql);
            $orderStmt->execute([
                $userId,
                $orderNumber,
                $paymentAmount,
                $pending['full_name'] ?? '',
                $pending['email'] ?? '',
                $pending['phone'] ?? '',
                $pending['address'] ?? '',
            ]);

            $orderId = $pdo->lastInsertId();

            $selectedProductIds = $pending['selected_product_ids'] ?? [];
            $cartUserId = cart_user_id();
            $totalAmount = 0;

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
                $totalAmount += $subtotal;

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
                $totalAmount ?: $paymentAmount,
                'Stripe Checkout',
                $paymentIntentId
            ]);

            $paymentId = $pdo->lastInsertId();

            // Remove purchased items from cart
            foreach ($selectedProductIds as $productId) {
                cart_remove_item($productId);
            }

            $pdo->commit();

            // Build payment record for display
            $payment = [
                'payment_id' => $paymentId,
                'reference_no' => $paymentIntentId,
                'method' => 'Stripe Checkout',
                'status' => 'SUCCESS',
               'amount' => $stripeResponse['amount_total'] / 100, 
                'transaction_time' => date('Y-m-d H:i:s'),
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

            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">Reference Number</span>
                    <span class="detail-value"><?= htmlspecialchars($referenceNo) ?></span>
                </div>
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
                <a href="cart_view.php" class="btn btn-secondary">View Cart</a>
            </div>
        </div>
    </div>
</body>

</html>

<?php include '../sb_foot.php'; ?>
