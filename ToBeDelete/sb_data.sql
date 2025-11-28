-- product module SQL script
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NULL,
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description, sort_order) VALUES
('Fiction', 'Novels and fictional works', 1),
('Non-Fiction', 'Factual literature and real-world topics', 2),
('Science Fiction', 'Speculative fiction with scientific elements', 3),
('Fantasy', 'Magical and supernatural elements', 4),
('Mystery', 'Crime and detective stories', 5),
('Romance', 'Love stories and romantic relationships', 6),
('Biography', 'Accounts of peoples lives', 7),
('History', 'Historical events and periods', 8),
('Classic Literature', 'Enduring works of literary merit', 9),
('Contemporary Fiction', 'Modern works of fiction', 10),
('Thriller', 'Suspenseful and exciting stories', 11),
('Young Adult', 'Books for teenage readers', 12),
('Children', 'Books for young readers', 13),
('Self-Help', 'Personal development and improvement', 14),
('Business', 'Business and economics literature', 15);

-- Insert sample data into products table
INSERT INTO products (category_id, title, author, price, description, publisher, publication_date, pages, cover_image, stock_quantity) VALUES
(1, 'The Great Gatsby', 'F. Scott Fitzgerald', 10.99, 'A novel set in the Jazz Age that tells the story of Jay Gatsby and his unrequited love for Daisy Buchanan.', 'Scribner', '1925-04-10', 180, 'great_gatsby.jpg', 50),
(1, '1984', 'George Orwell', 8.99, 'A dystopian novel that explores the dangers of totalitarianism and extreme political ideology.', 'Secker & Warburg', '1949-06-08', 328, '1984.jpg', 75),
(1, 'To Kill a Mockingbird', 'Harper Lee', 12.99, 'A novel about racial injustice in the Deep South, seen through the eyes of young Scout Finch.', 'J.B. Lippincott & Co.', '1960-07-11', 281, 'to_kill_a_mockingbird.jpg', 100),
(6, 'Pride and Prejudice', 'Jane Austen', 9.99, 'A classic romance novel that explores themes of love, class, and social expectations.', 'T. Egerton', '1813-01-28', 279, 'pride_and_prejudice.jpg', 60),
(4, 'The Hobbit', 'J.R.R. Tolkien', 11.99, 'A fantasy novel that follows the adventures of Bilbo Baggins as he embarks on a quest to reclaim a lost dwarf kingdom.', 'George Allen & Unwin', '1937-09-21', 310, 'the_hobbit.jpg', 80);

-- product module SQL script ends here

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