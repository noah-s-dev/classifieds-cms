-- Classifieds CMS Database Setup
-- This file creates the database schema for the classifieds content management system

-- Create database
CREATE DATABASE IF NOT EXISTS classifieds_cms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE classifieds_cms;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_email (email)
);

-- Create categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_name (name)
);

-- Create ads table
CREATE TABLE ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2),
    location VARCHAR(100),
    contact_email VARCHAR(100),
    contact_phone VARCHAR(20),
    image_filename VARCHAR(255),
    status ENUM('active', 'inactive', 'sold', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    views_count INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_title (title),
    FULLTEXT INDEX idx_search (title, description)
);

-- Insert default categories
INSERT INTO categories (name, description) VALUES
('Electronics', 'Computers, phones, gadgets and electronic devices'),
('Vehicles', 'Cars, motorcycles, boats and other vehicles'),
('Real Estate', 'Houses, apartments, land and property rentals'),
('Jobs', 'Employment opportunities and job listings'),
('Services', 'Professional services and business offerings'),
('For Sale', 'General items for sale'),
('Furniture', 'Home and office furniture'),
('Clothing', 'Clothing, shoes and accessories'),
('Sports & Recreation', 'Sports equipment and recreational items'),
('Pets', 'Pet sales, adoption and pet-related services');

-- Create admin user (password: admin123 - should be changed in production)
INSERT INTO users (username, email, password_hash, first_name, last_name) VALUES
('admin', 'admin@classifieds', '$2y$10$asHHRTjGnpPlB75Mc9W5pekc0B4a1u25KocPhBNnVgCH2B51./eYe', 'Admin', 'User');

-- Create sample ads for testing
INSERT INTO ads (user_id, category_id, title, description, price, location, contact_email, contact_phone) VALUES
(1, 1, 'iPhone 13 Pro - Excellent Condition', 'Selling my iPhone 13 Pro in excellent condition. No scratches, comes with original box and charger. Reason for selling: upgraded to newer model.', 699.99, 'Downtown', 'admin@classifieds.local', '555-0123'),
(1, 2, '2018 Honda Civic - Low Mileage', 'Well-maintained 2018 Honda Civic with only 45,000 miles. Regular oil changes, non-smoker, clean title. Great fuel economy and reliability.', 18500.00, 'Suburbs', 'admin@classifieds.local', '555-0123'),
(1, 6, 'Vintage Leather Sofa', 'Beautiful vintage brown leather sofa in great condition. Very comfortable and stylish. Moving sale - must go this week!', 450.00, 'City Center', 'admin@classifieds.local', '555-0123');

