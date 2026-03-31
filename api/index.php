<?php
// Vercel API endpoint for Blood Donation Management System
// This handles all PHP requests for Vercel deployment

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Set content type
header("Content-Type: application/json");

// Get the requested path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api', '', $path);

// Database configuration
$host = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'bdms';

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'error' => $conn->connect_error
    ]);
    exit;
}

// Route handling
switch ($path) {
    case '/login':
        handleLogin($conn);
        break;
    case '/register':
        handleRegister($conn);
        break;
    case '/donors':
        handleDonors($conn);
        break;
    case '/blood-banks':
        handleBloodBanks($conn);
        break;
    case '/hospitals':
        handleHospitals($conn);
        break;
    case '/blood-inventory':
        handleBloodInventory($conn);
        break;
    case '/blood-requests':
        handleBloodRequests($conn);
        break;
    case '/appointments':
        handleAppointments($conn);
        break;
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Endpoint not found',
            'path' => $path
        ]);
        break;
}

$conn->close();

// Handler functions
function handleLogin($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    
    // Check all user tables
    $tables = ['admins', 'donors', 'blood_banks', 'hospitals'];
    $user = null;
    $role = null;
    
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT * FROM $table WHERE email = ? AND password = ?");
        $stmt->bind_param("ss", $email, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $role = rtrim($table, 's'); // Remove 's' from plural
            break;
        }
    }
    
    if ($user) {
        echo json_encode([
            'success' => true,
            'user' => $user,
            'role' => $role,
            'message' => 'Login successful'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials'
        ]);
    }
}

function handleDonors($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $result = $conn->query("SELECT * FROM donors");
        $donors = [];
        
        while ($row = $result->fetch_assoc()) {
            $donors[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $donors
        ]);
    }
}

function handleBloodBanks($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $result = $conn->query("SELECT * FROM blood_banks");
        $bloodBanks = [];
        
        while ($row = $result->fetch_assoc()) {
            $bloodBanks[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $bloodBanks
        ]);
    }
}

function handleHospitals($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $result = $conn->query("SELECT * FROM hospitals");
        $hospitals = [];
        
        while ($row = $result->fetch_assoc()) {
            $hospitals[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $hospitals
        ]);
    }
}

function handleBloodInventory($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $result = $conn->query("
            SELECT bi.*, bb.name as blood_bank_name 
            FROM blood_inventory bi 
            JOIN blood_banks bb ON bi.blood_bank_id = bb.id
        ");
        $inventory = [];
        
        while ($row = $result->fetch_assoc()) {
            $inventory[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $inventory
        ]);
    }
}

function handleBloodRequests($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $result = $conn->query("
            SELECT br.*, h.name as hospital_name 
            FROM blood_requests br 
            JOIN hospitals h ON br.hospital_id = h.id
        ");
        $requests = [];
        
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $requests
        ]);
    }
}

function handleAppointments($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $result = $conn->query("
            SELECT a.*, d.name as donor_name, bb.name as blood_bank_name 
            FROM appointments a 
            JOIN donors d ON a.donor_id = d.id 
            JOIN blood_banks bb ON a.blood_bank_id = bb.id
        ");
        $appointments = [];
        
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $appointments
        ]);
    }
}

function handleRegister($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        return;
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $phone = $data['phone'] ?? '';
    $blood_type = $data['blood_type'] ?? '';
    
    // Insert new donor
    $stmt = $conn->prepare("INSERT INTO donors (name, email, password, phone, blood_type) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $email, $password, $phone, $blood_type);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed',
            'error' => $conn->error
        ]);
    }
}

?>
