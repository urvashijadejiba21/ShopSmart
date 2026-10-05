-- ============================================================
-- SHOPSMART DATABASE SCHEMA
-- ============================================================

CREATE DATABASE IF NOT EXISTS shopsmart_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE shopsmart_db;


-- ============================================================
-- DROP EXISTING OBJECTS
-- Allows this script to be run again safely.
-- ============================================================

DROP VIEW IF EXISTS v_order_summary;

DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;


-- ============================================================
-- 1. USERS
-- Stores administrators and customers.
-- Passwords will be stored using PHP password_hash().
-- ============================================================

CREATE TABLE users (
    user_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 2. CATEGORIES
-- Stores product categories.
-- ============================================================

CREATE TABLE categories (
    category_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    description VARCHAR(255) NULL,

    PRIMARY KEY (category_id),
    UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 3. PRODUCTS
-- Main product catalogue.
-- ============================================================

CREATE TABLE products (
    product_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    sku VARCHAR(30) NOT NULL,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock_qty INT UNSIGNED NOT NULL DEFAULT 0,
    image_url VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (product_id),

    UNIQUE KEY uq_products_sku (sku),

    KEY idx_products_category (category_id),
    KEY idx_products_name (name),

    CONSTRAINT fk_products_category
        FOREIGN KEY (category_id)
        REFERENCES categories (category_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_products_price
        CHECK (price >= 0)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 4. ORDERS
-- Stores customer order information.
-- ============================================================

CREATE TABLE orders (
    order_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    order_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    status ENUM(
        'pending',
        'paid',
        'shipped',
        'delivered',
        'cancelled'
    ) NOT NULL DEFAULT 'pending',

    shipping_address VARCHAR(255) NOT NULL,

    PRIMARY KEY (order_id),

    KEY idx_orders_user (user_id),
    KEY idx_orders_status (status),

    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id)
        REFERENCES users (user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 5. ORDER ITEMS
-- Connects orders with products.
-- ============================================================

CREATE TABLE order_items (
    order_item_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,

    PRIMARY KEY (order_item_id),

    UNIQUE KEY uq_order_product (order_id, product_id),

    KEY idx_items_product (product_id),

    CONSTRAINT fk_items_order
        FOREIGN KEY (order_id)
        REFERENCES orders (order_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_items_product
        FOREIGN KEY (product_id)
        REFERENCES products (product_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_items_qty
        CHECK (quantity > 0),

    CONSTRAINT chk_items_price
        CHECK (unit_price >= 0)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 6. AUDIT LOGS
-- Records important actions performed by administrators.
-- Example: changing product stock from 10 to 20.
-- ============================================================

CREATE TABLE audit_logs (
    audit_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT UNSIGNED NOT NULL,
    old_value VARCHAR(255) NULL,
    new_value VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (audit_id),

    KEY idx_audit_user (user_id),
    KEY idx_audit_entity (entity_type, entity_id),

    CONSTRAINT fk_audit_user
        FOREIGN KEY (user_id)
        REFERENCES users (user_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 7. ORDER SUMMARY VIEW
-- Calculates the total value and number of items for each order.
-- ============================================================

CREATE VIEW v_order_summary AS

SELECT
    o.order_id,
    o.order_date,
    o.status,
    u.full_name AS customer_name,
    u.email AS customer_email,
    COUNT(oi.order_item_id) AS line_count,
    COALESCE(
        SUM(oi.quantity * oi.unit_price),
        0
    ) AS order_total

FROM orders o

JOIN users u
    ON u.user_id = o.user_id

LEFT JOIN order_items oi
    ON oi.order_id = o.order_id

GROUP BY
    o.order_id,
    o.order_date,
    o.status,
    u.full_name,
    u.email;