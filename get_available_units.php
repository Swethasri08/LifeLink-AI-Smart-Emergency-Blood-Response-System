<?php
session_start();
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authorized']);
    exit();
}

// Get parameters
$bloodbank_id = isset($_GET['bloodbank_id']) ? (int)$_GET['bloodbank_id'] : 0;
$blood_type = trim(isset($_GET['blood_type']) ? $_GET['blood_type'] : '');

// Debug logging
error_log("Getting available units for Blood Bank ID: " . $bloodbank_id . ", Blood Type: " . $blood_type);

// Validate input
if (!$bloodbank_id || !$blood_type) {
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

// First, let's check if the blood bank exists
$check_sql = "SELECT id FROM blood_banks WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $bloodbank_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    error_log("Blood bank not found: " . $bloodbank_id);
    echo json_encode(['error' => 'Blood bank not found']);
    exit();
}

// Get available units (sum of non-expired units)
$sql = "SELECT COALESCE(SUM(units), 0) as units
        FROM blood_inventory
        WHERE bloodbank_id = ? AND blood_type = ? AND expiry_date >= CURDATE()";

// Enhanced Debug logging - show exact parameters before query
error_log("get_available_units.php: Executing query with bloodbank_id=" . $bloodbank_id . ", blood_type='" . $blood_type . "'");

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['error' => 'Database error']);
    exit();
}

$stmt->bind_param("is", $bloodbank_id, $blood_type);
if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(['error' => 'Database error']);
    exit();
}

$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Debug logging
error_log("Available units query result: " . print_r($row, true));

// Let's also check the raw inventory data
$debug_sql = "SELECT * FROM blood_inventory WHERE bloodbank_id = ? AND blood_type = ?";
$debug_stmt = $conn->prepare($debug_sql);
$debug_stmt->bind_param("is", $bloodbank_id, $blood_type);
$debug_stmt->execute();
$debug_result = $debug_stmt->get_result();
$debug_data = $debug_result->fetch_all(MYSQLI_ASSOC);
error_log("Raw inventory data: " . print_r($debug_data, true));

// Return the units
echo json_encode(['units' => (int)$row['units']]);
?> 