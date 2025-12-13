<?php
$_title = 'Welcome to SB Online';
include 'sb_head.php';


require_once __DIR__ . '/sb_base.php';  
require_once __DIR__ . '/page/product_functions.php';


$topSellingBooks = get_top_selling_products($pdo, 6);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_title; ?></title>
    <link rel="stylesheet" href="sb_style.css">
</head>
<body>
    <main class="container">

  <!-- Hero Section -->
<section class="hero" id="home">
    <div class="hero-content">
        <div class="hero-content">
            <h1>Discover Your Next Great Read</h1>
            <p>Explore thousands of books across every genre. From timeless classics to the latest bestsellers — all in one place.</p>
            <a href="#featured" class="btn">Browse Books</a>
        </div>
    </div>
</section>
<section class="top-selling-books" id="top-selling">
    <h2>Top Selling Books</h2>
    <div class="book-grid">
        <?php foreach ($topSellingBooks as $book): ?>
            <?php
           
                $image_url = 'https://via.placeholder.com/200x300/95a5a6/ffffff?text=No+Image';
                if (!empty($book['cover_image'])) {
                    if (filter_var($book['cover_image'], FILTER_VALIDATE_URL)) {
                        $image_url = $book['cover_image'];
                    } else {
                        $image_url = 'page/uploads/products/' . $book['cover_image'];
                        if (!file_exists($image_url)) {
                            $image_url = 'https://via.placeholder.com/200x300/95a5a6/ffffff?text=Image+Missing';
                        }
                    }
                }

            
                $stock_status = $book['stock_status'] ?? 'in_stock';

               
                $avg_rating = $book['avg_rating'] ?? 0;
                $review_count = $book['review_count'] ?? 0;
                $rounded = round($avg_rating);
            ?>
            <div class="book-card">
                <div class="book-cover">
                    <img src="<?= htmlspecialchars($image_url) ?>" 
                         alt="<?= htmlspecialchars($book['title']) ?>"
                         onerror="this.src='https://via.placeholder.com/200x300/95a5a6/ffffff?text=Image+Error'">
                </div>
                <div class="book-info">
                    <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                    <?php if (!empty($book['author'])): ?>
                        <div class="book-author">by <?= htmlspecialchars($book['author']) ?></div>
                    <?php endif; ?>

                    
                    <div class="book-rating">
    <?php for ($i = 1; $i <= 5; $i++): ?>
        <span class="star <?= $i <= $rounded ? 'filled' : 'empty' ?>">★</span>
    <?php endfor; ?>
    <span class="rating-text">(<?= number_format($avg_rating,2) ?> / 5, <?= $review_count ?> reviews)</span>
</div>


                    <div class="book-price">$<?= number_format($book['price'], 2) ?></div>
                    <span class="stock-status <?= $stock_status ?>">
                        <?php
                        switch($stock_status) {
                            case 'in_stock': echo 'In Stock'; break;
                            case 'low_stock': echo 'Low Stock'; break;
                            case 'out_of_stock': echo 'Out of Stock'; break;
                        }
                        ?>
                    </span>
                    
                    <div class="book-actions" style="margin-top: 10px; display:flex; gap:10px;">
                        <a href="page/product_details.php?id=<?= $book['id'] ?>" 
                           class="btn btn-outline" style="font-size:14px;">Details</a>
                        <button class="btn btn-primary add-to-cart" 
                                data-product-id="<?= $book['id'] ?>"
                                <?= $stock_status == 'out_of_stock' ? 'disabled' : '' ?>
                                style="font-size:14px;">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>


        <!-- Featured Books -->
        <section class="featured-books">
            <h2>Featured Books</h2>
            <div class="book-grid">
                <!-- Book 1 -->
                <div class="book-card">
                    <div class="book-cover">
                        <span>Book Cover</span>
                    </div>
                    <div class="book-info">
                        <div class="book-title">The Great Gatsby</div>
                        <div class="book-author">F. Scott Fitzgerald</div>
                        <div class="book-price">$10.99</div>
                    </div>
                </div>

                <!-- Book 2 -->
                <div class="book-card">
                    <div class="book-cover">
                        <span>Book Cover</span>
                    </div>
                    <div class="book-info">
                        <div class="book-title">1984</div>
                        <div class="book-author">George Orwell</div>
                        <div class="book-price">$8.99</div>
                    </div>
                </div>

                <!-- Book 3 -->
                <div class="book-card">
                    <div class="book-cover">
                        <span>Book Cover</span>
                    </div>
                    <div class="book-info">
                        <div class="book-title">To Kill a Mockingbird</div>
                        <div class="book-author">Harper Lee</div>
                        <div class="book-price">$12.99</div>
                    </div>
                </div>

        </section>

        <!-- Call to Action -->
        <section class="hero">
            <h2>Ready to Explore More?</h2>
            <p>Browse our complete collection of books and discover your next great read.</p>
            <a href="product_view.php" style="display: inline-block; background: #e74c3c; color: white; padding: 12px 30px; border-radius: 4px; margin-top: 1rem;">View All Books</a>
        </section>

        <!-- Additional Sections -->
        <section style="margin: 4rem 0;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div style="text-align: center; padding: 2rem; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3 style="color: #2c3e50; margin-bottom: 1rem;">📖 Free Shipping</h3>
                    <p>Free delivery on orders over $25. Fast and reliable shipping to your doorstep.</p>
                </div>
                
                <div style="text-align: center; padding: 2rem; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3 style="color: #2c3e50; margin-bottom: 1rem;">⭐ Customer Reviews</h3>
                    <p>Read genuine reviews from our community of book lovers before you buy.</p>
                </div>
                
                <div style="text-align: center; padding: 2rem; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h3 style="color: #2c3e50; margin-bottom: 1rem;">🔒 Secure Payment</h3>
                    <p>Shop with confidence using our secure payment processing system.</p>
                </div>
            </div>
        </section>
    </main>
    <script>
    
    function updateCartBadge(cartCount) {
        const cartBadge = document.querySelector('.cart-badge');
        const cartIconLink = document.querySelector('.cart-icon-link');

        if (cartCount > 0) {
            if (cartBadge) {
                cartBadge.textContent = cartCount;
            } else if (cartIconLink) {
                const newBadge = document.createElement('span');
                newBadge.className = 'cart-badge';
                newBadge.textContent = cartCount;
                cartIconLink.appendChild(newBadge);
            }
        } else if (cartBadge) {
            cartBadge.remove();
        }
    }

    
    function addToCart(productId) {
        fetch('page/cart_add.php', {  
            method: 'POST',
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `product_id=${productId}&qty=1`
        })
        .then(r => r.json())
        .then(data => {
            console.log(data); 
            if (data.ok) {
                alert('Product added to cart!');
                updateCartBadge(data.cartCount);
            } else {
                alert(data.message || 'Unable to add to cart.');
            }
        })
        .catch(() => alert('Unable to add to cart.'));
    }

   
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-to-cart')) {
            const productId = e.target.dataset.productId;
            addToCart(productId);
        }
    });
</script>

</body>
</html>
<?php include 'sb_foot.php'; ?>
