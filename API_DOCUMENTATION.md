# 📡 API Documentation

This document describes the API endpoints and data structures used in the Blood Donation Management System (BDMS).

## 🔐 Authentication

The system uses session-based authentication. Users must log in through the web interface to access protected endpoints.

### Authentication Flow
1. User submits login form
2. Server validates credentials
3. Session is created
4. All subsequent requests include session cookie

## 📊 Database Schema

### Core Tables

#### donors
```sql
CREATE TABLE donors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    blood_type VARCHAR(5),
    age INT,
    weight DECIMAL(5,2),
    last_donation_date DATE NULL,
    has_health_condition TINYINT(1) DEFAULT 0,
    health_condition_details TEXT NULL,
    is_eligible TINYINT(1) DEFAULT 1,
    eligibility_checked_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### blood_banks
```sql
CREATE TABLE blood_banks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(100) NOT NULL,
    address VARCHAR(255),
    phone VARCHAR(20)
);
```

#### hospitals
```sql
CREATE TABLE hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(100) NOT NULL,
    address VARCHAR(255),
    phone VARCHAR(20)
);
```

#### blood_inventory
```sql
CREATE TABLE blood_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bloodbank_id INT NOT NULL,
    blood_type VARCHAR(5) NOT NULL,
    units INT NOT NULL,
    collected_at DATE NOT NULL,
    expiry_date DATE NOT NULL,
    FOREIGN KEY (bloodbank_id) REFERENCES blood_banks(id) ON DELETE CASCADE
);
```

#### blood_requests
```sql
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
```

#### appointments
```sql
CREATE TABLE appointments (
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
```

## 🌐 API Endpoints

### Authentication Endpoints

#### POST /login_process.php
Authenticates a user and creates a session.

**Request Body:**
```json
{
    "email": "user@example.com",
    "password": "password123",
    "role": "donor|bloodbank|hospital"
}
```

**Response:**
- Success: Redirect to appropriate dashboard
- Failure: Error message with redirect back to login

#### GET /logout.php
Destroys the session and redirects to login.

### Donor Endpoints

#### GET /donor_dashboard.php
Returns the donor's dashboard with personal information and statistics.

**Response Data:**
```php
$donor = [
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'blood_type' => 'A+',
    'last_donation_date' => '2024-01-15',
    'age' => 25,
    'weight' => 70.5,
    'has_health_condition' => 0,
    'health_condition_details' => null,
    'is_eligible' => 1,
    'eligibility_checked_at' => null
];
```

#### POST /register.php
Registers a new donor account.

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "phone": "555-0301",
    "address": "123 Main St",
    "blood_type": "A+",
    "age": 25,
    "weight": 70.5
}
```

#### POST /check_eligibility.php
Checks if a donor is eligible to donate blood.

**Request Body:**
```json
{
    "age": 25,
    "weight": 70.5,
    "has_health_condition": 0,
    "health_condition_details": "",
    "last_donation_date": "2024-01-15"
}
```

**Response:**
```json
{
    "eligible": true,
    "reason": "Eligible to donate",
    "next_eligible_date": "2024-04-15"
}
```

#### POST /schedule_appointment.php
Schedules a new donation appointment.

**Request Body:**
```json
{
    "bloodbank_id": 1,
    "date": "2024-04-01",
    "time": "10:00:00"
}
```

#### GET /get_appointment.php
Retrieves appointment details for a donor.

**Query Parameters:**
- `id` (optional): Appointment ID

#### POST /edit_appointment.php
Updates an existing appointment.

#### POST /delete_appointment.php
Cancels an appointment.

### Blood Bank Endpoints

#### GET /bloodbank_dashboard.php
Returns blood bank dashboard with inventory and requests.

#### POST /add_inventory.php
Adds new blood units to inventory.

**Request Body:**
```json
{
    "blood_type": "A+",
    "units": 10,
    "collected_at": "2024-04-01",
    "expiry_date": "2024-05-01"
}
```

#### GET /get_blood_inventory.php
Retrieves current blood inventory.

**Response:**
```json
{
    "inventory": [
        {
            "blood_type": "A+",
            "units": 10,
            "collected_at": "2024-04-01",
            "expiry_date": "2024-05-01"
        }
    ]
}
```

#### POST /update_inventory.php
Updates blood inventory levels.

#### GET /bloodbank_requests.php
Retrieves pending blood requests.

#### POST /process_request.php
Processes a blood request (approve/reject).

**Request Body:**
```json
{
    "request_id": 1,
    "action": "approve|reject",
    "notes": "Processing notes"
}
```

### Hospital Endpoints

#### GET /hospital_dashboard.php
Returns hospital dashboard with request history.

#### POST /request_blood.php
Submits a new blood request.

**Request Body:**
```json
{
    "bloodbank_id": 1,
    "blood_type": "A+",
    "units": 5,
    "urgency": "normal|urgent|emergency",
    "notes": "Patient requires blood for surgery"
}
```

#### GET /check_requests.php
Retrieves status of blood requests.

#### POST /edit_request.php
Modifies an existing blood request.

#### POST /delete_request.php
Cancels a blood request.

### Utility Endpoints

#### GET /download_report.php
Generates and downloads PDF reports.

**Query Parameters:**
- `type`: `inventory|requests|donors|appointments`
- `format`: `pdf|csv`
- `date_from`: Start date (optional)
- `date_to`: End date (optional)

#### POST /update_donors_table.php
Updates donor information in bulk.

## 📝 Response Formats

### Success Response
```json
{
    "success": true,
    "data": {},
    "message": "Operation completed successfully"
}
```

### Error Response
```json
{
    "success": false,
    "error": "Error message",
    "code": "ERROR_CODE"
}
```

### Redirect Response
Most endpoints use HTML redirects rather than JSON responses for web interface compatibility.

## 🔒 Security Considerations

### Input Validation
- All inputs are sanitized using `mysqli_real_escape_string()`
- Email validation using `filter_var()`
- Numeric validation for IDs and quantities

### SQL Injection Prevention
- All queries use prepared statements
- Parameter binding for all user inputs
- Input type checking

### Session Security
- Session regeneration on login
- Session timeout configuration
- Secure cookie settings

### Password Security
- Password hashing using PHP's `password_hash()`
- Minimum password length requirements
- Password strength validation

## 🚨 Error Codes

| Code | Description |
|------|-------------|
| AUTH_FAILED | Authentication failed |
| INVALID_INPUT | Invalid input data |
| DB_ERROR | Database operation failed |
| PERMISSION_DENIED | Insufficient permissions |
| NOT_FOUND | Resource not found |
| DUPLICATE_ENTRY | Duplicate data entry |
| VALIDATION_ERROR | Input validation failed |

## 📊 Data Types

### Blood Types
- `A+`, `A-`, `B+`, `B-`, `AB+`, `AB-`, `O+`, `O-`

### Request Status
- `pending`, `approved`, `rejected`, `delivered`

### Appointment Status
- `pending`, `approved`, `rejected`

### Urgency Levels
- `normal`, `urgent`, `emergency`

### User Roles
- `donor`, `bloodbank`, `hospital`, `admin`

## 🔧 Configuration

### Database Configuration
Located in `config/database.php`:
```php
$host = "localhost";
$username = "root";
$password = "";
$database = "bdms";
```

### Session Configuration
Default session settings in `php.ini`:
```ini
session.gc_maxlifetime = 7200
session.cookie_httponly = 1
session.use_strict_mode = 1
```

## 📈 Rate Limiting

Currently, the system doesn't implement API rate limiting. For production deployment, consider implementing:
- Request throttling per IP
- Login attempt limits
- API key-based authentication for external integrations

## 🔄 Version History

### v1.0.0
- Initial API implementation
- Basic CRUD operations
- Session-based authentication
- PDF report generation

---

For more detailed information about specific endpoints or implementation details, please refer to the source code or create an issue on GitHub.
