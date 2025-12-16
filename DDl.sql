CREATE DATABASE IF NOT EXISTS campustrade1;
USE campustrade1;

-- Users table
CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(20),
  password VARCHAR(255) NOT NULL
);

-- Category table
CREATE TABLE IF NOT EXISTS category (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  category_name VARCHAR(100) NOT NULL UNIQUE
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
  product_id INT AUTO_INCREMENT PRIMARY KEY,
  product_name VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2),
  listing_type ENUM('sell', 'rent', 'lend') NOT NULL,
  status ENUM('available', 'sold', 'borrowed', 'rented', 'unavailable') DEFAULT 'available',
  posted_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  seller_id INT NOT NULL,
  category_id INT,
  is_deleted BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES category(category_id)
);

-- Product images table
CREATE TABLE IF NOT EXISTS product_images (
  image_id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  url VARCHAR(255),
  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Rent/Lend details table (with manual start and end dates)
CREATE TABLE IF NOT EXISTS rent_lend_details (
  product_id INT PRIMARY KEY,
  rent_price DECIMAL(10,2),
  start_date DATE,
  end_date DATE,
  terms TEXT,
  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- Interested users table (supports both product and category interest)
CREATE TABLE IF NOT EXISTS interested (
  interested_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NULL,
  category_id INT NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES category(category_id) ON DELETE CASCADE,
  UNIQUE (user_id, product_id),
  UNIQUE (user_id, category_id)
);

-- Notification table
CREATE TABLE IF NOT EXISTS notification (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  message VARCHAR(255) NOT NULL,
  reference_id INT NOT NULL,
  type ENUM('product', 'category') NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  is_read BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


-- Insert default categories
INSERT IGNORE INTO category (category_name) VALUES 
('Books'),
('Electronics'),
('Furniture'),
('Clothing'),
('Sports Equipment'),
('Stationery'),
('Other');


