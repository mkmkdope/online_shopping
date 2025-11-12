<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Book Nook - Product Details</title>
    <style>
        /* Reset and Base Styles */
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
          font-family: "Georgia", serif;
        }

        body {
          background-color: #f9f7f2;
          color: #333;
          line-height: 1.6;
        }

        a {
          text-decoration: none;
          color: inherit;
        }

        /* Header Styles */
        header {
          background-color: #2c3e50;
          color: #fff;
          padding: 1rem 2rem;
          display: flex;
          justify-content: space-between;
          align-items: center;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .logo {
          font-size: 1.8rem;
          font-weight: bold;
          letter-spacing: 1px;
        }

        nav ul {
          display: flex;
          list-style: none;
        }

        nav li {
          margin-left: 1.5rem;
        }

        nav a:hover {
          color: #e74c3c;
        }

        /* Main Content */
        .container {
          max-width: 1200px;
          margin: 0 auto;
          padding: 2rem;
        }

        .breadcrumb {
          margin-bottom: 1.5rem;
          font-size: 0.9rem;
          color: #7f8c8d;
        }

        .breadcrumb a:hover {
          color: #e74c3c;
        }

        .product-details {
          display: grid;
          grid-template-columns: 1fr 2fr;
          gap: 3rem;
          margin-bottom: 3rem;
        }

        @media (max-width: 768px) {
          .product-details {
            grid-template-columns: 1fr;
          }
        }

        .product-image {
          background: white;
          border-radius: 4px;
          overflow: hidden;
          box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 2rem;
          height: 400px;
        }

        .product-image-placeholder {
          color: #7f8c8d;
          text-align: center;
        }

        .product-info {
          padding: 1rem 0;
        }

        .product-title {
          font-size: 2rem;
          margin-bottom: 0.5rem;
          color: #2c3e50;
        }

        .product-author {
          font-size: 1.2rem;
          color: #7f8c8d;
          margin-bottom: 1.5rem;
        }

        .product-price {
          font-size: 1.8rem;
          color: #e74c3c;
          font-weight: bold;
          margin-bottom: 1.5rem;
        }

        .product-meta {
          display: grid;
          grid-template-columns: repeat(2, 1fr);
          gap: 1rem;
          margin-bottom: 2rem;
        }

        .meta-item {
          display: flex;
          flex-direction: column;
        }

        .meta-label {
          font-size: 0.9rem;
          color: #7f8c8d;
          margin-bottom: 0.3rem;
        }

        .meta-value {
          font-weight: bold;
        }

        .product-description {
          margin-bottom: 2rem;
          line-height: 1.8;
        }

        .add-to-cart {
          display: flex;
          align-items: center;
          gap: 1rem;
          margin-bottom: 2rem;
        }

        .quantity-selector {
          display: flex;
          align-items: center;
          border: 1px solid #ddd;
          border-radius: 4px;
          overflow: hidden;
        }

        .quantity-btn {
          background: #f5f5f5;
          border: none;
          padding: 0.7rem 1rem;
          cursor: pointer;
          font-size: 1.2rem;
        }

        .quantity-input {
          width: 50px;
          text-align: center;
          border: none;
          padding: 0.7rem 0;
          font-size: 1rem;
        }

        .btn {
          padding: 0.8rem 1.5rem;
          border: none;
          border-radius: 4px;
          cursor: pointer;
          font-size: 1rem;
          transition: background-color 0.3s;
        }

        .btn-primary {
          background: #e74c3c;
          color: white;
        }

        .btn-primary:hover {
          background: #c0392b;
        }

        .btn-secondary {
          background: #ecf0f1;
          color: #2c3e50;
        }

        .btn-secondary:hover {
          background: #bdc3c7;
        }

        .product-actions {
          display: flex;
          gap: 1rem;
        }

        /* Related Books */
        .related-books {
          margin: 3rem 0;
        }

        .related-books h2 {
          text-align: center;
          margin-bottom: 2rem;
          color: #2c3e50;
        }

        .book-grid {
          display: grid;
          grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
          gap: 2rem;
        }

        .book-card {
          background: white;
          border-radius: 4px;
          overflow: hidden;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
          transition: transform 0.3s;
        }

        .book-card:hover {
          transform: translateY(-5px);
        }

        .book-cover {
          height: 250px;
          background: #ecf0f1;
          display: flex;
          align-items: center;
          justify-content: center;
          color: #7f8c8d;
        }

        .book-info {
          padding: 1rem;
        }

        .book-title {
          font-weight: bold;
          margin-bottom: 0.5rem;
        }

        .book-author {
          color: #7f8c8d;
          font-size: 0.9rem;
          margin-bottom: 0.5rem;
        }

        .book-price {
          color: #e74c3c;
          font-weight: bold;
        }

        /* Footer */
        footer {
          background: #2c3e50;
          color: white;
          padding: 3rem 2rem;
          margin-top: 3rem;
        }

        .footer-content {
          max-width: 1200px;
          margin: 0 auto;
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 2rem;
        }

        .footer-section h3 {
          margin-bottom: 1rem;
        }

        .footer-section ul {
          list-style: none;
        }

        .footer-section li {
          margin-bottom: 0.5rem;
        }

        .footer-section a:hover {
          color: #e74c3c;
        }

        .copyright {
          text-align: center;
          margin-top: 2rem;
          padding-top: 2rem;
          border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
          header {
            flex-direction: column;
            padding: 1rem;
          }

          nav ul {
            margin-top: 1rem;
          }

          .product-meta {
            grid-template-columns: 1fr;
          }

          .product-actions {
            flex-direction: column;
          }

          .book-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
          }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo">The Book Nook</div>
        <nav>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">Books</a></li>
                <li><a href="#">Categories</a></li>
                <li><a href="#">Best Sellers</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="#">Home</a> > <a href="#">Books</a> > <a href="#">Fiction</a> > The Great Adventure
        </div>

        <!-- Product Details -->
        <div class="product-details">
            <div class="product-image">
                <div class="product-image-placeholder">
                    <h3>Book Cover</h3>
                    <p>The Great Adventure</p>
                </div>
            </div>
            <div class="product-info">
                <h1 class="product-title">The Great Adventure</h1>
                <p class="product-author">by John Smith</p>
                <div class="product-price">$24.99</div>
                
                <div class="product-meta">
                    <div class="meta-item">
                        <span class="meta-label">ISBN</span>
                        <span class="meta-value">978-1234567890</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Publisher</span>
                        <span class="meta-value">Adventure Press</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Publication Date</span>
                        <span class="meta-value">May 15, 2023</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Pages</span>
                        <span class="meta-value">320</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Language</span>
                        <span class="meta-value">English</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Category</span>
                        <span class="meta-value">Adventure</span>
                    </div>
                </div>
                
                <div class="product-description">
                    <p>An exciting journey through unknown lands filled with mystery and discovery. Join our protagonist as they navigate treacherous terrain, encounter fascinating characters, and uncover secrets that will change their world forever.</p>
                    <p>This epic tale of courage and determination has been praised by critics as "a masterpiece of modern adventure literature" and "a page-turner that will keep you up all night."</p>
                </div>
                
                <div class="add-to-cart">
                    <div class="quantity-selector">
                        <button class="quantity-btn">-</button>
                        <input type="text" class="quantity-input" value="1">
                        <button class="quantity-btn">+</button>
                    </div>
                    <button class="btn btn-primary">Add to Cart</button>
                </div>
                
                <div class="product-actions">
                    <button class="btn btn-secondary">Add to Wishlist</button>
                    <button class="btn btn-secondary">Share</button>
                </div>
            </div>
        </div>

        <!-- Related Books -->
        <div class="related-books">
            <h2>You Might Also Like</h2>
            <div class="book-grid">
                <div class="book-card">
                    <div class="book-cover">
                        <div>Book Cover</div>
                    </div>
                    <div class="book-info">
                        <div class="book-title">Mystery of the Lost City</div>
                        <div class="book-author">by Sarah Johnson</div>
                        <div class="book-price">$19.99</div>
                    </div>
                </div>
                <div class="book-card">
                    <div class="book-cover">
                        <div>Book Cover</div>
                    </div>
                    <div class="book-info">
                        <div class="book-title">Journey to the Center</div>
                        <div class="book-author">by Michael Brown</div>
                        <div class="book-price">$22.50</div>
                    </div>
                </div>
                <div class="book-card">
                    <div class="book-cover">
                        <div>Book Cover</div>
                    </div>
                    <div class="book-info">
                        <div class="book-title">The Last Explorer</div>
                        <div class="book-author">by Emily Wilson</div>
                        <div class="book-price">$21.99</div>
                    </div>
                </div>
                <div class="book-card">
                    <div class="book-cover">
                        <div>Book Cover</div>
                    </div>
                    <div class="book-info">
                        <div class="book-title">Beyond the Horizon</div>
                        <div class="book-author">by Robert Davis</div>
                        <div class="book-price">$23.75</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>About Us</h3>
                <ul>
                    <li><a href="#">Our Story</a></li>
                    <li><a href="#">Careers</a></li>
                    <li><a href="#">Press</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Customer Service</h3>
                <ul>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">Shipping Info</a></li>
                    <li><a href="#">Returns</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Connect</h3>
                <ul>
                    <li><a href="#">Newsletter</a></li>
                    <li><a href="#">Facebook</a></li>
                    <li><a href="#">Instagram</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            &copy; 2023 The Book Nook. All rights reserved.
        </div>
    </footer>

    <script>
        // Quantity selector functionality
        document.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('.quantity-input');
                let value = parseInt(input.value);
                
                if (this.textContent === '+') {
                    value++;
                } else if (this.textContent === '-' && value > 1) {
                    value--;
                }
                
                input.value = value;
            });
        });
        
        // Add to cart functionality
        document.querySelector('.btn-primary').addEventListener('click', function() {
            const quantity = document.querySelector('.quantity-input').value;
            alert(`Added ${quantity} copy/copies of "The Great Adventure" to your cart!`);
        });
    </script>
</body>
</html>