<?php
// Database configuration
$host = 'localhost';
$dbname = 'sbonline';
$username = 'root';
$password = '';

// Initialize variables at the top
$searchQuery = '';
$categoryFilter = '';
$searchResults = [];
$totalResults = 0;
$categories = [];

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch all categories for filter dropdown
    $categoriesSql = "SELECT id, name FROM categories ORDER BY sort_order, name";
    $categoriesStmt = $pdo->prepare($categoriesSql);
    $categoriesStmt->execute();
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check if search query exists
    $searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
    $categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : '';
    
    // Build the base query with category join
    $sql = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE 1=1";
    
    $params = [];
    $paramTypes = [];
    
    // Add search conditions
    if (!empty($searchQuery)) {
        $searchParam = '%' . $searchQuery . '%';
        $sql .= " AND (p.title LIKE :search 
                   OR p.author LIKE :search 
                   OR p.publisher LIKE :search 
                   OR p.description LIKE :search)";
        $params[':search'] = $searchParam;
        $paramTypes[':search'] = PDO::PARAM_STR;
    }
    
    // Add category filter
    if (!empty($categoryFilter) && is_numeric($categoryFilter)) {
        $sql .= " AND p.category_id = :category_id";
        $params[':category_id'] = $categoryFilter;
        $paramTypes[':category_id'] = PDO::PARAM_INT;
    }
    
    // Add ordering
    if (!empty($searchQuery)) {
        $sql .= " ORDER BY 
                    CASE 
                        WHEN p.title LIKE :search THEN 1 
                        WHEN p.author LIKE :search THEN 2 
                        WHEN p.publisher LIKE :search THEN 3 
                        ELSE 4 
                    END,
                    p.created_at DESC";
    } else {
        $sql .= " ORDER BY p.created_at DESC";
    }
    
    $stmt = $pdo->prepare($sql);
    
    // Bind parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, $paramTypes[$key]);
    }
    
    $stmt->execute();
    
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totalResults = count($searchResults);
    
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
    <link rel="stylesheet" href="/css/product.css">
    <style>
        .filter-container {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .filter-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #495057;
        }
        
        .filter-select, .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .filter-button {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .filter-button:hover {
            background: #5a6268;
        }
        
        .active-filters {
            margin: 15px 0;
            padding: 10px 15px;
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
        }
        
        .filter-tag {
            display: inline-block;
            background: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-right: 8px;
        }
        
        .clear-filters {
            color: #dc3545;
            text-decoration: none;
            margin-left: 10px;
            font-size: 14px;
        }
        
        .clear-filters:hover {
            text-decoration: underline;
        }
        
        .product-category {
            display: inline-block;
            background: #e9ecef;
            color: #495057;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-bottom: 8px;
        }
        
        .category-highlight {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
            }
            
            .filter-group {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="products-container">
        <h1><?php echo $searchQuery ? 'Search Results' : 'Our Books Collection'; ?></h1>
        
        <!-- Search and Filter Form -->
        <div class="search-container">
            <form method="GET" class="search-form" id="searchForm">
                <div class="filter-container">
                    <div class="filter-form">
                        <div class="filter-group">
                            <label for="search">Search Books</label>
                            <input type="text" 
                                   name="search" 
                                   class="search-input" 
                                   id="searchInput"
                                   placeholder="Search by title, author, publisher..."
                                   value="<?php echo htmlspecialchars($searchQuery); ?>"
                                   aria-label="Search books">
                        </div>
                        
                        <div class="filter-group">
                            <label for="category">Filter by Category</label>
                            <select name="category" class="filter-select" id="categoryFilter">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                        <?php echo ($categoryFilter == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <button type="submit" class="search-button" id="searchButton">
                                <?php echo ($searchQuery || $categoryFilter) ? 'Apply Filters' : 'Search Books'; ?>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Active Filters Display -->
                    <?php if (!empty($searchQuery) || !empty($categoryFilter)): ?>
                        <div class="active-filters">
                            <strong>Active Filters:</strong>
                            <?php if (!empty($searchQuery)): ?>
                                <span class="filter-tag">Search: "<?php echo htmlspecialchars($searchQuery); ?>"</span>
                            <?php endif; ?>
                            <?php if (!empty($categoryFilter)): 
                                $currentCategoryName = '';
                                foreach ($categories as $cat) {
                                    if ($cat['id'] == $categoryFilter) {
                                        $currentCategoryName = $cat['name'];
                                        break;
                                    }
                                }
                            ?>
                                <span class="filter-tag">Category: <?php echo htmlspecialchars($currentCategoryName); ?></span>
                            <?php endif; ?>
                            <a href="?" class="clear-filters">Clear All Filters</a>
                        </div>
                    <?php endif; ?>
                </div>
            </form>
            
            <?php if (!empty($searchQuery)): ?>
                <div class="search-results-info">
                    <strong>Search Results for:</strong> "<span class="highlight"><?php echo htmlspecialchars($searchQuery); ?></span>"
                    
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
                <?php if (!empty($searchQuery) || !empty($categoryFilter)): ?>
                    <h3>No books found</h3>
                    <p>
                        No books found 
                        <?php if (!empty($searchQuery)): ?>
                            matching "<strong><?php echo htmlspecialchars($searchQuery); ?></strong>"
                        <?php endif; ?>
                        <?php if (!empty($searchQuery) && !empty($categoryFilter)): ?>
                            and
                        <?php endif; ?>
                        <?php if (!empty($categoryFilter)): 
                            $currentCategoryName = '';
                            foreach ($categories as $cat) {
                                if ($cat['id'] == $categoryFilter) {
                                    $currentCategoryName = $cat['name'];
                                    break;
                                }
                            }
                        ?>
                            in category "<strong><?php echo htmlspecialchars($currentCategoryName); ?></strong>"
                        <?php endif; ?>
                    </p>
                    <div class="search-suggestions">
                        <p><strong>Suggestions:</strong></p>
                        <ul>
                            <li>Try different or more general keywords</li>
                            <li>Check your spelling</li>
                            <li>Select a different category</li>
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
                                data-id="<?php echo $product['id']; ?>"
                                data-title="<?php echo htmlspecialchars($product['title']); ?>">
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
       // Add to Cart AJAX
document.querySelectorAll('.add-to-cart-btn:not(:disabled)').forEach(btn => {
    btn.addEventListener('click', function() {
        let id = this.dataset.id;
        let title = this.dataset.title;

        fetch('cart_add.php', {
            method: 'POST',
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `product_id=${id}&qty=1`
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                alert(`Added "${title}" to Cart!`);
            } else {
                alert(data.message);
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Failed to add to cart.');
        });
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

        // Auto-submit form when category changes (optional)
        document.getElementById('categoryFilter')?.addEventListener('change', function() {
            // Only auto-submit if there's already a search query or if a category is selected
            if (this.value || document.getElementById('searchInput').value) {
                this.form.submit();
            }
        });
    </script>
</body>
</html>
<?php
include '../sb_foot.php';