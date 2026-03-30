<?php
session_start();
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hospital_id = $_SESSION['user_id'];
    $bloodbank_id = $_POST['bloodbank_id'];
    $blood_type = trim($_POST['blood_type']);
    $units = $_POST['units'];
    $urgency = $_POST['urgency'];

    // Debug information
    error_log("Processing request - Hospital ID: $hospital_id, Blood Bank ID: $bloodbank_id, Blood Type: $blood_type, Units: $units, Urgency: $urgency");

    // Validate input
    if (empty($bloodbank_id) || empty($blood_type) || empty($units) || empty($urgency)) {
        header("Location: hospital_dashboard.php?error=empty_fields");
        exit();
    }

    // Validate units
    if ($units < 1) {
        header("Location: hospital_dashboard.php?error=invalid_units");
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert blood request
        $sql = "INSERT INTO blood_requests (hospital_id, bloodbank_id, blood_type, units, urgency, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("iisis", $hospital_id, $bloodbank_id, $blood_type, $units, $urgency);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        // Debug information
        error_log("Request inserted successfully. Request ID: " . $stmt->insert_id);

        $conn->commit();
        header("Location: hospital_dashboard.php?success=request_submitted");
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error processing request: " . $e->getMessage());
        header("Location: hospital_dashboard.php?error=request_failed");
    }
    exit();
} else {
    header("Location: hospital_dashboard.php");
    exit();
}
?> 