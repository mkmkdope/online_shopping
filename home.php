<?php
$_title = 'Welcome to BookShop';
include 'sb_head.php';
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
        <section class="hero">
            <h1>Discover Your Next Favorite Book</h1>
            <p>Explore our vast collection of books across all genres. From classic literature to modern bestsellers, find the perfect read for every taste.</p>

            <!-- Search Bar -->
            <form action="product.php" method="GET" class="search-bar">
                <input type="text" name="search" placeholder="Search for books, authors, or categories...">
                <button type="submit">Search</button>
            </form>
        </section>

        <!-- Featured Categories -->
        <section class="categories">
            <h2>Popular Categories</h2>
            <div class="category-list">
                <a href="category.php?cat=fiction" class="category">Fiction</a>
                <a href="category.php?cat=non-fiction" class="category">Non-Fiction</a>
                <a href="category.php?cat=sci-fi" class="category">Science Fiction</a>
                <a href="category.php?cat=fantasy" class="category">Fantasy</a>
                <a href="category.php?cat=mystery" class="category">Mystery</a>
                <a href="category.php?cat=romance" class="category">Romance</a>
                <a href="category.php?cat=biography" class="category">Biography</a>
                <a href="category.php?cat=history" class="category">History</a>
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

                <!-- Book 4 -->
                <div class="book-card">
                    <div class="book-cover">
                        <span>Book Cover</span>
                    </div>
                    <div class="book-info">
                        <div class="book-title">Pride and Prejudice</div>
                        <div class="book-author">Jane Austen</div>
                        <div class="book-price">$9.99</div>
                    </div>
                </div>

                <!-- Book 5 -->
                <div class="book-card">
                    <div class="book-cover">
                        <span>Book Cover</span>
                    </div>
                    <div class="book-info">
                        <div class="book-title">The Hobbit</div>
                        <div class="book-author">J.R.R. Tolkien</div>
                        <div class="book-price">$11.99</div>
                    </div>
                </div>

                <!-- Book 6 -->
                <div class="book-card">
                    <div class="book-cover">
                        <span>Book Cover</span>
                    </div>
                    <div class="book-info">
                        <div class="book-title">Harry Potter</div>
                        <div class="book-author">J.K. Rowling</div>
                        <div class="book-price">$14.99</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="hero">
            <h2>Ready to Explore More?</h2>
            <p>Browse our complete collection of books and discover your next great read.</p>
            <a href="product.php" style="display: inline-block; background: #e74c3c; color: white; padding: 12px 30px; border-radius: 4px; margin-top: 1rem;">View All Books</a>
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

        <!-- Newsletter Signup -->
        <section style="background: #2c3e50; color: white; padding: 3rem; border-radius: 8px; text-align: center; margin: 3rem 0;">
            <h2 style="margin-bottom: 1rem;">Stay Updated</h2>
            <p style="margin-bottom: 2rem; opacity: 0.9;">Get the latest book recommendations and exclusive deals delivered to your inbox.</p>
            <form style="max-width: 400px; margin: 0 auto; display: flex; gap: 10px;">
                <input type="email" placeholder="Enter your email" style="flex: 1; padding: 12px; border: none; border-radius: 4px;">
                <button type="submit" style="background: #e74c3c; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer;">Subscribe</button>
            </form>
        </section>
    </main>
</body>

</html>
<?php include 'sb_foot.php'; ?>