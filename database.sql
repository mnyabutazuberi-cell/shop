-- ShopTrack database (run in phpMyAdmin > SQL tab)
CREATE DATABASE IF NOT EXISTS shoptrack CHARACTER SET utf8mb4;
USE shoptrack;

CREATE TABLE categories (
  category_id INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(50) NOT NULL UNIQUE,
  description VARCHAR(150)
);

CREATE TABLE suppliers (
  supplier_id INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(80) NOT NULL,
  phone       VARCHAR(20) NOT NULL,
  email       VARCHAR(80)
);

CREATE TABLE staff (
  staff_id      INT AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(80) NOT NULL,
  username      VARCHAR(30) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('admin','cashier') NOT NULL DEFAULT 'cashier'
);

CREATE TABLE products (
  product_id    INT AUTO_INCREMENT PRIMARY KEY,
  category_id   INT NOT NULL,
  supplier_id   INT NOT NULL,
  sku           VARCHAR(20) NOT NULL UNIQUE,
  name          VARCHAR(80) NOT NULL,
  cost_price    DECIMAL(10,2) NOT NULL,
  unit_price    DECIMAL(10,2) NOT NULL,
  stock_qty     INT NOT NULL DEFAULT 0,
  reorder_level INT NOT NULL DEFAULT 5,
  FOREIGN KEY (category_id) REFERENCES categories(category_id),
  FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
  CHECK (unit_price >= cost_price),
  CHECK (stock_qty >= 0)
);

CREATE TABLE sales (
  sale_id        INT AUTO_INCREMENT PRIMARY KEY,
  staff_id       INT NOT NULL,
  sale_date      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  payment_method ENUM('cash','mobile','card') NOT NULL DEFAULT 'cash',
  total_amount   DECIMAL(10,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (staff_id) REFERENCES staff(staff_id)
);

CREATE TABLE sale_items (
  sale_item_id INT AUTO_INCREMENT PRIMARY KEY,
  sale_id      INT NOT NULL,
  product_id   INT NOT NULL,
  quantity     INT NOT NULL CHECK (quantity > 0),
  unit_price   DECIMAL(10,2) NOT NULL,
  line_total   DECIMAL(10,2) NOT NULL,
  UNIQUE (sale_id, product_id),
  FOREIGN KEY (sale_id)    REFERENCES sales(sale_id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE stock_receipts (
  receipt_id    INT AUTO_INCREMENT PRIMARY KEY,
  product_id    INT NOT NULL,
  supplier_id   INT NOT NULL,
  staff_id      INT NOT NULL,
  quantity      INT NOT NULL CHECK (quantity > 0),
  received_date DATE NOT NULL DEFAULT (CURRENT_DATE),
  FOREIGN KEY (product_id)  REFERENCES products(product_id),
  FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id),
  FOREIGN KEY (staff_id)    REFERENCES staff(staff_id)
);

-- Sample data so the pages are not empty
INSERT INTO categories (name, description) VALUES
 ('Groceries','Food and drinks'),('Household','Cleaning and home items'),('Stationery','School and office items');
INSERT INTO suppliers (name, phone, email) VALUES
 ('Mwanza Traders','0712000111','info@mwanzatraders.co.tz'),('Dar Wholesale','0754000222',NULL);
INSERT INTO staff (full_name, username, password_hash, role) VALUES
 ('Zuberi (Owner)','zuberi','not-used-in-this-demo','admin'),('Amina','amina','not-used-in-this-demo','cashier');
INSERT INTO products (category_id, supplier_id, sku, name, cost_price, unit_price, stock_qty, reorder_level) VALUES
 (1,1,'P001','Sugar 1kg',2300,2800,0,5),
 (1,1,'P002','Rice 5kg',12500,14000,18,5),
 (2,2,'P003','Soap bar',900,1200,4,5),
 (3,2,'P004','Notebook A5',1000,1500,40,10),
 (1,1,'P005','Cooking oil 1L',4200,5000,25,8);
INSERT INTO sales (staff_id, sale_date, payment_method, total_amount) VALUES (2, NOW(), 'cash', 16800);
INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, line_total) VALUES
 (1,2,1,14000,14000),(1,1,1,2800,2800);
