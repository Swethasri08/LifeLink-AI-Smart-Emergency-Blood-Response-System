<?php
session_start();
require_once 'config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow cross-origin requests (if your hospital dashboard is on a different domain/port - adjust as needed)
// header('Access-Control-Allow-Origin: *');
// header('Content-Type: application/json');

// Check if user is logged in (optional, depending on whether you want this public)
// if (!isset($_SESSION['user_id'])) {
//     echo json_encode(['error' => 'Not authorized']);
//     exit();
// }

// Get parameters
$bloodbank_id = isset($_GET['bloodbank_id']) ? (int)$_GET['bloodbank_id'] : 0;

// Debug logging
error_log("get_blood_inventory.php: Received Blood Bank ID=" . $bloodbank_id);

// Validate input
if (!$bloodbank_id) {
    error_log("get_blood_inventory.php: Invalid input. bloodbank_id=" . $bloodbank_id);
    echo json_encode(['error' => 'Invalid input: Blood Bank ID missing.']);
    exit();
}

// Remove expired blood before checking inventory
// Note: This is a simple approach. A better approach might be a scheduled task.
try {
    $conn->query("DELETE FROM blood_inventory WHERE expiry_date < CURDATE()");
    error_log("get_blood_inventory.php: Expired blood removal attempted.");
} catch (mysqli_sql_exception $e) {
     error_log("get_blood_inventory.php: Error deleting expired blood: " . $e->getMessage());
     // Continue execution even if deletion fails
}

// Get current inventory (summing units for non-expired blood by type)
$sql = "SELECT blood_type, COALESCE(SUM(units), 0) as total_units
        FROM blood_inventory
        WHERE bloodbank_id = ? AND expiry_date >= CURDATE()
        GROUP BY blood_type
        ORDER BY FIELD(blood_type, 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-')"; // Order blood types

error_log("get_blood_inventory.php: Executing SQL Query: " . $sql . ", Params: [" . $bloodbank_id . "]");

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("get_blood_inventory.php: Prepare failed: " . $conn->error);
    echo json_encode(['error' => 'Database error: Prepare failed.']);
    exit();
}

$stmt->bind_param("i", $bloodbank_id);
if (!$stmt->execute()) {
    error_log("get_blood_inventory.php: Execute failed: " . $stmt->error);
    echo json_encode(['error' => 'Database error: Execute failed.']);
    exit();
}

$result = $stmt->get_result();
$inventory_data = $result->fetch_all(MYSQLI_ASSOC);

error_log("get_blood_inventory.php: Fetched Inventory Data: " . print_r($inventory_data, true));

// Return the inventory data
header('Content-Type: application/json');
echo json_encode($inventory_data);

$stmt->close();
$conn->close();
?> 