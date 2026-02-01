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

ALTER TABLE jobcards
ADD COLUMN job_status VARCHAR(30) DEFAULT 'New Job' AFTER jobcard_date;

ALTER TABLE jobcards
ADD COLUMN paid_amount DECIMAL(10,2) DEFAULT 0 AFTER job_status;




ALTER TABLE jobcards
ADD job_status VARCHAR(30) DEFAULT 'New Job' AFTER jobcard_date,
ADD paid_amount DECIMAL(10,2) DEFAULT 0 AFTER job_status;



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


CREATE TABLE jobcards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jobcard_no VARCHAR(50),
    jobcard_date DATE,
    job_status VARCHAR(30) DEFAULT 'New Job',
    paid_amount DECIMAL(10,2) DEFAULT 0,
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

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_status VARCHAR(20),     
    sales_date DATE,              
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
    item_name VARCHAR(150),
    qty DECIMAL(10,2),
    price_per_qty DECIMAL(10,2),
    total_price DECIMAL(10,2),
    CONSTRAINT fk_sales_items_sale
        FOREIGN KEY (sale_id) REFERENCES sales(id)
        ON DELETE CASCADE,
    INDEX (stock_id)
) ENGINE=InnoDB;

CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150),
    addr1 VARCHAR(200) NOT NULL,
    addr2 VARCHAR(200),
    city VARCHAR(100) NOT NULL,
    pincode VARCHAR(10) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_no VARCHAR(50),
    order_date DATE,
    supplier_id INT,
    supplier_name VARCHAR(150),
    supplier_phone VARCHAR(20),
    item_name VARCHAR(150),
    brand VARCHAR(100),
    model VARCHAR(100),
    qty INT,
    purchase_price DECIMAL(10,2),
    selling_price DECIMAL(10,2),
    total_price DECIMAL(10,2),
    payment_date DATE,
    payment_mode VARCHAR(50),
    reference_no VARCHAR(100),
    paid_amount DECIMAL(10,2),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
