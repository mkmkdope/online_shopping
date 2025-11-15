<?php
// Database configuration
$host = 'localhost';
$dbname = 'sbonline';
$username = 'root';
$password = '';

// Initialize variables at the top
$searchQuery = '';
$searchResults = [];
$totalResults = 0;

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if search query exists
    if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
        $searchQuery = trim($_GET['search']);
        $searchParam = '%' . $searchQuery . '%';
        
        // Enhanced search query
        $sql = "SELECT * FROM products 
                WHERE title LIKE :search 
                   OR author LIKE :search 
                   OR publisher LIKE :search 
                   OR description LIKE :search 
                ORDER BY 
                    CASE 
                        WHEN title LIKE :search THEN 1 
                        WHEN author LIKE :search THEN 2 
                        WHEN publisher LIKE :search THEN 3 
                        ELSE 4 
                    END,
                    created_at DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':search', $searchParam, PDO::PARAM_STR);
        $stmt->execute();
        
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalResults = count($searchResults);
        
    } else {
        // Query to fetch all products if no search
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalResults = count($searchResults);
    }
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$_title = 'Book Search';
include '../sb_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Shop - <?php echo $searchQuery ? 'Search Results' : 'All Books'; ?></title>
    <style>
        .search-container {
            margin: 20px 0;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .search-form {
            display: flex;
            gap: 12px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #dee2e6;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .search-button {
            padding: 12px 25px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .search-button:hover {
            background: #0056b3;
        }
        
        .search-results-info {
            margin: 15px 0;
            padding: 15px;
            background: #e7f3ff;
            border-radius: 6px;
            border-left: 4px solid #007bff;
        }
        
        .clear-search {
            display: inline-block;
            margin-left: 10px;
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }
        
        .clear-search:hover {
            text-decoration: underline;
        }
        
        .search-stats {
            color: #495057;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .no-results {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        
        .no-results h3 {
            color: #495057;
            margin-bottom: 10px;
        }
        
        .search-suggestions {
            margin-top: 15px;
            font-size: 14px;
        }
        
        .highlight {
            background-color: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: 500;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .product-image {
            text-align: center;
            margin-bottom: 15px;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .product-image img {
            max-width: 100%;
            max-height: 180px;
            object-fit: contain;
            border-radius: 4px;
        }
        
        .product-title {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
        }
        
        .product-author {
            color: #666;
            margin-bottom: 10px;
            font-style: italic;
        }
        
        .product-price {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        
        .product-meta {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }
        
        .product-meta p {
            margin: 2px 0;
        }
        
        .product-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .stock-info {
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .in-stock {
            color: #28a745;
            font-weight: bold;
        }
        
        .out-of-stock {
            color: #dc3545;
            font-weight: bold;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .add-to-cart-btn, .view-details-btn {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .add-to-cart-btn {
            background: #28a745;
            color: white;
        }
        
        .add-to-cart-btn:hover:not(:disabled) {
            background: #218838;
        }
        
        .add-to-cart-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .view-details-btn {
            background: #6c757d;
            color: white;
        }
        
        .view-details-btn:hover {
            background: #545b62;
        }
        
        .no-image {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="products-container">
        <h1><?php echo $searchQuery ? 'Search Results' : 'Our Books Collection'; ?></h1>
        
        <!-- Search Form -->
        <div class="search-container">
            <form method="GET" class="search-form" id="searchForm">
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       id="searchInput"
                       placeholder="Search books by title, author, publisher, or description..."
                       value="<?php echo htmlspecialchars($searchQuery); ?>"
                       aria-label="Search books">
                <button type="submit" class="search-button" id="searchButton">
                    <?php echo $searchQuery ? 'Search Again' : 'Search Books'; ?>
                </button>
            </form>
            
            <?php if (!empty($searchQuery)): ?>
                <div class="search-results-info">
                    <strong>Search Results for:</strong> "<span class="highlight"><?php echo htmlspecialchars($searchQuery); ?></span>"
                    <a href="?" class="clear-search">Show All Books</a>
                    
                    <div class="search-stats">
                        Found <?php echo $totalResults; ?> book<?php echo $totalResults != 1 ? 's' : ''; ?> matching your search
                        <?php if ($totalResults > 0): ?>
                            • Sorted by relevance
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if (empty($searchResults)): ?>
            <div class="no-results">
                <?php if (!empty($searchQuery)): ?>
                    <h3>No books found</h3>
                    <p>No books found matching "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>"</p>
                    <div class="search-suggestions">
                        <p><strong>Suggestions:</strong></p>
                        <ul>
                            <li>Try different or more general keywords</li>
                            <li>Check your spelling</li>
                            <li>Search by author name or publisher</li>
                            <li><a href="?">Browse all books</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <h3>No books available</h3>
                    <p>There are currently no books in our collection.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($searchResults as $product): ?>
<div class="product-card">
    <?php
    // Get the first product image for this product
    $productId = $product['id'];
    $imageSql = "SELECT image_path FROM product_images WHERE product_id = ? ORDER BY sort_order LIMIT 1";
    $imageStmt = $pdo->prepare($imageSql);
    $imageStmt->execute([$productId]);
    $productImage = $imageStmt->fetch(PDO::FETCH_ASSOC);
    ?>
    
    <div class="product-image">
        <?php if ($productImage && !empty($productImage['image_path'])): ?>
            <img src="uploads/products/<?php echo htmlspecialchars($productImage['image_path']); ?>" 
                 alt="<?php echo htmlspecialchars($product['title']); ?>"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <div class="no-image" style="display: none;">Image Failed to Load</div>
        <?php else: ?>
            <div class="no-image">No Image Available</div>
        <?php endif; ?>
    </div>
    
    <div class="product-details">
        <h3 class="product-title">
            <?php 
            if (!empty($searchQuery)) {
                echo preg_replace(
                    "/(" . preg_quote($searchQuery, '/') . ")/i", 
                    '<span class="highlight">$1</span>', 
                    htmlspecialchars($product['title'])
                );
            } else {
                echo htmlspecialchars($product['title']);
            }
            ?>
        </h3>
        
        <p class="product-author">
            by 
            <?php 
            if (!empty($searchQuery)) {
                echo preg_replace(
                    "/(" . preg_quote($searchQuery, '/') . ")/i", 
                    '<span class="highlight">$1</span>', 
                    htmlspecialchars($product['author'])
                );
            } else {
                echo htmlspecialchars($product['author']);
            }
            ?>
        </p>
        
        <div class="product-price">
            $<?php echo number_format($product['price'], 2); ?>
        </div>
        
        <div class="product-actions">
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
            
            <button class="view-details-btn" 
                    onclick="viewProductDetails(<?php echo $product['id']; ?>)">
                View Details
            </button>
        </div>
    </div>
</div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

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

        // View product details
        function viewProductDetails(productId) {
window.location.href = 'product_details.php?id=' + productId;
        }

        // Focus on search input when page loads if there's a search query
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.focus();
                if (searchInput.value) {
                    searchInput.select();
                }
            }
        });

        // Quick search on Enter key
        document.querySelector('.search-input')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    </script>
</body>
</html>
<?php
include '../sb_foot.php';