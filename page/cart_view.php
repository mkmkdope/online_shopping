<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load database and helpers
require_once __DIR__ . '/../sb_base.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/product_functions.php';

// Get current user ID
$userId = cart_user_id();

// Get cart items
$sql = "SELECT * FROM cart_items WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();



// Calculate subtotal
$subtotal = 0;
foreach ($cartItems as $item) {
    $product = get_product_by_id($item['product_id']);
    if($product){
    $subtotal += $product['price'] * $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Cart</title>
    <style>
        body {
            font-family: Arial;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: auto;
            display: flex;
            gap: 30px;
            padding-top: 40px;
        }

        .cart-items {
            flex: 2;
            background: white;
            padding: 20px;
            border-radius: 5px;
        }

        .summary {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 5px;
            height: fit-content;
        }

        h2 { margin-top: 0; }

        .cart-item {
            display: flex;
            gap: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }

        .cart-item img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 5px;
        }

        .item-details { flex: 1; }

        .title {
            font-size: 14px;
            color: #333;
            margin-bottom: 6px;
        }

        .price {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .remove {
            cursor: pointer;
              width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #777;
            transition: 0.2s;
        }

        .remove:hover { 
            color: black; 
        }

       
    </style>
</head>

<body>

<a href="javascript:history.back()" 
   style="display:inline-block; margin:20px; font-size:18px; text-decoration:none; color:#333;">
    ← Back
</a>

<div class="container">
    <div class="cart-items">
        <h2>My Bag</h2>

        <?php foreach ($cartItems as $item): ?>
            <?php $product = get_product_by_id($item['product_id']); ?>

            <div class="cart-item">
                <img src="../images/<?= $product['cover_image'] ?>" alt="">

                <div class="item-details">
                    <div class="price">RM<?= number_format($product['price'], 2) ?></div>
                    <?php $lineTotal = $product['price'] * $item['quantity']; ?>
                    <div class="line-total" data-line-total="<?= number_format($lineTotal, 2, '.', '') ?>">
                        RM<?= number_format($lineTotal, 2) ?>
                    </div>
                    <div class="title"><?= htmlspecialchars($product['title']) ?></div>

                    <div>
                        Qty:
                        <select
                            class="qty-selector"
                            style="padding:4px;"
                            data-product-id="<?= $item['product_id'] ?>"
                            data-price="<?= number_format($product['price'], 2, '.', '') ?>"
                        >
                            <?php for($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>" <?= $i == $item['quantity'] ? 'selected' : '' ?>>
                                    <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="remove" data-product-id="<?= $item['product_id'] ?>">×</div>
            </div>

        <?php endforeach; ?>
    </div>

    <div class="summary">
        <h2>Total</h2>
        <p>Sub-total: <span id="cart-subtotal">RM<?= number_format($subtotal, 2) ?></span></p>

        <button style="width:100%; padding:10px; background:green; color:white; border:none; border-radius:3px; font-size:18px;">
            CHECKOUT
        </button>
    </div>
</div>

<script>
// Remove item
document.querySelectorAll('.remove').forEach(btn => {
    btn.addEventListener('click', () => {
        const productId = btn.dataset.productId;
        fetch('../cart_delete.php?product_id=' + productId)
            .then(res => res.text())
            .then(data => {
                if(data.includes('deleted')){
                    location.reload();
                } else {
                    alert('Error: Failed to delete');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Failed to delete item');
            });
    });
});


// Update quantity

const qtySelectors = document.querySelectorAll('.qty-selector');
const subtotalEl = document.getElementById('cart-subtotal');

function updateSubtotal() {
    let subtotal = 0;
    document.querySelectorAll('.line-total').forEach(lineEl => {
        const amount = parseFloat(lineEl.dataset.lineTotal || '0');
        if (!isNaN(amount)) {
            subtotal += amount;
        }
    });
    if (subtotalEl) {
        subtotalEl.textContent = 'RM' + subtotal.toFixed(2);
    }
}

qtySelectors.forEach(select => {
    select.addEventListener('change', event => {
        const price = parseFloat(event.target.dataset.price || '0');
        const quantity = parseInt(event.target.value, 10) || 0;
        const lineTotal = price * quantity;
        const lineTotalEl = event.target.closest('.cart-item').querySelector('.line-total');
        if (lineTotalEl) {
            lineTotalEl.dataset.lineTotal = lineTotal.toFixed(2);
            lineTotalEl.textContent = 'RM' + lineTotal.toFixed(2);
        }

        updateSubtotal();

        // Optional: submit the new quantity to the backend
         fetch('../cart_update.php', {
             method: 'POST',
             body: new URLSearchParams({
                 product_id: event.target.dataset.productId,
                 quantity: quantity
             })
         });
    });
});

updateSubtotal();
</script>

</body>
</html>
