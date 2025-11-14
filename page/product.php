<?php
// Database configuration
$host = 'localhost';
$dbname = 'sbonline';
$username = 'root';
$password = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Query to fetch all products
    $sql = "SELECT * FROM products ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Fetch all products as associative array
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
$_title = 'Index';
include '../sb_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Shop - Products</title>
    <!-- Your existing CSS links here -->
</head>
<body>
    <div class="products-container">
        <h1>Our Books Collection</h1>
        
        <?php if (empty($products)): ?>
            <div class="no-products">
                <p>No products found.</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <?php if (!empty($product['cover_image'])): ?>
                            <div class="product-image">
                                <img src="images/<?php echo htmlspecialchars($product['cover_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['title']); ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-details">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                            <p class="product-author">by <?php echo htmlspecialchars($product['author']); ?></p>
                            
                            <div class="product-price">
                                $<?php echo number_format($product['price'], 2); ?>
                            </div>
                            
                            <div class="product-meta">
                                <?php if (!empty($product['publisher'])): ?>
                                    <p><strong>Publisher:</strong> <?php echo htmlspecialchars($product['publisher']); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['publication_date'])): ?>
                                    <p><strong>Published:</strong> <?php echo date('F j, Y', strtotime($product['publication_date'])); ?></p>
                                <?php endif; ?>
                                
                                <?php if (!empty($product['pages'])): ?>
                                    <p><strong>Pages:</strong> <?php echo $product['pages']; ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($product['description'])): ?>
                                <div class="product-description">
                                    <p><?php echo htmlspecialchars($product['description']); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="stock-info">
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <span class="in-stock">In Stock (<?php echo $product['stock_quantity']; ?> available)</span>
                                <?php else: ?>
                                    <span class="out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <button class="add-to-cart-btn" 
                                        data-product-id="<?php echo $product['id']; ?>"
                                        data-product-title="<?php echo htmlspecialchars($product['title']); ?>"
                                        data-product-price="<?php echo $product['price']; ?>">
                                    Add to Cart
                                </button>
                            <?php else: ?>
                                <button class="add-to-cart-btn" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Your existing JavaScript here -->
    <script>
        // Add to cart functionality
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const productTitle = this.dataset.productTitle;
                const productPrice = this.dataset.productPrice;
                
                // Add your cart logic here
                console.log('Adding to cart:', productId, productTitle, productPrice);
                
                // Example: Show confirmation
                alert(`Added "${productTitle}" to cart!`);
            });
        });
    </script>
<?php
include '../sb_foot.php';