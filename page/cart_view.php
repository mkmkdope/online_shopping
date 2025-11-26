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
            position: sticky;
            top: 20px;
        }

        h2 { margin-top: 0; }

        .cart-item {
            display: flex;
            gap: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 15px;
            margin-bottom: 15px;
            align-items: flex-start;
        }

        .item-checkbox {
            margin-top: 15px;
            cursor: pointer;
            width: 20px;
            height: 20px;
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

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .select-all-btn {
            padding: 8px 16px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
            transition: 0.2s;
        }

        .select-all-btn:hover {
            background: #0056b3;
        }

        .selection-counter {
            font-size: 14px;
            color: #666;
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
        <div class="cart-header">
            <h2 style="margin: 0;">My Bag</h2>
            <div style="display: flex; gap: 15px; align-items: center;">
                <button class="select-all-btn" id="select-all-btn">Select All</button>
                <span class="selection-counter" id="selection-counter">0 of <?= count($cartItems) ?> items selected</span>
            </div>
        </div>

        <?php if (empty($cartItems)): ?>
            <div style="text-align: center; padding: 60px 20px; color: #999;">
                <h3 style="margin-bottom: 10px; color: #666;">Your Cart is Empty</h3>
                <p style="margin-bottom: 20px;">No items in your cart yet. Start shopping!</p>
                <a href="javascript:history.back()" style="display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 3px;">
                    Continue Shopping
                </a>
            </div>
        <?php endif; ?>

        <?php foreach ($cartItems as $item): ?>
            <?php $product = get_product_by_id($item['product_id']); ?>

            <div class="cart-item">
                <input
                    type="checkbox"
                    class="item-checkbox"
                    data-product-id="<?= $item['product_id'] ?>"
                    checked
                >
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
const selectionCounterEl = document.getElementById('selection-counter');
const selectAllBtn = document.getElementById('select-all-btn');

function updateSubtotal() {
    let subtotal = 0;
    let checkedCount = 0;
    let totalCount = 0;

    document.querySelectorAll('.cart-item').forEach(cartItem => {
        const checkbox = cartItem.querySelector('.item-checkbox');
        const lineEl = cartItem.querySelector('.line-total');
        if (checkbox && lineEl) {
            totalCount++;
            if (checkbox.checked) {
                checkedCount++;
                const amount = parseFloat(lineEl.dataset.lineTotal || '0');
                if (!isNaN(amount)) {
                    subtotal += amount;
                }
            }
        }
    });

    if (subtotalEl) {
        subtotalEl.textContent = 'RM' + subtotal.toFixed(2);
    }

    if (selectionCounterEl) {
        selectionCounterEl.textContent = checkedCount + ' of ' + totalCount + ' items selected';
    }

    // Update button text based on selection state
    if (selectAllBtn) {
        selectAllBtn.textContent = checkedCount === totalCount && totalCount > 0 ? 'Deselect All' : 'Select All';
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

        // 防止重复点击：禁用select
        const currentSelect = event.target;
        currentSelect.disabled = true;

        // 提交新数量到后端
        fetch('../cart_update.php', {
            method: 'POST',
            body: new URLSearchParams({
                product_id: event.target.dataset.productId,
                quantity: quantity
            })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.ok) {
                alert(data.message || 'Failed to update cart');
                // 失败时刷新页面恢复数据
                location.reload();
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Failed to update cart');
            location.reload();
        })
        .finally(() => {
            // 不管成功失败，都重新启用select
            currentSelect.disabled = false;
        });
    });
});

// Handle checkbox changes
document.querySelectorAll('.item-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', () => {
        updateSubtotal();
    });
});

// Handle Select All button
selectAllBtn.addEventListener('click', () => {
    const allCheckboxes = document.querySelectorAll('.item-checkbox');
    const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);

    // Toggle: if all are checked, uncheck all; otherwise, check all
    allCheckboxes.forEach(checkbox => {
        checkbox.checked = !allChecked;
    });

    updateSubtotal();
});

updateSubtotal();
</script>

</body>
</html>
