<?php
require_once __DIR__ . '/../sb_base.php';  
require_once __DIR__ . '/product_functions.php';

// Variables
$searchQuery = '';
$searchResults = [];
$totalResults = 0;

// Get all products function
function get_all_products(): array {
    global $conn;
    
    $sql = "SELECT * FROM products";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Search
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $searchQuery = trim($_GET['search']);
    $like = "%" . $searchQuery . "%";

    $sql = "
        SELECT *
        FROM products
        WHERE title LIKE ?
           OR author LIKE ?
           OR publisher LIKE ?
           OR description LIKE ?
        ORDER BY created_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$like, $like, $like, $like]);
    $searchResults = $stmt->fetchAll();
    $totalResults = count($searchResults);

} else {
    // Show all products
    $sql = "SELECT * FROM products ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $searchResults = $stmt->fetchAll();
    $totalResults = count($searchResults);
}

$_title = 'Book Search';
include '../sb_head.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Book Shop</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .products-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 32px;
            color: #222;
        }

        .search-container {
            margin-bottom: 40px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .search-input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
        }

        .search-button {
            padding: 12px 30px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-button:hover {
            background-color: #45a049;
        }

        .search-results-info {
            color: #666;
            font-size: 14px;
            margin-top: 10px;
        }

        .search-results-info .highlight {
            font-weight: 600;
            color: #4CAF50;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 8px;
            color: #999;
            font-size: 18px;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }

        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .product-image {
            width: 100%;
            height: 280px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .no-image {
            color: #999;
            font-size: 14px;
            text-align: center;
        }

        .product-details {
            padding: 15px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .product-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #222;
            line-height: 1.3;
            min-height: 50px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-author {
            color: #777;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .product-price {
            font-size: 22px;
            font-weight: 700;
            color: #4CAF50;
            margin-bottom: 15px;
        }

        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: auto;
        }

        .add-to-cart-btn,
        .view-details-btn {
            flex: 1;
            padding: 10px 12px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .add-to-cart-btn {
            background-color: #4CAF50;
            color: white;
        }

        .add-to-cart-btn:hover:not(:disabled) {
            background-color: #45a049;
        }

        .add-to-cart-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .view-details-btn {
            background-color: #f0f0f0;
            color: #333;
            border: 1px solid #ddd;
        }

        .view-details-btn:hover {
            background-color: #e0e0e0;
        }

        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
                gap: 15px;
            }

            h1 {
                font-size: 24px;
            }

            .search-form {
                flex-direction: column;
            }

            .product-image {
                height: 200px;
            }
        }
    </style>

</head>
<body>

<div class="products-container">
    <h1><?php echo $searchQuery ? 'Search Results' : 'Our Books Collection'; ?></h1>

    <!-- Search -->
    <div class="search-container">
        <form method="GET" class="search-form">
            <input type="text" name="search" class="search-input"
                   placeholder="Search by title, author, publisher..."
                   value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit" class="search-button">
                <?php echo $searchQuery ? 'Search Again' : 'Search Books'; ?>
            </button>
        </form>

        <?php if ($searchQuery): ?>
            <div class="search-results-info">
                Found <?php echo $totalResults ?> book(s)
                for "<span class="highlight"><?php echo htmlspecialchars($searchQuery); ?></span>"
            </div>
        <?php endif; ?>
    </div>

    <!-- No results -->
    <?php if ($totalResults === 0): ?>
        <div class="no-results">No books found.</div>
    <?php else: ?>

    <!-- Products Grid -->
    <div class="products-grid">
        <?php foreach ($searchResults as $product): ?>
        <div class="product-card">
            
            <!-- Product Image -->
            <div class="product-image">
                <?php if (!empty($product['cover_image'])): ?>
                    <img src="../uploads/products/<?php echo htmlspecialchars($product['cover_image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['title']); ?>">
                <?php else: ?>
                    <div class="no-image">No Image</div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="product-details">
                <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                <p class="product-author">by <?php echo htmlspecialchars($product['author']); ?></p>

                <div class="product-price">
                    $<?php echo number_format($product['price'], 2); ?>
                </div>

                <!-- Buttons -->
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
                            onclick="window.location='product_details.php?id=<?php echo $product['id']; ?>'">
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

        fetch('../cart_add.php', {
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
</script>

</body>
</html>

<?php include '../sb_foot.php'; ?>