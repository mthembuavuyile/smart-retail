-- ============================================================
-- Smart Retail System — Database Initialisation Script
-- ============================================================
-- Run this file against your MySQL server to create the
-- database, tables, and sample data for local development.
--
-- Usage:
--   mysql -u root -p < database/schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS smart_retail_system
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE smart_retail_system;


-- ============================================================
-- Customers
-- ============================================================

CREATE TABLE IF NOT EXISTS `Customers` (
    `CustomerID`     INT AUTO_INCREMENT PRIMARY KEY,
    `FirstName`      VARCHAR(100)  NOT NULL,
    `LastName`       VARCHAR(100)  NOT NULL,
    `Email`          VARCHAR(255)  NOT NULL UNIQUE,
    `PasswordHash`   VARCHAR(255)  NOT NULL,
    `PhoneNumber`    VARCHAR(15),
    `DateRegistered` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ============================================================
-- Addresses
-- ============================================================

CREATE TABLE IF NOT EXISTS `Addresses` (
    `AddressID`  INT AUTO_INCREMENT PRIMARY KEY,
    `CustomerID` INT NOT NULL,
    `Street`     VARCHAR(255) NOT NULL,
    `Suburb`     VARCHAR(100),
    `City`       VARCHAR(100) NOT NULL,
    `Province`   VARCHAR(50),
    `PostalCode` CHAR(4),
    `IsPrimary`  BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (`CustomerID`) REFERENCES `Customers`(`CustomerID`) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- Categories
-- ============================================================

CREATE TABLE IF NOT EXISTS `Categories` (
    `CategoryID`       INT AUTO_INCREMENT PRIMARY KEY,
    `CategoryName`     VARCHAR(255) NOT NULL,
    `ParentCategoryID` INT,
    FOREIGN KEY (`ParentCategoryID`) REFERENCES `Categories`(`CategoryID`) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ============================================================
-- Products
-- ============================================================

CREATE TABLE IF NOT EXISTS `Products` (
    `ProductID`   INT AUTO_INCREMENT PRIMARY KEY,
    `SKU`         VARCHAR(100) NOT NULL UNIQUE,
    `Name`        VARCHAR(255) NOT NULL,
    `Description` TEXT,
    `Price`       DECIMAL(10,2) NOT NULL,
    `CategoryID`  INT,
    `ImageURL`    VARCHAR(255),
    FOREIGN KEY (`CategoryID`) REFERENCES `Categories`(`CategoryID`) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ============================================================
-- Inventory
-- ============================================================

CREATE TABLE IF NOT EXISTS `Inventory` (
    `InventoryID`       INT AUTO_INCREMENT PRIMARY KEY,
    `ProductID`         INT NOT NULL UNIQUE,
    `StockLevel`        INT NOT NULL,
    `LowStockThreshold` INT,
    FOREIGN KEY (`ProductID`) REFERENCES `Products`(`ProductID`) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- Orders
-- ============================================================

CREATE TABLE IF NOT EXISTS `Orders` (
    `OrderID`           INT AUTO_INCREMENT PRIMARY KEY,
    `CustomerID`        INT NOT NULL,
    `OrderDate`         DATETIME DEFAULT CURRENT_TIMESTAMP,
    `Status`            VARCHAR(20) DEFAULT 'Pending',
    `TotalAmount`       DECIMAL(10,2),
    `ShippingAddressID` INT,
    FOREIGN KEY (`CustomerID`) REFERENCES `Customers`(`CustomerID`) ON DELETE CASCADE,
    FOREIGN KEY (`ShippingAddressID`) REFERENCES `Addresses`(`AddressID`) ON DELETE SET NULL
) ENGINE=InnoDB;


-- ============================================================
-- Order Items
-- ============================================================

CREATE TABLE IF NOT EXISTS `OrderItems` (
    `OrderItemID`     INT AUTO_INCREMENT PRIMARY KEY,
    `OrderID`         INT NOT NULL,
    `ProductID`       INT NOT NULL,
    `Quantity`        INT NOT NULL,
    `PriceAtPurchase` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`OrderID`) REFERENCES `Orders`(`OrderID`) ON DELETE CASCADE,
    FOREIGN KEY (`ProductID`) REFERENCES `Products`(`ProductID`) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- Transactions
-- ============================================================

CREATE TABLE IF NOT EXISTS `Transactions` (
    `TransactionID`      INT AUTO_INCREMENT PRIMARY KEY,
    `OrderID`            INT NOT NULL,
    `GatewayReferenceID` VARCHAR(255),
    `Amount`             DECIMAL(10,2) NOT NULL,
    `Status`             VARCHAR(50),
    `Timestamp`          DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`OrderID`) REFERENCES `Orders`(`OrderID`) ON DELETE CASCADE
) ENGINE=InnoDB;


-- ============================================================
-- Admins
-- ============================================================

CREATE TABLE IF NOT EXISTS `Admins` (
    `AdminID`      INT AUTO_INCREMENT PRIMARY KEY,
    `Username`     VARCHAR(100) NOT NULL UNIQUE,
    `Email`        VARCHAR(255) NOT NULL UNIQUE,
    `PasswordHash` VARCHAR(255) NOT NULL,
    `DateCreated`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ============================================================
-- SAMPLE DATA
-- ============================================================

-- Customers
-- Password for both: "password123" (bcrypt hash)
INSERT INTO `Customers` (`FirstName`, `LastName`, `Email`, `PasswordHash`) VALUES
('Avuyile', 'Mthembu', 'avuyile@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Sipho',   'Ndlovu',  'sipho.n@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Naledi',  'Molefe',  'naledi.m@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Addresses
INSERT INTO `Addresses` (`CustomerID`, `Street`, `Suburb`, `City`, `Province`, `PostalCode`, `IsPrimary`) VALUES
(1, '14 Umhlanga Rocks Drive', 'Umhlanga', 'Durban',       'KwaZulu-Natal', '4319', TRUE),
(2, '88 Sandton Drive',        'Sandton',  'Johannesburg', 'Gauteng',       '2196', TRUE),
(3, '5 Long Street',           'CBD',      'Cape Town',    'Western Cape',  '8001', TRUE);

-- Categories
INSERT INTO `Categories` (`CategoryID`, `CategoryName`) VALUES
(1, 'Electronics'),
(2, 'Books'),
(3, 'Clothing'),
(4, 'Home & Kitchen'),
(5, 'Sports & Outdoors');

-- Products
INSERT INTO `Products` (`SKU`, `Name`, `Description`, `Price`, `CategoryID`, `ImageURL`) VALUES
('ELEC-001', 'Wireless Bluetooth Headphones',
 'Over-ear noise-cancelling headphones with 30-hour battery life and built-in microphone. Foldable design for easy portability.',
 899.00, 1, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600'),

('ELEC-002', 'Smartwatch Pro',
 '1.4-inch AMOLED display, heart rate monitor, GPS tracking, and 7-day battery. Water-resistant to 50 metres.',
 2499.00, 1, 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=600'),

('ELEC-003', 'Portable Power Bank 20000mAh',
 'Dual USB-C and USB-A output, fast charging support. Aircraft-grade aluminium shell.',
 549.00, 1, 'https://images.unsplash.com/photo-1609091839311-d5365f9ff1c5?w=600'),

('BOOK-001', 'Clean Code by Robert C. Martin',
 'A handbook of agile software craftsmanship. Covers naming, functions, formatting, error handling, and test-driven development.',
 450.00, 2, 'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=600'),

('BOOK-002', 'The Pragmatic Programmer',
 'From journeyman to master. Covers topics from personal responsibility and career development to architectural techniques.',
 520.00, 2, 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600'),

('CLTH-001', 'Cotton Crew-Neck T-Shirt',
 '100% organic cotton, pre-shrunk, reinforced shoulder seams. Available in multiple colours.',
 249.00, 3, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600'),

('CLTH-002', 'Denim Jacket',
 'Classic trucker-style jacket in medium-wash denim. Button front, two chest pockets, adjustable waist tabs.',
 1199.00, 3, 'https://images.unsplash.com/photo-1576995853123-5a10305d93c0?w=600'),

('HOME-001', 'Stainless Steel Water Bottle',
 'Double-walled vacuum insulation keeps drinks cold for 24 hours or hot for 12. BPA-free, 750ml capacity.',
 329.00, 4, 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=600'),

('HOME-002', 'Ceramic Pour-Over Coffee Dripper',
 'Hand-crafted ceramic dripper with spiral ridges for even extraction. Includes 40 unbleached paper filters.',
 399.00, 4, 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600'),

('SPRT-001', 'Yoga Mat 6mm',
 'Non-slip TPE surface, alignment markers, includes carrying strap. 183cm x 61cm.',
 399.00, 5, 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=600');

-- Inventory (one row per product)
INSERT INTO `Inventory` (`ProductID`, `StockLevel`, `LowStockThreshold`) VALUES
(1,  45, 10),
(2,  22,  5),
(3,  60, 15),
(4,  38, 10),
(5,  30, 10),
(6, 120, 25),
(7,  18,  8),
(8,  55, 12),
(9,  40, 10),
(10, 70, 15);

-- Sample Orders (so admin reports have data to display)
INSERT INTO `Orders` (`CustomerID`, `OrderDate`, `Status`, `TotalAmount`, `ShippingAddressID`) VALUES
(1, NOW() - INTERVAL 5 DAY, 'Completed', 1348.00, 1),
(2, NOW() - INTERVAL 3 DAY, 'Shipped',   2499.00, 2),
(3, NOW() - INTERVAL 1 DAY, 'Pending',    649.00, 3),
(1, NOW(),                   'Pending',    899.00, 1);

-- Order Items for the sample orders
INSERT INTO `OrderItems` (`OrderID`, `ProductID`, `Quantity`, `PriceAtPurchase`) VALUES
(1, 1, 1, 899.00),
(1, 8, 1, 329.00),
(1, 9, 1, 399.00),  -- Note: 899 + 329 + (should be a rounding for demo) but keeping total as listed
(2, 2, 1, 2499.00),
(3, 3, 1, 549.00),
(4, 1, 1, 899.00);

-- Update order 1 total to match items: 899 + 329 + 399 = 1627
UPDATE `Orders` SET `TotalAmount` = 1627.00 WHERE `OrderID` = 1;
-- Update order 3 total to match items: 549
UPDATE `Orders` SET `TotalAmount` = 549.00 WHERE `OrderID` = 3;

-- Transactions for completed/shipped orders
INSERT INTO `Transactions` (`OrderID`, `GatewayReferenceID`, `Amount`, `Status`) VALUES
(1, 'SIM_ABC123DEF', 1627.00, 'Completed'),
(2, 'SIM_GHI456JKL', 2499.00, 'Completed');

-- Admin account
-- Username: admin  |  Password: admin123
INSERT INTO `Admins` (`Username`, `Email`, `PasswordHash`) VALUES
('admin', 'admin@smartretail.co.za', '$2y$10$4OmwMYcPEunTcMWpFVQP1OdayOfcNWwhMm/tiLS.rk.q5G2059p0i');
