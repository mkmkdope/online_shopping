<?php
// Database configuration
$host = 'localhost';
$dbname = 'sbonline';
$username = 'root';
$password = '';

// Initialize variables
$product = null;
$productImages = [];
$relatedProducts = [];
$error = '';

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $error = "Product ID is required.";
} else {
    $productId = intval($_GET['id']);
    
    try {
        // Create PDO connection
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Fetch product details with category information
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
            // Fetch product images
            $imagesSql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order";
            $imagesStmt = $pdo->prepare($imagesSql);
            $imagesStmt->execute([$productId]);
            $productImages = $imagesStmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch related products (same category)
            if (!empty($product['category_id'])) {
                $relatedSql = "SELECT p.*, c.name as category_name 
                              FROM products p 
                              LEFT JOIN categories c ON p.category_id = c.id 
                              WHERE p.category_id = ? AND p.id != ? 
                              ORDER BY RAND() 
                              LIMIT 4";
                $relatedStmt = $pdo->prepare($relatedSql);
                $relatedStmt->execute([$product['category_id'], $productId]);
                $relatedProducts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // If no related products from same category, get random products
            if (empty($relatedProducts)) {
                $randomSql = "SELECT p.*, c.name as category_name 
                             FROM products p 
                             LEFT JOIN categories c ON p.category_id = c.id 
                             WHERE p.id != ? 
                             ORDER BY RAND() 
                             LIMIT 4";
                $randomStmt = $pdo->prepare($randomSql);
                $randomStmt->execute([$productId]);
                $relatedProducts = $randomStmt->fetchAll(PDO::FETCH_ASSOC);
            }
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
            <!-- Back Button -->
            <a href="product.php" class="back-button">&larr; Back to Products</a>

            <!-- Product Details -->
            <div class="product-details">
                <!-- Product Images -->
                <div class="product-images">
                    <div class="main-image">
                        <?php if (!empty($productImages)): ?>
                            <img src="uploads/products/<?php echo htmlspecialchars($productImages[0]['image_path']); ?>" 
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
                                     onclick="changeMainImage('<?php echo htmlspecialchars($image['image_path']); ?>', this)">
                                    <img src="uploads/products/<?php echo htmlspecialchars($image['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($product['title']); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Information -->
                <div class="product-info">
                    <!-- Category Badge -->
                    <?php if (!empty($product['category_name'])): ?>
                        <div class="product-category category-highlight">
                            <?php echo htmlspecialchars($product['category_name']); ?>
                        </div>
                    <?php endif; ?>

                    <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>
                    <p class="product-author">by <?php echo htmlspecialchars($product['author']); ?></p>
                    
                    <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>

                    <!-- Stock Information -->
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
                            <button class="add-to-cart-btn" 
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-title="<?php echo htmlspecialchars($product['title']); ?>"
                                    data-product-price="<?php echo $product['price']; ?>">
                                Add to Cart
                            </button>
                            <button class="buy-now-btn" 
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-title="<?php echo htmlspecialchars($product['title']); ?>"
                                    data-product-price="<?php echo $product['price']; ?>">
                                Buy Now
                            </button>
                        <?php else: ?>
                            <button class="add-to-cart-btn" disabled>Out of Stock</button>
                            <button class="buy-now-btn" disabled>Out of Stock</button>
                        <?php endif; ?>
                    </div>

                    <!-- Product Meta Details -->
                    <div class="product-meta-details">
                        <div class="meta-item">
                            <span class="meta-label">Publisher:</span>
                            <span class="meta-value">
                                <?php echo !empty($product['publisher']) ? htmlspecialchars($product['publisher']) : 'Not specified'; ?>
                            </span>
                        </div>
                        
                        <div class="meta-item">
                            <span class="meta-label">Publication Date:</span>
                            <span class="meta-value">
                                <?php echo !empty($product['publication_date']) ? date('F j, Y', strtotime($product['publication_date'])) : 'Not specified'; ?>
                            </span>
                        </div>
                        
                        <div class="meta-item">
                            <span class="meta-label">Pages:</span>
                            <span class="meta-value">
                                <?php echo !empty($product['pages']) ? number_format($product['pages']) : 'Not specified'; ?>
                            </span>
                        </div>
                        
                        <div class="meta-item">
                            <span class="meta-label">Category:</span>
                            <span class="meta-value">
                                <?php echo !empty($product['category_name']) ? htmlspecialchars($product['category_name']) : 'Uncategorized'; ?>
                            </span>
                        </div>
                        
                        <div class="meta-item">
                            <span class="meta-label">ISBN/ID:</span>
                            <span class="meta-value">#<?php echo $product['id']; ?></span>
                        </div>
                    </div>

                    <!-- Full Description -->
                    <?php if (!empty($product['description'])): ?>
                        <div class="product-description-full">
                            <h3>Description</h3>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Related Products Section -->
            <?php if (!empty($relatedProducts)): ?>
                <div class="related-products">
                    <h2>Related Books</h2>
                    <div class="products-grid">
                        <?php foreach ($relatedProducts as $relatedProduct): ?>
                            <div class="product-card">
                                <?php
                                // Get the first product image for this product
                                $relatedProductId = $relatedProduct['id'];
                                $imageSql = "SELECT image_path FROM product_images WHERE product_id = ? ORDER BY sort_order LIMIT 1";
                                $imageStmt = $pdo->prepare($imageSql);
                                $imageStmt->execute([$relatedProductId]);
                                $relatedProductImage = $imageStmt->fetch(PDO::FETCH_ASSOC);
                                ?>
                                
                                <div class="product-image">
                                    <?php if ($relatedProductImage && !empty($relatedProductImage['image_path'])): ?>
                                        <img src="uploads/products/<?php echo htmlspecialchars($relatedProductImage['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($relatedProduct['title']); ?>"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <div class="no-image" style="display: none;">Image Failed to Load</div>
                                    <?php else: ?>
                                        <div class="no-image">No Image Available</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-details">
                                    <!-- Category Badge -->
                                    <?php if (!empty($relatedProduct['category_name'])): ?>
                                        <div class="product-category">
                                            <?php echo htmlspecialchars($relatedProduct['category_name']); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <h3 class="product-title">
                                        <a href="product_details.php?id=<?php echo $relatedProduct['id']; ?>" 
                                           style="text-decoration: none; color: inherit;">
                                            <?php echo htmlspecialchars($relatedProduct['title']); ?>
                                        </a>
                                    </h3>
                                    
                                    <p class="product-author">
                                        by <?php echo htmlspecialchars($relatedProduct['author']); ?>
                                    </p>
                                    
                                    <div class="product-price">
                                        $<?php echo number_format($relatedProduct['price'], 2); ?>
                                    </div>
                                    
                                    <div class="product-actions">
                                        <?php if ($relatedProduct['stock_quantity'] > 0): ?>
                                            <button class="add-to-cart-btn" 
                                                    data-product-id="<?php echo $relatedProduct['id']; ?>"
                                                    data-product-title="<?php echo htmlspecialchars($relatedProduct['title']); ?>"
                                                    data-product-price="<?php echo $relatedProduct['price']; ?>">
                                                Add to Cart
                                            </button>
                                        <?php else: ?>
                                            <button class="add-to-cart-btn" disabled>Out of Stock</button>
                                        <?php endif; ?>
                                        
                                        <a href="product_details.php?id=<?php echo $relatedProduct['id']; ?>" 
                                           class="view-details-btn" 
                                           style="text-decoration: none; text-align: center;">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <script>
        // Change main product image
        function changeMainImage(imagePath, element) {
            const mainImage = document.getElementById('mainProductImage');
            if (mainImage) {
                mainImage.src = 'uploads/products/' + imagePath;
            }
            
            // Update active thumbnail
            document.querySelectorAll('.gallery-thumb').forEach(thumb => {
                thumb.classList.remove('active');
            });
            element.classList.add('active');
        }

        // Add to cart functionality
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (this.disabled) return;
                
                const productId = this.dataset.productId;
                const productTitle = this.dataset.productTitle;
                const productPrice = this.dataset.productPrice;
                
                // Add your cart logic here
                console.log('Adding to cart:', productId, productTitle, productPrice);
                
                // Example: Show confirmation
                alert(`Added "${productTitle}" to cart!`);
                
                // You can integrate with your shopping cart system here
                // Example: 
                // addToCart(productId, productTitle, productPrice, 1);
            });
        });

        // Buy now functionality
        document.querySelectorAll('.buy-now-btn').forEach(button => {
            button.addEventListener('click', function() {
                if (this.disabled) return;
                
                const productId = this.dataset.productId;
                const productTitle = this.dataset.productTitle;
                const productPrice = this.dataset.productPrice;
                
                // Add your buy now logic here
                console.log('Buy now:', productId, productTitle, productPrice);
                
                // Example: Redirect to checkout or add to cart and go to checkout
                alert(`Proceeding to checkout with "${productTitle}"!`);
                
                // You can integrate with your checkout system here
                // Example:
                // addToCart(productId, productTitle, productPrice, 1);
                // window.location.href = 'checkout.php';
            });
        });

        // Keyboard navigation for image gallery
        document.addEventListener('keydown', function(e) {
            const thumbnails = document.querySelectorAll('.gallery-thumb');
            if (thumbnails.length <= 1) return;

            const activeThumb = document.querySelector('.gallery-thumb.active');
            let currentIndex = Array.from(thumbnails).indexOf(activeThumb);

            if (e.key === 'ArrowRight') {
                e.preventDefault();
                currentIndex = (currentIndex + 1) % thumbnails.length;
                thumbnails[currentIndex].click();
            } else if (e.key === 'ArrowLeft') {
                e.preventDefault();
                currentIndex = (currentIndex - 1 + thumbnails.length) % thumbnails.length;
                thumbnails[currentIndex].click();
            }
        });
    </script>
</body>
</html>
<?php
include '../sb_foot.php';