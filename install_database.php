<?php
// Auto-install database schema for Blood Donation Management System
// This script will automatically create tables if they don't exist

// Include database configuration
require_once 'config/database.php';

// Check if we have a valid database connection
if (!$conn) {
    die("Database connection failed. Please check your configuration.");
}

// Determine database type
$is_postgresql = is_resource($conn) && get_resource_type($conn) === 'pgsql link';

// SQL schema for PostgreSQL
$postgresql_schema = "
-- Drop existing tables if they exist
DROP TABLE IF EXISTS appointments CASCADE;
DROP TABLE IF EXISTS blood_requests CASCADE;
DROP TABLE IF EXISTS blood_inventory CASCADE;
DROP TABLE IF EXISTS donors CASCADE;
DROP TABLE IF EXISTS blood_banks CASCADE;
DROP TABLE IF EXISTS hospitals CASCADE;
DROP TABLE IF EXISTS admins CASCADE;

-- Create admins table
CREATE TABLE admins (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create hospitals table
CREATE TABLE hospitals (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    license_number VARCHAR(100),
    contact_person VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create blood_banks table
CREATE TABLE blood_banks (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    license_number VARCHAR(100),
    operating_hours VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create donors table
CREATE TABLE donors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    blood_type VARCHAR(10) NOT NULL,
    age INTEGER,
    weight DECIMAL(5,2),
    gender VARCHAR(10),
    last_donation_date DATE,
    has_health_condition BOOLEAN DEFAULT FALSE,
    health_condition_details TEXT,
    is_eligible BOOLEAN DEFAULT TRUE,
    eligibility_checked_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create blood_inventory table
CREATE TABLE blood_inventory (
    id SERIAL PRIMARY KEY,
    blood_bank_id INTEGER REFERENCES blood_banks(id) ON DELETE CASCADE,
    blood_type VARCHAR(10) NOT NULL,
    units_available INTEGER DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(blood_bank_id, blood_type)
);

-- Create blood_requests table
CREATE TABLE blood_requests (
    id SERIAL PRIMARY KEY,
    hospital_id INTEGER REFERENCES hospitals(id) ON DELETE CASCADE,
    blood_type VARCHAR(10) NOT NULL,
    units_required INTEGER NOT NULL,
    urgency VARCHAR(20) DEFAULT 'normal',
    request_date DATE DEFAULT CURRENT_DATE,
    status VARCHAR(20) DEFAULT 'pending',
    fulfilled_date DATE,
    blood_bank_id INTEGER REFERENCES blood_banks(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create appointments table
CREATE TABLE appointments (
    id SERIAL PRIMARY KEY,
    donor_id INTEGER REFERENCES donors(id) ON DELETE CASCADE,
    blood_bank_id INTEGER REFERENCES blood_banks(id) ON DELETE CASCADE,
    appointment_date TIMESTAMP NOT NULL,
    status VARCHAR(20) DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

// SQL schema for MySQL
$mysql_schema = "
-- Drop existing tables if they exist
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS blood_requests;
DROP TABLE IF EXISTS blood_inventory;
DROP TABLE IF EXISTS donors;
DROP TABLE IF EXISTS blood_banks;
DROP TABLE IF EXISTS hospitals;
DROP TABLE IF EXISTS admins;

-- Create admins table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create hospitals table
CREATE TABLE hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    license_number VARCHAR(100),
    contact_person VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create blood_banks table
CREATE TABLE blood_banks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    license_number VARCHAR(100),
    operating_hours VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create donors table
CREATE TABLE donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    blood_type VARCHAR(10) NOT NULL,
    age INT,
    weight DECIMAL(5,2),
    gender VARCHAR(10),
    last_donation_date DATE,
    has_health_condition TINYINT(1) DEFAULT 0,
    health_condition_details TEXT,
    is_eligible TINYINT(1) DEFAULT 1,
    eligibility_checked_at TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create blood_inventory table
CREATE TABLE blood_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blood_bank_id INT,
    blood_type VARCHAR(10) NOT NULL,
    units_available INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (blood_bank_id) REFERENCES blood_banks(id) ON DELETE CASCADE,
    UNIQUE KEY (blood_bank_id, blood_type)
);

-- Create blood_requests table
CREATE TABLE blood_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT,
    blood_type VARCHAR(10) NOT NULL,
    units_required INT NOT NULL,
    urgency VARCHAR(20) DEFAULT 'normal',
    request_date DATE DEFAULT CURRENT_DATE,
    status VARCHAR(20) DEFAULT 'pending',
    fulfilled_date DATE NULL,
    blood_bank_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE,
    FOREIGN KEY (blood_bank_id) REFERENCES blood_banks(id) ON DELETE SET NULL
);

-- Create appointments table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT,
    blood_bank_id INT,
    appointment_date TIMESTAMP NOT NULL,
    status VARCHAR(20) DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(id) ON DELETE CASCADE,
    FOREIGN KEY (blood_bank_id) REFERENCES blood_banks(id) ON DELETE CASCADE
);
";

// Sample data for both databases
$sample_data = "
-- Insert sample data into admins table
INSERT INTO admins (username, password, email) VALUES
('admin', 'admin123', 'admin@bdms.com');

-- Insert sample data into hospitals
INSERT INTO hospitals (name, email, password, phone, address, city, state, license_number, contact_person) VALUES
('City General Hospital', 'cityhospital@bdms.com', 'hospital123', '555-0101', '123 Main St', 'New York', 'NY', 'HOSP-001', 'Dr. John Smith'),
('St. Mary Medical Center', 'stmary@bdms.com', 'hospital123', '555-0102', '456 Oak Ave', 'Los Angeles', 'CA', 'HOSP-002', 'Dr. Sarah Johnson');

-- Insert sample data into blood_banks
INSERT INTO blood_banks (name, email, password, phone, address, city, state, license_number, operating_hours) VALUES
('City Blood Bank', 'cityblood@bdms.com', 'blood123', '555-0201', '789 Blood Center Dr', 'New York', 'NY', 'BB-001', 'Mon-Fri 8AM-6PM'),
('Regional Blood Services', 'regionalblood@bdms.com', 'blood123', '555-0202', '321 Donation Way', 'Los Angeles', 'CA', 'BB-002', 'Mon-Sat 9AM-5PM');

-- Insert sample data into donors
INSERT INTO donors (name, email, password, phone, address, city, state, blood_type, age, weight, gender, last_donation_date, is_eligible) VALUES
('John Doe', 'john@example.com', 'donor123', '555-0301', '123 Elm St', 'New York', 'NY', 'O+', 35, 75.5, 'Male', '2023-01-15', 1),
('Jane Smith', 'jane@example.com', 'donor123', '555-0302', '456 Maple Ave', 'Los Angeles', 'CA', 'A+', 28, 65.0, 'Female', '2023-02-20', 1),
('Mike Johnson', 'mike@example.com', 'donor123', '555-0303', '789 Pine Rd', 'Chicago', 'IL', 'B-', 42, 80.0, 'Male', '2022-12-10', 1),
('Emily Davis', 'emily@example.com', 'donor123', '555-0304', '321 Oak Ln', 'Houston', 'TX', 'AB+', 31, 58.5, 'Female', '2023-03-05', 1);

-- Insert sample data into blood_inventory
INSERT INTO blood_inventory (blood_bank_id, blood_type, units_available) VALUES
(1, 'O+', 25),
(1, 'O-', 15),
(1, 'A+', 30),
(1, 'A-', 12),
(1, 'B+', 20),
(1, 'B-', 8),
(1, 'AB+', 10),
(1, 'AB-', 5),
(2, 'O+', 18),
(2, 'O-', 10),
(2, 'A+', 22),
(2, 'A-', 8),
(2, 'B+', 15),
(2, 'B-', 6),
(2, 'AB+', 7),
(2, 'AB-', 3);

-- Insert sample data into blood_requests
INSERT INTO blood_requests (hospital_id, blood_type, units_required, urgency, status) VALUES
(1, 'O+', 5, 'urgent', 'pending'),
(1, 'A+', 3, 'normal', 'pending'),
(2, 'B-', 2, 'urgent', 'pending');

-- Insert sample data into appointments
INSERT INTO appointments (donor_id, blood_bank_id, appointment_date, status) VALUES
(1, 1, '2024-04-15 10:00:00', 'scheduled'),
(2, 2, '2024-04-16 14:00:00', 'scheduled'),
(3, 1, '2024-04-17 09:00:00', 'scheduled');
";

// Execute schema and sample data
try {
    if ($is_postgresql) {
        // Execute PostgreSQL schema
        $statements = explode(';', $postgresql_schema);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                pg_query($conn, $statement);
            }
        }
        
        // Execute sample data
        $statements = explode(';', $sample_data);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                pg_query($conn, $statement);
            }
        }
        
        echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Installed Successfully</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-8'>
                <div class='card shadow'>
                    <div class='card-header bg-success text-white'>
                        <h4 class='mb-0'>
                            <i class='fas fa-check-circle me-2'></i>
                            Database Installed Successfully!
                        </h4>
                    </div>
                    <div class='card-body'>
                        <div class='alert alert-success'>
                            <h5><i class='fas fa-database me-2'></i>PostgreSQL Database Ready</h5>
                            <p>Your Blood Donation Management System database has been successfully installed with all tables and sample data.</p>
                        </div>
                        
                        <h6><i class='fas fa-list me-2'></i>Tables Created:</h6>
                        <ul>
                            <li>admins</li>
                            <li>hospitals</li>
                            <li>blood_banks</li>
                            <li>donors</li>
                            <li>blood_inventory</li>
                            <li>blood_requests</li>
                            <li>appointments</li>
                        </ul>
                        
                        <h6><i class='fas fa-users me-2'></i>Sample Data Added:</h6>
                        <ul>
                            <li>1 Admin user</li>
                            <li>2 Hospitals</li>
                            <li>2 Blood banks</li>
                            <li>4 Donors</li>
                            <li>Blood inventory data</li>
                            <li>Blood requests</li>
                            <li>Appointments</li>
                        </ul>
                        
                        <div class='text-center mt-4'>
                            <a href='index.php' class='btn btn-success btn-lg me-2'>
                                <i class='fas fa-sign-in-alt me-2'></i>Go to Login
                            </a>
                            <a href='login.php' class='btn btn-primary btn-lg'>
                                <i class='fas fa-user me-2'></i>Login Page
                            </a>
                        </div>
                    </div>
                    <div class='card-footer text-muted text-center'>
                        <small>Blood Donation Management System - Ready to Use!</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
        
    } else {
        // Execute MySQL schema
        $statements = explode(';', $mysql_schema);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                mysqli_query($conn, $statement);
            }
        }
        
        // Execute sample data
        $statements = explode(';', $sample_data);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                mysqli_query($conn, $statement);
            }
        }
        
        echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Installed Successfully</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-8'>
                <div class='card shadow'>
                    <div class='card-header bg-success text-white'>
                        <h4 class='mb-0'>
                            <i class='fas fa-check-circle me-2'></i>
                            Database Installed Successfully!
                        </h4>
                    </div>
                    <div class='card-body'>
                        <div class='alert alert-success'>
                            <h5><i class='fas fa-database me-2'></i>MySQL Database Ready</h5>
                            <p>Your Blood Donation Management System database has been successfully installed with all tables and sample data.</p>
                        </div>
                        
                        <h6><i class='fas fa-list me-2'></i>Tables Created:</h6>
                        <ul>
                            <li>admins</li>
                            <li>hospitals</li>
                            <li>blood_banks</li>
                            <li>donors</li>
                            <li>blood_inventory</li>
                            <li>blood_requests</li>
                            <li>appointments</li>
                        </ul>
                        
                        <h6><i class='fas fa-users me-2'></i>Sample Data Added:</h6>
                        <ul>
                            <li>1 Admin user</li>
                            <li>2 Hospitals</li>
                            <li>2 Blood banks</li>
                            <li>4 Donors</li>
                            <li>Blood inventory data</li>
                            <li>Blood requests</li>
                            <li>Appointments</li>
                        </ul>
                        
                        <div class='text-center mt-4'>
                            <a href='index.php' class='btn btn-success btn-lg me-2'>
                                <i class='fas fa-sign-in-alt me-2'></i>Go to Login
                            </a>
                            <a href='login.php' class='btn btn-primary btn-lg'>
                                <i class='fas fa-user me-2'></i>Login Page
                            </a>
                        </div>
                    </div>
                    <div class='card-footer text-muted text-center'>
                        <small>Blood Donation Management System - Ready to Use!</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
    }
    
} catch (Exception $e) {
    echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Installation Error</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='bg-light'>
    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-md-8'>
                <div class='card shadow'>
                    <div class='card-header bg-danger text-white'>
                        <h4 class='mb-0'>
                            <i class='fas fa-exclamation-triangle me-2'></i>
                            Database Installation Error
                        </h4>
                    </div>
                    <div class='card-body'>
                        <div class='alert alert-danger'>
                            <h5><i class='fas fa-exclamation-circle me-2'></i>Installation Failed</h5>
                            <p>Error: " . htmlspecialchars($e->getMessage()) . "</p>
                        </div>
                        
                        <div class='text-center mt-4'>
                            <a href='index.php' class='btn btn-primary'>
                                <i class='fas fa-home me-2'></i>Go to Homepage
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
}

?>
