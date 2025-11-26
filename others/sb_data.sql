-- Create products table for book shop
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    publisher VARCHAR(255),
    publication_date DATE,
    pages INT,
    cover_image VARCHAR(255),
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert sample data into products table
INSERT INTO products (title, author, price, description, publisher, publication_date, pages, cover_image, stock_quantity) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', 10.99, 'A novel set in the Roaring Twenties.', 'Scribner', '1925-04-10', 180, 'great_gatsby.jpg', 50);



-- cart
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- INSERT INTO cart_items (user_id, product_id, quantity) VALUES
-- (1, 1, 2); 


-- -- orders
-- CREATE TABLE orders (
--     order_id INT AUTO_INCREMENT PRIMARY KEY,
--     user_id INT NOT NULL,
--     total DECIMAL(10,2) NOT NULL,
--     status VARCHAR(20) NOT NULL DEFAULT 'PAID',
--     created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (user_id) REFERENCES users(user_id)
-- );

--payment
CREATE TABLE payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    amount DECIMAL(10,2) NOT NULL,
    method VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'SUCCESS',
    reference_no VARCHAR(100),
    transaction_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    receipt_pdf VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);