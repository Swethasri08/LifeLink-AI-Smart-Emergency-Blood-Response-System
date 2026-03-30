<?php
session_start();
require_once 'config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is a blood bank
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bloodbank') {
    header("Location: login.php?error=not_authorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bloodbank_id = $_SESSION['user_id'];
    $blood_type = trim($_POST['blood_type']);
    $units = (int)$_POST['units'];

    // Debug information
    error_log("Updating inventory - Blood Bank ID: $bloodbank_id, Blood Type: $blood_type, Units: $units");

    // Validate input
    if (empty($blood_type) || $units < 1) {
        error_log("Invalid input - Blood Type: $blood_type, Units: $units");
        header("Location: bloodbank_dashboard.php?error=invalid_input");
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if inventory exists for this blood type
        $sql = "SELECT * FROM blood_inventory WHERE bloodbank_id = ? AND blood_type = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("is", $bloodbank_id, $blood_type);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;

        if ($exists) {
            // Update existing inventory
            $sql = "UPDATE blood_inventory SET units = units + ? WHERE bloodbank_id = ? AND blood_type = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare update failed: " . $conn->error);
            }
            
            $stmt->bind_param("iis", $units, $bloodbank_id, $blood_type);
            if (!$stmt->execute()) {
                throw new Exception("Execute update failed: " . $stmt->error);
            }
            
            error_log("Updated existing inventory for blood type: $blood_type");
        } else {
            // Insert new inventory
            $sql = "INSERT INTO blood_inventory (bloodbank_id, blood_type, units) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare insert failed: " . $conn->error);
            }
            
            $stmt->bind_param("isi", $bloodbank_id, $blood_type, $units);
            if (!$stmt->execute()) {
                throw new Exception("Execute insert failed: " . $stmt->error);
            }
            
            error_log("Created new inventory for blood type: $blood_type");
        }

        $conn->commit();
        error_log("Inventory update successful");
        header("Location: bloodbank_dashboard.php?success=inventory_updated");
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error updating inventory: " . $e->getMessage());
        header("Location: bloodbank_dashboard.php?error=update_failed");
    }
    exit();
} else {
    header("Location: bloodbank_dashboard.php");
    exit();
}
?> 