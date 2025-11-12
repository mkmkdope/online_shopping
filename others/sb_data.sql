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

-- Insert sample data into products table
INSERT INTO products (title, author, price, description, publisher, publication_date, pages, cover_image, stock_quantity) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', 10.99, 'A novel set in the Roaring Twenties.', 'Scribner', '1925-04-10', 180, 'great_gatsby.jpg', 50);