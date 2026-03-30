<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a blood bank
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bloodbank') {
    header("Location: login.php?error=not_authorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bloodbank_id = $_SESSION['user_id'];
    $appointment_id = $_POST['appointment_id'];
    $action = $_POST['action'];

    // Validate input
    if (empty($appointment_id) || empty($action)) {
        header("Location: bloodbank_dashboard.php?error=invalid_input");
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get appointment details
        $sql = "SELECT a.*, d.blood_type 
                FROM appointments a 
                JOIN donors d ON a.donor_id = d.id 
                WHERE a.id = ? AND a.bloodbank_id = ? AND a.status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $appointment_id, $bloodbank_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Appointment not found");
        }

        $appointment = $result->fetch_assoc();

        $appointment['blood_type'] = trim($appointment['blood_type']);

        if ($action === 'approve') {
            // Update appointment status
            $sql = "UPDATE appointments SET status = 'approved' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();

            // Add one unit to inventory after successful donation
            $sql = "INSERT INTO blood_inventory (bloodbank_id, blood_type, units) 
                    VALUES (?, ?, 1) 
                    ON DUPLICATE KEY UPDATE units = units + 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $bloodbank_id, $appointment['blood_type']);
            $stmt->execute();
        } else if ($action === 'reject') {
            // Update appointment status
            $sql = "UPDATE appointments SET status = 'rejected' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();
        } else {
            throw new Exception("Invalid action");
        }

        $conn->commit();
        header("Location: bloodbank_dashboard.php?success=appointment_updated");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: bloodbank_dashboard.php?error=" . urlencode($e->getMessage()));
    }
    exit();
} else {
    header("Location: bloodbank_dashboard.php");
    exit();
}
?> 