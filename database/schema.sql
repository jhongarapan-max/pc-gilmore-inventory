-- PC Gilmore Inventory System Database Schema
-- Version 1.0.0

-- Create database
CREATE DATABASE IF NOT EXISTS pc_gilmore_inventory;
USE pc_gilmore_inventory;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin', 'cashier', 'manager') DEFAULT 'cashier',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
    supplier_id INT PRIMARY KEY AUTO_INCREMENT,
    supplier_name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    contact_number VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Items table
CREATE TABLE IF NOT EXISTS items (
    item_id INT PRIMARY KEY AUTO_INCREMENT,
    barcode VARCHAR(50) UNIQUE NOT NULL,
    item_name VARCHAR(200) NOT NULL,
    description TEXT,
    category_id INT,
    supplier_id INT,
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    selling_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stock_quantity INT NOT NULL DEFAULT 0,
    min_stock_level INT DEFAULT 0,
    max_stock_level INT DEFAULT 0,
    unit VARCHAR(20) DEFAULT 'pcs',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
);

-- Sales table
CREATE TABLE IF NOT EXISTS sales (
    sale_id INT PRIMARY KEY AUTO_INCREMENT,
    sale_number VARCHAR(50) UNIQUE NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    customer_name VARCHAR(100),
    customer_contact VARCHAR(20),
    customer_address TEXT,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'gcash', 'bank_transfer') DEFAULT 'cash',
    payment_received DECIMAL(10,2) DEFAULT 0.00,
    change_amount DECIMAL(10,2) DEFAULT 0.00,
    cashier_id INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cashier_id) REFERENCES users(user_id)
);

-- Sale items table
CREATE TABLE IF NOT EXISTS sale_items (
    sale_item_id INT PRIMARY KEY AUTO_INCREMENT,
    sale_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(sale_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(item_id)
);

-- Stock movements table
CREATE TABLE IF NOT EXISTS stock_movements (
    movement_id INT PRIMARY KEY AUTO_INCREMENT,
    item_id INT NOT NULL,
    movement_type ENUM('in', 'out', 'sale', 'adjustment', 'return') NOT NULL,
    quantity INT NOT NULL,
    unit_cost DECIMAL(10,2) DEFAULT 0.00,
    reference_no VARCHAR(50),
    notes TEXT,
    user_id INT NOT NULL,
    movement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(item_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Audit log table
CREATE TABLE IF NOT EXISTS audit_log (
    audit_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action_type VARCHAR(50) NOT NULL,
    action_module VARCHAR(50) NOT NULL,
    action_description TEXT,
    reference_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Indexes for better performance
CREATE INDEX idx_items_barcode ON items(barcode);
CREATE INDEX idx_items_category ON items(category_id);
CREATE INDEX idx_items_supplier ON items(supplier_id);
CREATE INDEX idx_sales_date ON sales(sale_date);
CREATE INDEX idx_sales_cashier ON sales(cashier_id);
CREATE INDEX idx_sale_items_sale ON sale_items(sale_id);
CREATE INDEX idx_sale_items_item ON sale_items(item_id);
CREATE INDEX idx_stock_movements_item ON stock_movements(item_id);
CREATE INDEX idx_stock_movements_date ON stock_movements(movement_date);
CREATE INDEX idx_audit_log_user ON audit_log(user_id);
CREATE INDEX idx_audit_log_date ON audit_log(created_at);

-- Insert default admin user
INSERT IGNORE INTO users (username, password_hash, full_name, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin');

-- Insert sample categories
INSERT IGNORE INTO categories (category_name, description) VALUES
('Processors', 'CPU processors and chips'),
('Laptops', 'Laptop computers'),
('Mice', 'Computer mice and pointing devices'),
('Memory', 'RAM and memory modules'),
('Storage', 'Hard drives and SSDs'),
('Monitors', 'Display monitors'),
('Keyboards', 'Computer keyboards'),
('Accessories', 'Computer accessories');

-- Insert sample suppliers
INSERT IGNORE INTO suppliers (supplier_name, contact_person, contact_number, email, address) VALUES
('TechSource Inc.', 'Maria Santos', '+63-917-123-4567', 'maria@techsource.ph', '123 Technology Ave, Makati City'),
('Digital Solutions Ltd.', 'Juan dela Cruz', '+63-918-234-5678', 'juan@digitalsolutions.ph', '456 Innovation St, BGC, Taguig'),
('PC Components Hub', 'Ana Reyes', '+63-919-345-6789', 'ana@pccomponents.ph', '789 Hardware Rd, Quezon City');

-- Insert sample items
INSERT IGNORE INTO items (barcode, item_name, description, category_id, supplier_id, cost_price, selling_price, stock_quantity, min_stock_level, unit) VALUES
('8886419353430', 'Intel Core i5-12400F Processor', '12th Gen Intel Core i5 processor', 1, 1, 8500.00, 9500.00, 10, 2, 'pcs'),
('4711081334347', 'ASUS TUF Gaming Laptop', 'ASUS TUF Gaming F15 laptop', 2, 2, 45000.00, 52000.00, 5, 1, 'pcs'),
('0840006642619', 'Logitech MX Master 3 Mouse', 'Wireless ergonomic mouse', 3, 3, 2500.00, 3200.00, 15, 3, 'pcs'),
('8436589771404', 'Kingston Fury 16GB DDR4 RAM', '16GB DDR4-3200 RAM kit', 4, 1, 2800.00, 3500.00, 20, 5, 'pcs'),
('0740617261028', 'Samsung 970 EVO Plus 500GB', 'NVMe SSD 500GB', 5, 2, 3200.00, 4200.00, 8, 2, 'pcs');
