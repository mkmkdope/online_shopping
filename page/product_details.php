<?php
// Database configuration
$host = 'localhost';
$dbname = 'sbonline';
$username = 'root';
$password = '';

// Initialize variables
$product = null;
$productImages = [];
$error = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get product ID from URL
    $productId = $_GET['id'] ?? 0;
    
    if ($productId) {
        // Fetch product details
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Fetch product images if product exists
        if ($product) {
            $sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order, created_at";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$productId]);
            $productImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
    
    if (!$product) {
        $error = "Product not found!";
    }
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

$_title = $product ? $product['title'] : 'Product Not Found';
include '../sb_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($_title); ?> - Book Shop</title>
    <style>
        .product-details-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .back-button:hover {
            background: #545b62;
        }
        
        .product-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 20px;
        }
        
        @media (max-width: 768px) {
            .product-details {
                grid-template-columns: 1fr;
            }
        }
        
        .product-images {
            position: sticky;
            top: 20px;
        }
        
        .main-image {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .main-image img {
            max-width: 100%;
            max-height: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        
        .gallery-thumb {
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 4px;
            transition: border-color 0.3s;
        }
        
        .gallery-thumb:hover,
        .gallery-thumb.active {
            border-color: #007bff;
        }
        
        .gallery-thumb img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .product-info {
            padding: 20px;
        }
        
        .product-title {
            font-size: 28px;
            margin-bottom: 10px;
            color: #333;
        }
        
        .product-author {
            font-size: 20px;
            color: #666;
            margin-bottom: 20px;
            font-style: italic;
        }
        
        .product-price {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 20px;
        }
        
        .product-meta {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .meta-item {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .meta-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .meta-label {
            font-weight: bold;
            color: #495057;
            display: inline-block;
            width: 120px;
        }
        
        .meta-value {
            color: #333;
        }
        
        .product-description {
            line-height: 1.6;
            color: #555;
            margin-bottom: 30px;
        }
        
        .stock-info {
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .in-stock {
            color: #28a745;
            font-weight: bold;
        }
        
        .out-of-stock {
            color: #dc3545;
            font-weight: bold;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .add-to-cart-btn, .buy-now-btn {
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
            flex: 1;
        }
        
        .add-to-cart-btn {
            background: #28a745;
            color: white;
        }
        
        .add-to-cart-btn:hover:not(:disabled) {
            background: #218838;
        }
        
        .buy-now-btn {
            background: #007bff;
            color: white;
        }
        
        .buy-now-btn:hover:not(:disabled) {
            background: #0056b3;
        }
        
        .add-to-cart-btn:disabled,
        .buy-now-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .no-image {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 8px;
            color: #6c757d;
            font-style: italic;
        }
        
        .error-message {
            text-align: center;
            padding: 40px;
            color: #dc3545;
            background: #f8d7da;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="product-details-container">
        <!-- Back Button -->
        <a href="javascript:history.back()" class="back-button">← Back to Results</a>
        
        <?php if ($error): ?>
            <div class="error-message">
                <h2>Error</h2>
                <p><?php echo htmlspecialchars($error); ?></p>
                <a href="search_products.php" class="back-button">Browse All Books</a>
            </div>
        <?php elseif ($product): ?>
            <div class="product-details">
                <!-- Product Images Section -->
                <div class="product-images">
                    <div class="main-image">
                        <?php if (!empty($productImages)): ?>
                            <img src="uploads/products/<?php echo htmlspecialchars($productImages[0]['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                 id="mainProductImage">
                        <?php elseif (!empty($product['cover_image'])): ?>
                            <img src="uploads/products/<?php echo htmlspecialchars($product['cover_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                 id="mainProductImage">
                        <?php else: ?>
                            <div class="no-image">
                                No Image Available
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Image Gallery -->
                    <?php if (!empty($productImages) && count($productImages) > 1): ?>
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
                
                <!-- Product Information Section -->
                <div class="product-info">
                    <h1 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h1>
                    <p class="product-author">by <?php echo htmlspecialchars($product['author']); ?></p>
                    
                    <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                    
                    <div class="product-meta">
                        <?php if (!empty($product['publisher'])): ?>
                            <div class="meta-item">
                                <span class="meta-label">Publisher:</span>
                                <span class="meta-value"><?php echo htmlspecialchars($product['publisher']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['publication_date'])): ?>
                            <div class="meta-item">
                                <span class="meta-label">Published:</span>
                                <span class="meta-value"><?php echo date('F j, Y', strtotime($product['publication_date'])); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['pages'])): ?>
                            <div class="meta-item">
                                <span class="meta-label">Pages:</span>
                                <span class="meta-value"><?php echo number_format($product['pages']); ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="meta-item">
                            <span class="meta-label">Format:</span>
                            <span class="meta-value">Paperback</span>
                        </div>
                    </div>
                    
                    <?php if (!empty($product['description'])): ?>
                        <div class="product-description">
                            <h3>Description</h3>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="stock-info">
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <span class="in-stock">✓ In Stock (<?php echo $product['stock_quantity']; ?> available)</span>
                        <?php else: ?>
                            <span class="out-of-stock">✗ Out of Stock</span>
                        <?php endif; ?>
                    </div>

                    <!-- Quantity Selector -->
                    <div style="margin-bottom: 25px;">
                        <label for="qty-input" style="display: block; margin-bottom: 8px; font-weight: bold; color: #333;">
                            Quantity:
                        </label>
                        <input
                            type="number"
                            id="qty-input"
                            name="qty"
                            value="1"
                            min="1"
                            max="<?php echo $product['stock_quantity']; ?>"
                            style="padding: 10px; width: 100%; max-width: 120px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px;"
                        >
                    </div>

                    <div class="action-buttons">
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <button class="add-to-cart-btn" 
                                    onclick="addToCart(<?php echo $product['id']; ?>)">
                                Add to Cart
                            </button>
                            <button class="buy-now-btn" 
                                    onclick="buyNow(<?php echo $product['id']; ?>)">
                                Buy Now
                            </button>
                        <?php else: ?>
                            <button class="add-to-cart-btn" disabled>Out of Stock</button>
                            <button class="buy-now-btn" disabled>Notify Me</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Change main image when thumbnail is clicked
        function changeMainImage(imagePath, element) {
            const mainImage = document.getElementById('mainProductImage');
            mainImage.src = 'uploads/products/' + imagePath;
            
            // Update active thumbnail
            document.querySelectorAll('.gallery-thumb').forEach(thumb => {
                thumb.classList.remove('active');
            });
            element.classList.add('active');
        }
        
        // Add to cart functionality
        function addToCart(productId) {
    const btn = event.target;
    const qtyInput = document.getElementById('qty-input');
    const quantity = parseInt(qtyInput.value) || 1;

    // 验证数量
    if (quantity < 1) {
        alert('Please enter a valid quantity');
        return;
    }

    // ★ 防止重复点击：禁用按钮
    btn.disabled = true;
    const originalText = btn.textContent;
    btn.textContent = 'Adding...';

    fetch('./cart_add.php', {
        method: 'POST',
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `product_id=${productId}&qty=${quantity}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            alert(`Added ${quantity} item(s) to cart!`);
            qtyInput.value = 1;  // 重置数量输入框
        } else {
            alert(data.message || 'Unable to add to cart.');
            // 失败时重新启用按钮
            btn.disabled = false;
            btn.textContent = originalText;
        }
    })
    .catch(() => {
        alert('Unable to add to cart.');
        // 失败时重新启用按钮
        btn.disabled = false;
        btn.textContent = originalText;
    });
}
        
        // Buy now functionality
        function buyNow(productId) {
            const btn = event.target;
            const qtyInput = document.getElementById('qty-input');
            const quantity = parseInt(qtyInput.value) || 1;

            // 验证数量
            if (quantity < 1) {
                alert('Please enter a valid quantity');
                return;
            }

            // ★ 防止重复点击：禁用按钮
            btn.disabled = true;
            const originalText = btn.textContent;
            btn.textContent = 'Processing...';

            fetch('./cart_add.php', {
                method: 'POST',
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `product_id=${productId}&qty=${quantity}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    window.location.href = 'cart_view.php';
                } else {
                    alert(data.message || 'Unable to add to cart.');
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(() => {
                alert('Unable to add to cart.');
                btn.disabled = false;
                btn.textContent = originalText;
            });
        }
        
        // Keyboard navigation for image gallery
        document.addEventListener('keydown', function(e) {
            const thumbs = document.querySelectorAll('.gallery-thumb');
            const activeThumb = document.querySelector('.gallery-thumb.active');
            
            if (thumbs.length > 1 && activeThumb) {
                let currentIndex = Array.from(thumbs).indexOf(activeThumb);
                
                if (e.key === 'ArrowRight') {
                    currentIndex = (currentIndex + 1) % thumbs.length;
                    thumbs[currentIndex].click();
                } else if (e.key === 'ArrowLeft') {
                    currentIndex = (currentIndex - 1 + thumbs.length) % thumbs.length;
                    thumbs[currentIndex].click();
                }
            }
        });
    </script>
</body>
</html>
<?php
include '../sb_foot.php';
?>
