SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ----------------------------
-- TABLE: employees
-- ----------------------------
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emp_code VARCHAR(50),
    name VARCHAR(100),
    mobile VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    role VARCHAR(20),
    username VARCHAR(50),
    password VARCHAR(50),
    status VARCHAR(20),
    created_at DATETIME
);


-- ----------------------------
-- TABLE: users (login system)
-- ----------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(100),
    role VARCHAR(20),
    created_at DATETIME
);

-- Default admin login
INSERT INTO users (username, password, role, created_at)
VALUES ('admin', 'admin123', 'admin', NOW());


-- ----------------------------
-- TABLE: jobcards
-- ----------------------------
CREATE TABLE jobcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jobcard_no VARCHAR(50),
    jobcard_date DATE,
    customer_phone VARCHAR(20),
    customer_name VARCHAR(100),
    customer_city VARCHAR(100),
    machine_image VARCHAR(200),
    machine_name VARCHAR(150),
    serial_number VARCHAR(150),
    work_type VARCHAR(100),
    remarks TEXT,
    created_at DATETIME
);


-- ----------------------------
-- TABLE: stock
-- ----------------------------
CREATE TABLE stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    brand VARCHAR(50),
    model VARCHAR(50),
    stock_image VARCHAR(200),
    old_stock INT DEFAULT 0,
    new_stock INT DEFAULT 0,
    total_stock INT DEFAULT 0,
    min_quantity INT DEFAULT 0,
    purchase_price DECIMAL(10,2),
    actual_price DECIMAL(10,2),
    selling_price DECIMAL(10,2),
    gst_percent DECIMAL(5,2),
    total_price DECIMAL(10,2),
    warranty_months INT,
    created_at DATETIME
);


-- ----------------------------
-- TABLE: sales
-- ----------------------------
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_status VARCHAR(20),
    order_date DATE,
    customer_phone VARCHAR(20),
    customer_name VARCHAR(100),
    addr1 VARCHAR(200),
    addr2 VARCHAR(200),
    city VARCHAR(100),
    total_amount DECIMAL(10,2),
    paid_amount DECIMAL(10,2),
    balance DECIMAL(10,2),
    created_at DATETIME
);


-- ----------------------------
-- TABLE: sales_items
-- ----------------------------
CREATE TABLE sales_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT,
    item_name VARCHAR(150),
    part_no VARCHAR(120),
    qty DECIMAL(10,2),
    price_per_qty DECIMAL(10,2),
    gst_percent DECIMAL(10,2),
    total_price DECIMAL(10,2),
    item_image VARCHAR(200),
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE
);


-- ----------------------------
-- TABLE: outing
-- ----------------------------
CREATE TABLE outing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    outing_no VARCHAR(50),
    outing_date DATE,
    customer_name VARCHAR(100),
    phone VARCHAR(20),
    addr1 VARCHAR(200),
    addr2 VARCHAR(200),
    city VARCHAR(100),
    item_image VARCHAR(200),
    item_name VARCHAR(150),
    serial_no VARCHAR(150),
    purpose VARCHAR(150),
    expected_return DATE,
    remarks TEXT,
    created_at DATETIME
);

COMMIT;



CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(15) UNIQUE,
    name VARCHAR(100),
    addr1 VARCHAR(255),
    addr2 VARCHAR(255),
    city VARCHAR(100),
    created_at DATETIME
);


CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,

    order_status VARCHAR(20),     
    sales_date DATE,              

    customer_id INT,
    customer_phone VARCHAR(20),
    customer_name VARCHAR(100),
    addr1 VARCHAR(200),
    addr2 VARCHAR(200),
    city VARCHAR(100),

    total_amount DECIMAL(10,2),
    paid_amount DECIMAL(10,2),
    balance DECIMAL(10,2),

    created_at DATETIME,

    INDEX (customer_id)
) ENGINE=InnoDB;


CREATE TABLE sales_items (
    id INT AUTO_INCREMENT PRIMARY KEY,

    sale_id INT NOT NULL,
    stock_id INT,

    item_name VARCHAR(150),
    part_no VARCHAR(120),

    qty DECIMAL(10,2),
    price_per_qty DECIMAL(10,2),
    gst_percent DECIMAL(10,2),
    total_price DECIMAL(10,2),

    item_image VARCHAR(200),

    CONSTRAINT fk_sales_items_sale
        FOREIGN KEY (sale_id) REFERENCES sales(id)
        ON DELETE CASCADE,

    INDEX (stock_id)
) ENGINE=InnoDB;


CREATE TABLE jobcard_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jobcard_id INT NOT NULL,
    jobcard_no VARCHAR(50),
    stock_id INT,
    item_name VARCHAR(150),
    qty INT,
    price DECIMAL(10,2),
    total_amount DECIMAL(10,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE jobcard_labour (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jobcard_id INT,
    jobcard_no VARCHAR(50),
    labour_name VARCHAR(150),
    labour_cost DECIMAL(10,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
