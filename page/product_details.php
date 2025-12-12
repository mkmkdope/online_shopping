<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'sbonline';
$username = 'root';
$password = '';

// Initialize
$product = null;
$productImages = [];
$relatedProducts = [];
$error = '';
$ratingInfo = ['avg' => 0, 'count' => 0];
$reviews = [];

require_once 'product_functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $error = "Product ID is required.";
} else {
    $productId = intval($_GET['id']);
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT p.*, c.name as category_name, c.id as category_id 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $error = "Product not found.";
        } else {
            $productImages = json_decode($product['images'] ?? '[]', true);
            if (empty($productImages) && !empty($product['cover_image'])) {
                $productImages = [$product['cover_image']];
            }

            $ratingInfo = get_product_rating($pdo, $productId);
            $reviews = get_product_reviews($pdo, $productId);
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

$_title = $product ? htmlspecialchars($product['title']) : 'Product Details';
include '../sb_head.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $_title; ?> - Book Shop</title>
<link rel="stylesheet" href="/css/product.css">
<style>
#star-rating span {
    font-size: 1.4em;
    cursor: pointer;
    color: #ccc;
    transition: color 0.2s;
}
#star-rating span.hovered,
#star-rating span.selected { color: #FFD700; }

.review-stars span.filled { color: #FFD700; }
.review-stars span.empty { color: #ccc; }
</style>
</head>
<body>
<div class="product-details-container">

<?php if ($error): ?>
<div class="error-message">
    <h3>Error</h3>
    <p><?php echo htmlspecialchars($error); ?></p>
    <a href="product.php" class="back-button">Back to Products</a>
</div>
<?php elseif ($product): ?>
<a href="product_view.php" class="back-button">&larr; Back to Products</a>

<div class="product-details">
    <!-- Product Images -->
    <div class="product-images">
        <div class="main-image">
            <?php if (!empty($productImages)): ?>
            <img src="uploads/products/<?php echo htmlspecialchars($productImages[0]); ?>" 
                 alt="<?php echo htmlspecialchars($product['title']); ?>" 
                 id="mainProductImage">
            <?php else: ?>
            <div class="no-image" style="height: 400px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 8px;">
                No Image Available
            </div>
            <?php endif; ?>
        </div>

        <?php if (count($productImages) > 1): ?>
        <div class="image-gallery">
            <?php foreach ($productImages as $index => $image): ?>
            <div class="gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>" 
                 onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>', this)">
                <img src="uploads/products/<?php echo htmlspecialchars($image); ?>" 
                     alt="<?php echo htmlspecialchars($product['title']); ?>">
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Product Info -->
    <div class="product-info">
        <?php if (!empty($product['category_name'])): ?>
        <div class="product-category category-highlight">
            <?php echo htmlspecialchars($product['category_name']); ?>
        </div>
        <?php endif; ?>

        <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>
        <p class="product-author">by <?php echo htmlspecialchars($product['author']); ?></p>
        <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>

        <!-- Stock -->
        <div class="stock-info">
            <?php if ($product['stock_quantity'] > 0): ?>
            <span class="in-stock">✓ In Stock (<?php echo $product['stock_quantity']; ?> available)</span>
            <?php else: ?>
            <span class="out-of-stock">✗ Out of Stock</span>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <?php if ($product['stock_quantity'] > 0): ?>
            <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
            <button class="buy-now-btn" onclick="buyNow(<?php echo $product['id']; ?>)">Buy Now</button>
            <?php else: ?>
            <button class="add-to-cart-btn" disabled>Out of Stock</button>
            <button class="buy-now-btn" disabled>Notify Me</button>
            <?php endif; ?>
        </div>

        <!-- Meta Details -->
        <div class="product-meta-details">
            <div class="meta-item"><span class="meta-label">Publisher:</span> <span class="meta-value"><?php echo !empty($product['publisher']) ? htmlspecialchars($product['publisher']) : 'Not specified'; ?></span></div>
            <div class="meta-item"><span class="meta-label">Publication Date:</span> <span class="meta-value"><?php echo !empty($product['publication_date']) ? date('F j, Y', strtotime($product['publication_date'])) : 'Not specified'; ?></span></div>
            <div class="meta-item"><span class="meta-label">Pages:</span> <span class="meta-value"><?php echo !empty($product['pages']) ? number_format($product['pages']) : 'Not specified'; ?></span></div>
            <div class="meta-item"><span class="meta-label">Category:</span> <span class="meta-value"><?php echo !empty($product['category_name']) ? htmlspecialchars($product['category_name']) : 'Uncategorized'; ?></span></div>
            <div class="meta-item"><span class="meta-label">ISBN/ID:</span> <span class="meta-value">#<?php echo $product['id']; ?></span></div>
        </div>

        <!-- Description -->
        <?php if (!empty($product['description'])): ?>
        <div class="product-description-full">
            <h3>Description</h3>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
        <?php endif; ?>

        
<!-- Reviews -->
<?php if (isset($_SESSION['user_id'])): ?>
<div class="review-form">
    <h3>Write a Review</h3>

    <label>Your Rating:</label>
    <div class="star-rating" id="star-rating">
        <span data-value="1">☆</span>
        <span data-value="2">☆</span>
        <span data-value="3">☆</span>
        <span data-value="4">☆</span>
        <span data-value="5">☆</span>
        <span id="selected-rating" style="margin-left: 10px; font-weight:bold;">0</span>/5
    </div>

    <label for="review-text">Your Review:</label>

   
    <input type="hidden" id="editing-review-id" name="review_id" value="">

    <textarea id="review-text" rows="4" placeholder="Write your review..."></textarea>

    <button id="submit-review-btn">Submit Review</button>
    <p id="review-msg"></p>
</div>
<?php else: ?>
<p><a href="/login/login.php">Login</a> to write a review.</p>
<?php endif; ?>


           <!-- Ratings Summary -->
        <div class="rating-summary">
            <h3>Ratings</h3>
            <?php if ($ratingInfo['count'] > 0): ?>
            <div class="rating-stars">
                <?php
                $avg = round($ratingInfo['avg']);
                for ($i=1;$i<=5;$i++){
                    echo $i<=$avg?'★':'☆';
                }
                ?>
                <span class="rating-text">(<?= $ratingInfo['avg'] ?> / 5, <?= $ratingInfo['count'] ?> reviews)</span>
            </div>
            <?php else: ?>
            <p class="no-reviews">No ratings yet.</p>
            <?php endif; ?>
        </div>


       <div class="review-list">
    <h3>User Reviews</h3>

    <?php if (empty($reviews)): ?>
        <p class="no-reviews">No reviews yet.</p>
    <?php else: ?>
        <?php foreach ($reviews as $r): ?>
        <div class="review-item">
            
            <div class="review-header">
                <strong><?= htmlspecialchars($r['username'] ?? 'User') ?></strong>

                <?php if ($r['user_id'] == ($_SESSION['user_id'] ?? -1)): ?>
                <span class="review-actions">
                    <button class="edit-review-btn"
                        data-id="<?= $r['id'] ?>"
                        data-text="<?= htmlspecialchars($r['review']) ?>"
                        data-rating="<?= $r['rating'] ?>">Edit</button>

                    <button class="delete-review-btn"
                        data-id="<?= $r['id'] ?>">Delete</button>
                </span>
                <?php endif; ?>
            </div>

            <div class="review-stars">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="<?= $i <= $r['rating'] ? 'filled' : 'empty' ?>">★</span>
            <?php endfor; ?>
            </div>

            <p><?= nl2br(htmlspecialchars($r['review'])) ?></p>
            <small class="review-date"><?= date('F j, Y', strtotime($r['created_at'])) ?></small>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


    </div>
</div>

<?php endif; ?>
</div>

<script>
// Image switching
function changeMainImage(imagePath, element){
    document.getElementById('mainProductImage').src='uploads/products/'+imagePath;
    document.querySelectorAll('.gallery-thumb').forEach(t=>t.classList.remove('active'));
    element.classList.add('active');
}

// Cart functions
function updateCartBadge(count){
    const badge = document.querySelector('.cart-badge');
    if(count>0){
        if(badge) badge.textContent=count;
        else{
            const cartIcon = document.querySelector('.cart-icon-link');
            const newBadge = document.createElement('span');
            newBadge.className='cart-badge';
            newBadge.textContent=count;
            cartIcon.appendChild(newBadge);
        }
    } else badge?.remove();
}
function addToCart(pid){fetch('cart_add.php',{method:'POST',headers:{"Content-Type":"application/x-www-form-urlencoded"},body:`product_id=${pid}&qty=1`}).then(r=>r.json()).then(d=>{if(d.ok){alert('Product added!');updateCartBadge(d.cartCount);}else alert(d.message||'Failed');}).catch(()=>alert('Failed'));}
function buyNow(pid){fetch('cart_add.php',{method:'POST',headers:{"Content-Type":"application/x-www-form-urlencoded"},body:`product_id=${pid}&qty=1`}).then(r=>r.json()).then(d=>{if(d.ok){updateCartBadge(d.cartCount);window.location.href='cart_view.php';}else alert(d.message||'Failed');}).catch(()=>alert('Failed'));}

// Keyboard arrow for gallery
document.addEventListener('keydown',e=>{
    const thumbs=document.querySelectorAll('.gallery-thumb');
    if(thumbs.length<=1) return;
    const active=document.querySelector('.gallery-thumb.active');
    let idx=Array.from(thumbs).indexOf(active);
    if(e.key==='ArrowRight'){e.preventDefault(); idx=(idx+1)%thumbs.length; thumbs[idx].click();}
    if(e.key==='ArrowLeft'){e.preventDefault(); idx=(idx-1+thumbs.length)%thumbs.length; thumbs[idx].click();}
});

// Review star selection
const stars=document.querySelectorAll('#star-rating span[data-value]');
let selectedRating=0;
stars.forEach(star=>{
    star.addEventListener('mouseover',()=>{stars.forEach(s=>s.classList.remove('hovered'));for(let i=0;i<star.dataset.value;i++) stars[i].classList.add('hovered');});
    star.addEventListener('mouseout',()=>{stars.forEach(s=>s.classList.remove('hovered'));});
    star.addEventListener('click',()=>{selectedRating=star.dataset.value;stars.forEach(s=>s.classList.remove('selected'));for(let i=0;i<selectedRating;i++) stars[i].classList.add('selected');document.getElementById('selected-rating').textContent=selectedRating;});
});
// Submit review AJAX

document.addEventListener('DOMContentLoaded', () => {
   
    const stars = document.querySelectorAll('#star-rating span[data-value]');
    let selectedRating = 0;

    stars.forEach(star => {
        star.addEventListener('mouseover', () => {
            stars.forEach(s => s.classList.remove('hovered'));
            for (let i = 0; i < star.dataset.value; i++) stars[i].classList.add('hovered');
        });
        star.addEventListener('mouseout', () => stars.forEach(s => s.classList.remove('hovered')));
        star.addEventListener('click', () => {
            selectedRating = star.dataset.value;
            stars.forEach(s => s.classList.remove('selected'));
            for (let i = 0; i < selectedRating; i++) stars[i].classList.add('selected');
            document.getElementById('selected-rating').textContent = selectedRating;
        });
    });

  
    document.getElementById('submit-review-btn')?.addEventListener('click', () => {
        const reviewText = document.getElementById('review-text').value.trim();
        const pid = <?= $productId ?>;
        const msg = document.getElementById('review-msg');
        const reviewId = document.getElementById('editing-review-id').value;

        if (!reviewText && selectedRating === 0) {
            msg.textContent = "Please enter a review or select a rating.";
            msg.style.color = "red";
            return;
        }

        const btn = document.getElementById('submit-review-btn');
        btn.disabled = true;

        const ratingToSend = selectedRating === 0 ? "" : selectedRating;
        const formData = `product_id=${pid}&rating=${ratingToSend}&review=${encodeURIComponent(reviewText)}&review_id=${reviewId}`;

        fetch('submit_review.php', {
            method: 'POST',
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            msg.textContent = res.message;
            msg.style.color = res.success ? "green" : "red";
            if (res.success) {
                document.getElementById('review-text').value = '';
                stars.forEach(s => s.classList.remove('selected'));
                document.getElementById('selected-rating').textContent = 0;
                selectedRating = 0;
                document.getElementById('editing-review-id').value = '';
                setTimeout(() => location.reload(), 500);
            }
        })
        .catch(() => {
            msg.textContent = "Failed to submit review.";
            msg.style.color = "red";
        })
        .finally(() => btn.disabled = false);
    });

    document.querySelector('.review-list')?.addEventListener('click', e => {
        
        const editBtn = e.target.closest('.edit-review-btn');
        if (editBtn) {
            const reviewId = editBtn.dataset.id;
            const oldText = editBtn.dataset.text;
            const oldRating = parseInt(editBtn.dataset.rating);

            document.getElementById('review-text').value = oldText;
            selectedRating = oldRating;
            stars.forEach((s,i) => s.classList.toggle('selected', i < oldRating));
            document.getElementById('selected-rating').textContent = oldRating;
            document.getElementById('editing-review-id').value = reviewId;
        }

        
        const delBtn = e.target.closest('.delete-review-btn');
        if (delBtn) {
            if (!confirm("Are you sure you want to delete this review?")) return;
            const reviewId = delBtn.dataset.id;

            fetch("review_delete.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `id=${reviewId}`
            })
            .then(r => r.json())
            .then(res => {
                alert(res.message);
                if (res.success) location.reload();
            });
        }
    });
});


</script>

</body>
</html>
<?php include '../sb_foot.php'; ?>
