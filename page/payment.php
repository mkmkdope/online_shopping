<?php

// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login/login.php?error=Please login to proceed with payment');
    exit;
}

// Load database and helpers
require_once __DIR__ . '/../sb_base.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/product_functions.php';

// Get selected items from POST or redirect back
if (!isset($_POST['selected_items']) || empty($_POST['selected_items'])) {
    header('Location: cart_view.php?error=Please select items to checkout');
    exit;
}

$selectedItems = json_decode($_POST['selected_items'], true);
$userId = cart_user_id();

// Get cart items that match selected IDs
$cartItems = [];
$totalAmount = 0;

foreach ($selectedItems as $productId) {
    $sql = "SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $productId]);
    $item = $stmt->fetch();

    if ($item) {
        $product = get_product_by_id($item['product_id']);
        if ($product) {
            $lineTotal = $product['price'] * $item['quantity'];
            $totalAmount += $lineTotal;

            $cartItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'line_total' => $lineTotal
            ];
        }
    }
}

// If no valid items, redirect back
if (empty($cartItems)) {
    header('Location: cart_view.php?error=No valid items found');
    exit;
}

$_title = 'Payment - SB Online';
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
        }

        .payment-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 1.5rem;
            color: #2c3e50;
            font-size: 1rem;
            text-decoration: none;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #e74c3c;
        }

        .page-title {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .page-title h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin: 0;
        }

        .payment-wrapper {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
        }

        .payment-form,
        .order-summary {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .order-summary {
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h3 {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #ecf0f1;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            font-size: 0.95rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: "Georgia", serif;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #3498db;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .payment-method {
            position: relative;
        }

        .payment-method input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .payment-method label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            background: #fafafa;
        }

        .payment-method input[type="radio"]:checked+label {
            border-color: #e74c3c;
            background: #fff5f5;
        }

        .order-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item img {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            background: #ecf0f1;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.3rem;
        }

        .item-qty {
            font-size: 0.85rem;
            color: #7f8c8d;
        }

        .item-price {
            font-size: 1rem;
            font-weight: bold;
            color: #e74c3c;
            text-align: right;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            font-size: 1rem;
            color: #2c3e50;
        }

        .summary-line.total {
            border-top: 2px solid #ecf0f1;
            margin-top: 1rem;
            padding-top: 1rem;
            font-size: 1.3rem;
            font-weight: bold;
            color: #e74c3c;
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 1.5rem;
            font-family: "Georgia", serif;
        }

        .submit-btn:hover {
            background: #c0392b;
        }

        .secure-note {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #7f8c8d;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 968px) {
            .payment-wrapper {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
                order: -1;
            }
        }

        @media (max-width: 640px) {
            .payment-methods {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="payment-container">
        <a href="cart_view.php" class="back-link">← Back to Cart</a>

        <div class="page-title">
            <h1>Payment Details</h1>
        </div>

        <div class="payment-wrapper">
            <div class="payment-form">
                <form id="payment-form" action="payment_process.php" method="POST">
                    <input type="hidden" name="total_amount" value="<?= number_format($totalAmount, 2, '.', '') ?>">
                    <input type="hidden" name="selected_items" value="<?= htmlspecialchars($_POST['selected_items']) ?>">
                    <input type="hidden" name="payment_method" value="Stripe Checkout">
                    
                    <div class="form-section">
                        <h3>Payment Method</h3>
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" name="payment_method_display" id="credit_card" value="Card" checked>
                                <label for="credit_card">💳 Pay with Card (Stripe Checkout)</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Billing Information</h3>
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" required>
                        </div>

                        <div class="form-group">
                            <label for="postcode">Postcode / ZIP Code</label>
                            <input type="text" id="postcode" name="postcode" required>
                        </div>

                        <div class="form-group" style="margin-top:-0.5rem;">
                            <button type="button" id="calculate_shipping_btn" style="
                                padding: 0.6rem 1rem;
                                background:#3498db;
                                color:white;
                                border:none;
                                border-radius:4px;
                                cursor:pointer;
                            ">Calculate Shipping</button>
                        </div>

                        <div id="shipping-message" style="color:blue; font-size:0.9rem; margin-top:0.3rem;"></div>
                    </div>

                    <div class="form-section">
                        <h3>Discount Code</h3>
                        <div class="form-group" style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="text" id="discount_code" name="discount_code" placeholder="Enter discount code" style="flex:1;">
                            <button type="button" id="check_discount_btn">Check Availability</button>
                        </div>
                        <div id="discount-message" style="color:green;"></div>
                    </div>

                    <button type="submit" class="submit-btn">Complete Payment</button>
                    <div class="secure-note">🔒 Secure payment processing</div>
                </form>
            </div>

            <div class="order-summary">
                <h2>Order Summary</h2>

                <div style="margin: 1.5rem 0;">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="order-item">
                            <img src="uploads/products/<?= htmlspecialchars($item['product']['cover_image']) ?>" alt="">
                            <div class="item-info">
                                <div class="item-name"><?= htmlspecialchars($item['product']['title']) ?></div>
                                <div class="item-qty">Qty: <?= $item['quantity'] ?></div>
                            </div>
                            <div class="item-price">RM<?= number_format($item['line_total'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-line">
                    <span>Subtotal</span>
                    <span>RM<?= number_format($totalAmount, 2) ?></span>
                </div>
                <div class="summary-line">
                    <span>Discount</span>
                    <span id="discount-display">- RM0.00</span>
                </div>

                <div class="summary-line">
                    <span>Shipping Fee</span>
                    <span id="shipping-display">RM0.00</span>
                </div>

                <div class="summary-line total">
                    <span>Total</span>
                    <span id="total-display">RM<?= number_format($totalAmount, 2) ?></span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://js.stripe.com/v3/"></script>
     <script>
const stripe = Stripe('pk_test_51SXhfm9YDXhXkkofqgza7axWsDIzLGMLV2CeLm64DIkFLNHuvtm5JceEa0BuYvqRJYPS0N6bS8EYyx4fXNO7CBAL00LNqXVMWc');
const form = document.getElementById('payment-form');

form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    const resp = await fetch('payment_process.php', { method: 'POST', body: formData });
    const result = await resp.json();

    if (result.error) {
        alert(result.error);
        return;
    }

    const { error } = await stripe.redirectToCheckout({ sessionId: result.sessionId });
    if (error) alert(error.message);
});


let discountAmountField = document.createElement('input');
discountAmountField.type = 'hidden';
discountAmountField.name = 'discount_amount';
discountAmountField.value = 0;
form.appendChild(discountAmountField);

let discountCodeUsedField = document.createElement('input');
discountCodeUsedField.type = 'hidden';
discountCodeUsedField.name = 'discount_code_used';
discountCodeUsedField.value = '';
form.appendChild(discountCodeUsedField);

let shippingAmountField = document.createElement('input');
shippingAmountField.type = 'hidden';
shippingAmountField.name = 'shipping_fee';
shippingAmountField.value = 0;
form.appendChild(shippingAmountField);


const discountInput = document.getElementById('discount_code');
const discountMsg   = document.getElementById('discount-message');
const discountDisplay = document.getElementById('discount-display');
const totalDisplay    = document.getElementById('total-display');
const shippingMsg     = document.getElementById('shipping-message');
const calculateShippingBtn = document.getElementById('calculate_shipping_btn');
const postcodeInput   = document.getElementById('postcode');

const originalTotal = parseFloat('<?= number_format($totalAmount, 2, ".", "") ?>');
let currentTotalAmount = originalTotal;


function updateTotal() {
    const discountValue = parseFloat(discountAmountField.value) || 0;
    const shippingFee   = parseFloat(shippingAmountField.value) || 0;

    const newTotal = currentTotalAmount - discountValue + shippingFee;

    discountDisplay.textContent = '- RM' + discountValue.toFixed(2);
    document.getElementById('shipping-display').textContent = 'RM' + shippingFee.toFixed(2);
    totalDisplay.textContent = 'RM' + newTotal.toFixed(2);
}


document.getElementById('check_discount_btn').addEventListener('click', function() {
    const code = discountInput.value.trim();
    if (!code) {
        discountMsg.style.color = 'red';
        discountMsg.textContent = 'Please enter a discount code';
        return;
    }

    discountMsg.textContent = 'Validating...';
    discountMsg.style.color = 'black';

    const formData = new FormData();
    formData.append('code', code);
    formData.append('selected_items', <?= json_encode($_POST['selected_items']) ?>);

    fetch('discount_process.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            discountAmountField.value = data.discount_amount;
            discountCodeUsedField.value = code;
            discountMsg.style.color = 'green';
            discountMsg.textContent = 'Discount Applied Successfully';
        } else {
            discountAmountField.value = 0;
            discountCodeUsedField.value = '';
            discountMsg.style.color = 'red';
            discountMsg.textContent = data.message;
        }
        updateTotal(); 
    })
    .catch(err => {
        discountAmountField.value = 0;
        discountCodeUsedField.value = '';
        discountMsg.style.color = 'red';
        discountMsg.textContent = 'Failed to validate discount code. Please try again';
        console.error(err);
        updateTotal();
    });
});


calculateShippingBtn.addEventListener('click', () => {
    const postcode = postcodeInput.value.trim();
    if (!postcode) {
        shippingMsg.style.color = "red";
        shippingMsg.textContent = "⚠ Please enter a postcode";
        return;
    }

    shippingMsg.style.color = "black";
    shippingMsg.textContent = "Calculating shipping...";

    const formData = new FormData();
    formData.append('postcode', postcode);

    fetch('shipping_fee.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            shippingAmountField.value = data.shipping_fee;
            shippingMsg.style.color = "green";
            shippingMsg.textContent = "✔ Shipping fee calculated: RM" + data.shipping_fee.toFixed(2);
        } else {
            shippingAmountField.value = 0;
            shippingMsg.style.color = "red";
            shippingMsg.textContent = data.message;
        }
        updateTotal(); 
    })
    .catch(err => {
        shippingAmountField.value = 0;
        shippingMsg.style.color = "red";
        shippingMsg.textContent = "Unable to calculate shipping. Please try again later";
        console.error(err);
        updateTotal();
    });
});
</script>

</body>

</html>

<?php include '../sb_foot.php'; ?>
