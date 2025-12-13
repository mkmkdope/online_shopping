
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


.review-edit-form {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 20px;
    margin-top: 15px;
}

.edit-rating {
    margin-bottom: 15px;
}

.edit-rating label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #495057;
}

.edit-star-rating {
    display: flex;
    align-items: center;
    gap: 8px;
}

.edit-star-rating span {
    font-size: 1.6em;
    cursor: pointer;
    color: #ccc;
    transition: color 0.2s, transform 0.1s;
}

.edit-star-rating span.selected {
    color: #FFD700;
}

.edit-star-rating span.hovered {
    color: #ffeb3b;
    transform: scale(1.1);
}

.edit-star-rating span:hover {
    transform: scale(1.1);
}

.edit-text label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #495057;
}

.edit-text textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    font-size: 14px;
    resize: vertical;
    min-height: 120px;
    transition: border-color 0.3s;
}

.edit-text textarea:focus {
    outline: none;
    border-color: #4dabf7;
    box-shadow: 0 0 0 3px rgba(77, 171, 247, 0.2);
}

.edit-buttons {
    display: flex;
    gap: 12px;
    margin-top: 20px;
}

.edit-buttons button {
    padding: 10px 24px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
}

.save-edit-btn {
    background-color: #28a745;
    color: white;
}

.save-edit-btn:hover {
    background-color: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2);
}

.save-edit-btn:active {
    transform: translateY(0);
}

.save-edit-btn:disabled {
    background-color: #94d3a2;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.cancel-edit-btn {
    background-color: #6c757d;
    color: white;
}

.cancel-edit-btn:hover {
    background-color: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(108, 117, 125, 0.2);
}

.cancel-edit-btn:active {
    transform: translateY(0);
}

.edit-message {
    margin-top: 12px;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 13px;
}


.review-edit-form h4 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #343a40;
    font-size: 18px;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 10px;
}


.edit-rating > div {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 5px;
}


@media (max-width: 768px) {
    .edit-buttons {
        flex-direction: column;
    }
    
    .edit-buttons button {
        width: 100%;
    }
    
    .edit-star-rating {
        flex-wrap: wrap;
    }
}
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

<!-- User Reviews -->
<div class="review-list">
    <h3>User Reviews</h3>
    
    <?php if (empty($reviews)): ?>
        <p class="no-reviews">No reviews yet.</p>
    <?php else: ?>
        <?php foreach ($reviews as $r): ?>
        <div class="review-item" id="review-<?= $r['id'] ?>">
          
            <div class="review-display">
                <div class="review-header">
                    <strong><?= htmlspecialchars($r['username'] ?? 'User') ?></strong>
                    
                    <?php if ($r['user_id'] == ($_SESSION['user_id'] ?? -1)): ?>
                    <span class="review-actions">
                        <button class="edit-review-btn" onclick="startEditReview(<?= $r['id'] ?>)">Edit</button>
                        <button class="delete-review-btn" onclick="deleteReview(<?= $r['id'] ?>)">Delete</button>
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
            
     
<div class="review-edit-form" id="edit-form-<?= $r['id'] ?>" style="display: none;">
    <h4>Edit Your Review</h4>
    
    <div class="edit-rating">
        <label>Rating:</label>
        <div class="rating-input">
            <div class="star-rating edit-star-rating" id="edit-rating-<?= $r['id'] ?>">
                <?php for ($i=1; $i<=5; $i++): ?>
                <span data-value="<?= $i ?>" class="<?= $i <= $r['rating'] ? 'selected' : '' ?>">☆</span>
                <?php endfor; ?>
                <div class="rating-display">
                    <span id="edit-selected-rating-<?= $r['id'] ?>" class="rating-value"><?= $r['rating'] ?></span>
                    <span class="rating-max">/5</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="edit-text">
        <label for="edit-textarea-<?= $r['id'] ?>">Review:</label>
        <textarea 
            id="edit-textarea-<?= $r['id'] ?>" 
            rows="4"
            placeholder="Share your updated thoughts on this product..."
        ><?= htmlspecialchars($r['review']) ?></textarea>
    </div>
    
    <div class="edit-buttons">
        <button class="save-edit-btn" onclick="saveEditedReview(<?= $r['id'] ?>)">
            <span class="btn-text">Save Changes</span>
            <span class="btn-spinner" style="display:none;">Saving...</span>
        </button>
        <button class="cancel-edit-btn" onclick="cancelEditReview(<?= $r['id'] ?>)">Cancel</button>
    </div>
    <p class="edit-message" id="edit-msg-<?= $r['id'] ?>"></p>
</div>
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
function addToCart(pid){
    fetch('cart_add.php',{
        method:'POST',
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:`product_id=${pid}&qty=1`
    })
    .then(r=>r.json())
    .then(d=>{
        if(d.ok){
            alert('Product added!');
            updateCartBadge(d.cartCount);
        } else {
            alert(d.message||'Failed');
        }
    })
    .catch(()=>alert('Failed'));
}

function buyNow(pid){
    fetch('cart_add.php',{
        method:'POST',
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:`product_id=${pid}&qty=1`
    })
    .then(r=>r.json())
    .then(d=>{
        if(d.ok){
            updateCartBadge(d.cartCount);
            window.location.href='cart_view.php';
        } else {
            alert(d.message||'Failed');
        }
    })
    .catch(()=>alert('Failed'));
}

// Keyboard arrow for gallery
document.addEventListener('keydown',e=>{
    const thumbs=document.querySelectorAll('.gallery-thumb');
    if(thumbs.length<=1) return;
    const active=document.querySelector('.gallery-thumb.active');
    let idx=Array.from(thumbs).indexOf(active);
    if(e.key==='ArrowRight'){
        e.preventDefault(); 
        idx=(idx+1)%thumbs.length; 
        thumbs[idx].click();
    }
    if(e.key==='ArrowLeft'){
        e.preventDefault(); 
        idx=(idx-1+thumbs.length)%thumbs.length; 
        thumbs[idx].click();
    }
});


const stars = document.querySelectorAll('#star-rating span[data-value]');
let selectedRating = 0;

if (stars.length > 0) {
    stars.forEach(star => {
        star.addEventListener('mouseover', () => {
            stars.forEach(s => s.classList.remove('hovered'));
            for (let i = 0; i < star.dataset.value; i++) {
                stars[i].classList.add('hovered');
            }
        });
        star.addEventListener('mouseout', () => {
            stars.forEach(s => s.classList.remove('hovered'));
        });
        star.addEventListener('click', () => {
            selectedRating = parseInt(star.dataset.value);
            stars.forEach(s => s.classList.remove('selected'));
            for (let i = 0; i < selectedRating; i++) {
                stars[i].classList.add('selected');
            }
            document.getElementById('selected-rating').textContent = selectedRating;
        });
    });
}

document.addEventListener('DOMContentLoaded', () => {
    
    document.getElementById('submit-review-btn')?.addEventListener('click', () => {
        const reviewText = document.getElementById('review-text').value.trim();
        const pid = <?= $productId ?>;
        const msg = document.getElementById('review-msg');
        
        if (!reviewText && selectedRating === 0) {
            msg.textContent = "Please enter a review or select a rating.";
            msg.style.color = "red";
            return;
        }
        
        const btn = document.getElementById('submit-review-btn');
        btn.disabled = true;
        
        const ratingToSend = selectedRating === 0 ? "" : selectedRating;
        const formData = `product_id=${pid}&rating=${ratingToSend}&review=${encodeURIComponent(reviewText)}`;
        
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
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(() => {
            msg.textContent = "Failed to submit review.";
            msg.style.color = "red";
        })
        .finally(() => btn.disabled = false);
    });
});


function startEditReview(reviewId) {
   
    document.querySelector(`#review-${reviewId} .review-display`).style.display = 'none';

    document.querySelector(`#edit-form-${reviewId}`).style.display = 'block';
    

    initEditRating(reviewId);
}

function initEditRating(reviewId) {
    const stars = document.querySelectorAll(`#edit-rating-${reviewId} span[data-value]`);
    let selectedRating = parseInt(document.querySelector(`#edit-selected-rating-${reviewId}`).textContent) || 0;
    
    
    stars.forEach(star => {
        star.replaceWith(star.cloneNode(true));
    });
    
    
    const newStars = document.querySelectorAll(`#edit-rating-${reviewId} span[data-value]`);
    
    
    newStars.forEach(star => {
        if (parseInt(star.dataset.value) <= selectedRating) {
            star.classList.add('selected');
        } else {
            star.classList.remove('selected');
        }
    });
    
    
    newStars.forEach(star => {
        star.addEventListener('mouseover', () => {
            const hoverRating = parseInt(star.dataset.value);
            newStars.forEach((s, index) => {
                if (index < hoverRating) {
                    s.classList.add('hovered');
                } else {
                    s.classList.remove('hovered');
                }
            });
        });
        
        star.addEventListener('mouseout', () => {
            newStars.forEach(s => s.classList.remove('hovered'));
        });
        
        star.addEventListener('click', () => {
            selectedRating = parseInt(star.dataset.value);
            document.querySelector(`#edit-selected-rating-${reviewId}`).textContent = selectedRating;
            
            
            newStars.forEach((s, index) => {
                if (index < selectedRating) {
                    s.classList.add('selected');
                } else {
                    s.classList.remove('selected');
                }
            });
        });
    });
}
function saveEditedReview(reviewId) {
    const newRating = parseInt(document.querySelector(`#edit-selected-rating-${reviewId}`).textContent) || 0;
    const newText = document.querySelector(`#edit-textarea-${reviewId}`).value.trim();
    const msgElement = document.querySelector(`#edit-msg-${reviewId}`);
    const saveBtn = document.querySelector(`#edit-form-${reviewId} .save-edit-btn`);
    const btnText = saveBtn.querySelector('.btn-text');
    const btnSpinner = saveBtn.querySelector('.btn-spinner');
   
    if (newRating === 0 && !newText) {
        msgElement.textContent = "Please provide at least a rating or review content.";
        msgElement.style.color = "#dc3545";
        msgElement.style.backgroundColor = "#f8d7da";
        msgElement.style.border = "1px solid #f5c6cb";
        msgElement.style.padding = "8px 12px";
        msgElement.style.borderRadius = "4px";
        return;
    }
    
    saveBtn.disabled = true;
    if (btnText && btnSpinner) {
        btnText.style.display = 'none';
        btnSpinner.style.display = 'inline';
    }
    msgElement.textContent = "Saving your review...";
    msgElement.style.color = "#0066cc";
    msgElement.style.backgroundColor = "#e7f1ff";
    msgElement.style.border = "1px solid #b3d7ff";
    msgElement.style.padding = "8px 12px";
    msgElement.style.borderRadius = "4px";
    
    fetch('submit_review.php', {
        method: 'POST',
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `review_id=${reviewId}&rating=${newRating}&review=${encodeURIComponent(newText)}`
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            msgElement.textContent = res.message + " Refreshing...";
            msgElement.style.color = "#155724";
            msgElement.style.backgroundColor = "#d4edda";
            msgElement.style.border = "1px solid #c3e6cb";
            
         
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            msgElement.textContent = res.message;
            msgElement.style.color = "#721c24";
            msgElement.style.backgroundColor = "#f8d7da";
            msgElement.style.border = "1px solid #f5c6cb";
            
            if (btnText && btnSpinner) {
                btnText.style.display = 'inline';
                btnSpinner.style.display = 'none';
            }
            saveBtn.disabled = false;
        }
    })
    .catch(() => {
        msgElement.textContent = "Failed to update review. Please try again.";
        msgElement.style.color = "#721c24";
        msgElement.style.backgroundColor = "#f8d7da";
        msgElement.style.border = "1px solid #f5c6cb";
        
        if (btnText && btnSpinner) {
            btnText.style.display = 'inline';
            btnSpinner.style.display = 'none';
        }
        saveBtn.disabled = false;
    });
}

function cancelEditReview(reviewId) {
    
    document.querySelector(`#review-${reviewId} .review-display`).style.display = 'block';
    
    document.querySelector(`#edit-form-${reviewId}`).style.display = 'none';
}

function deleteReview(reviewId) {
    if (!confirm("Are you sure you want to delete this review?")) return;
    
    fetch("review_delete.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `review_id=${reviewId}`
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message);
        if (res.success) {
            
            const reviewElement = document.querySelector(`#review-${reviewId}`);
            if (reviewElement) {
                reviewElement.remove();
            }
            
           
            setTimeout(() => {
                location.reload();
            }, 500);
        }
    })
    .catch(() => {
        alert("Failed to delete review.");
    });
}
</script>

</body>
</html>
<?php include '../sb_foot.php'; ?>

