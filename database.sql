-- Create database
CREATE DATABASE IF NOT EXISTS bdms;
USE bdms;

-- Drop old tables if needed
DROP TABLE IF EXISTS blood_inventory;
DROP TABLE IF EXISTS blood_requests;
DROP TABLE IF EXISTS blood_banks;
DROP TABLE IF EXISTS hospitals;

-- Create tables
CREATE TABLE IF NOT EXISTS donors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    blood_type VARCHAR(5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Blood Banks
CREATE TABLE blood_banks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(100) NOT NULL,
    address VARCHAR(255),
    phone VARCHAR(20)
);

-- Hospitals
CREATE TABLE hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(100) NOT NULL,
    address VARCHAR(255),
    phone VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS appointments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    donor_id INT,
    bloodbank_id INT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id),
    FOREIGN KEY (bloodbank_id) REFERENCES blood_banks(id)
);

-- Blood Inventory (with expiry)
CREATE TABLE blood_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bloodbank_id INT NOT NULL,
    blood_type VARCHAR(5) NOT NULL,
    units INT NOT NULL,
    collected_at DATE NOT NULL,
    expiry_date DATE NOT NULL,
    FOREIGN KEY (bloodbank_id) REFERENCES blood_banks(id) ON DELETE CASCADE
);

-- Blood Requests
CREATE TABLE blood_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    bloodbank_id INT NOT NULL,
    blood_type VARCHAR(5) NOT NULL,
    units INT NOT NULL,
    urgency ENUM('normal', 'urgent', 'emergency') DEFAULT 'normal',
    status ENUM('pending', 'approved', 'rejected', 'delivered') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivered_at TIMESTAMP NULL,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id),
    FOREIGN KEY (bloodbank_id) REFERENCES blood_banks(id)
);

-- Insert sample data
-- Sample admin
INSERT INTO admins (name, email, password) VALUES
('Admin User', 'admin@bdms.com', 'admin123');

-- Sample blood banks
INSERT INTO blood_banks (name, email, password, address, phone) VALUES
('City Blood Bank', 'cityblood@bdms.com', 'blood123', '123 Main St, City', '555-0101'),
('Central Blood Center', 'central@bdms.com', 'blood123', '456 Center Ave, City', '555-0102');

-- Sample hospitals
INSERT INTO hospitals (name, email, password, address, phone) VALUES
('City General Hospital', 'cityhospital@bdms.com', 'hospital123', '789 Hospital Rd, City', '555-0201'),
('Central Medical Center', 'centralmed@bdms.com', 'hospital123', '321 Medical Blvd, City', '555-0202');

-- Sample donors
INSERT INTO donors (name, email, password, phone, blood_type) VALUES
('John Doe', 'john@example.com', 'donor123', '555-0301', 'A+'),
('Jane Smith', 'jane@example.com', 'donor123', '555-0302', 'O-'),
('Mike Johnson', 'mike@example.com', 'donor123', '555-0303', 'B+');

-- Initialize blood inventory for blood banks
INSERT INTO blood_inventory (bloodbank_id, blood_type, units, collected_at, expiry_date) VALUES
(1, 'A+', 10, '2024-04-01', '2024-05-01'),
(1, 'A-', 5, '2024-04-01', '2024-05-01'),
(1, 'B+', 8, '2024-04-01', '2024-05-01'),
(1, 'B-', 4, '2024-04-01', '2024-05-01'),
(1, 'AB+', 3, '2024-04-01', '2024-05-01'),
(1, 'AB-', 2, '2024-04-01', '2024-05-01'),
(1, 'O+', 15, '2024-04-01', '2024-05-01'),
(1, 'O-', 7, '2024-04-01', '2024-05-01'),
(2, 'A+', 12, '2024-04-01', '2024-05-01'),
(2, 'A-', 6, '2024-04-01', '2024-05-01'),
(2, 'B+', 9, '2024-04-01', '2024-05-01'),
(2, 'B-', 5, '2024-04-01', '2024-05-01'),
(2, 'AB+', 4, '2024-04-01', '2024-05-01'),
(2, 'AB-', 3, '2024-04-01', '2024-05-01'),
(2, 'O+', 18, '2024-04-01', '2024-05-01'),
(2, 'O-', 8, '2024-04-01', '2024-05-01'); 