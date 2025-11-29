CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    publisher VARCHAR(255),
    publication_date DATE,
    pages INT,
    cover_image VARCHAR(255),
    images JSON,
    stock_quantity INT DEFAULT 0,
    low_stock_threshold INT DEFAULT 10,
    stock_status ENUM('in_stock', 'low_stock', 'out_of_stock') DEFAULT 'in_stock',
    needs_reorder ENUM('yes', 'no') DEFAULT 'no',
    last_restocked DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);


-- Insert sample categories
INSERT INTO categories (name, description, sort_order) VALUES
('Fiction', 'Novels and fictional works', 1),
('Non-Fiction', 'Factual literature and real-world topics', 2),
('Science Fiction', 'Speculative fiction with scientific elements', 3),
('Fantasy', 'Magical and supernatural elements', 4),
('Mystery', 'Crime and detective stories', 5);

-- Insert sample products
INSERT INTO products (category_id, title, author, price, description, publisher, publication_date, pages, cover_image, images, stock_quantity, low_stock_threshold) VALUES
(1, 'The Great Gatsby', 'F. Scott Fitzgerald', 10.99, 'A novel set in the Jazz Age that tells the story of Jay Gatsby and his unrequited love for Daisy Buchanan.', 'Scribner', '1925-04-10', 180, '4_692b2f7a24ec9_cat.png', '["4_692b2f7a24ec9_cat.png"]', 50, 5),
(2, 'Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', 14.99, 'An exploration of the history of humankind from the Stone Age to the 21st century.', 'Harper', '2011-01-01', 443, '4_692b2f7a24ec9_cat.png', '["4_692b2f7a24ec9_cat.png"]', 30, 3),
(3, 'Dune', 'Frank Herbert', 9.99, 'A science fiction novel about politics, religion, and ecology on the desert planet Arrakis.', 'Chilton Books', '1965-08-01', 412, '4_692b2f7a24ec9_cat.png', '["4_692b2f7a24ec9_cat.png"]', 20, 2),
(4, 'The Hobbit', 'J.R.R. Tolkien', 12.99, 'A fantasy novel that follows the journey of Bilbo Baggins as he embarks on an adventure to reclaim a lost dwarf kingdom.', 'George Allen & Unwin', '1937-09-21', 310, '4_692b2f7a24ec9_cat.png', '["4_692b2f7a24ec9_cat.png"]', 40, 4),
(5, 'The Hound of the Baskervilles', 'Arthur Conan Doyle', 8.99, 'A mystery novel featuring Sherlock Holmes investigating the legend of a supernatural hound on the moors of Devonshire.', 'George Newnes', '1902-04-01', 256, '4_692b2f7a24ec9_cat.png', '["4_692b2f7a24ec9_cat.png"]', 25, 2);

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

-- Users table for all user types: user, member, and admin
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_role ENUM('user', 'member', 'admin') DEFAULT 'user' NOT NULL,
    profile_photo VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sample admin user
INSERT INTO users (username, email, password_hash, user_role) VALUES
('admin', 'admin@example.com', 'admin123', 'admin');

-- Sample users and members
INSERT INTO users (username, email, password_hash, user_role) VALUES
('user1', 'user1@example.com', 'user123', 'user'),
('member1', 'member1@example.com', 'member123', 'member');

-- Addresses table for users and members (admins don't need addresses)
CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    street VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    address_state VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);