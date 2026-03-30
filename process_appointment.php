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
    $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $blood_type = isset($_POST['blood_type']) ? $_POST['blood_type'] : '';
    $units = isset($_POST['units']) ? intval($_POST['units']) : 1;

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

        if ($action === 'approve') {
            // Update appointment status
            $sql = "UPDATE appointments SET status = 'approved' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();

            // Add units to inventory after successful donation
            $sql = "INSERT INTO blood_inventory (bloodbank_id, blood_type, units, expiry_date) 
                    VALUES (?, ?, ?, DATE_ADD(CURDATE(), INTERVAL 42 DAY))
                    ON DUPLICATE KEY UPDATE units = units + ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isii", $bloodbank_id, $appointment['blood_type'], $units, $units);
            $stmt->execute();

            $_SESSION['success_message'] = "Appointment approved and blood units added to inventory";
        } else if ($action === 'reject') {
            // Update appointment status
            $sql = "UPDATE appointments SET status = 'rejected' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $appointment_id);
            $stmt->execute();

            $_SESSION['success_message'] = "Appointment rejected";
        } else {
            throw new Exception("Invalid action");
        }

        $conn->commit();
        header("Location: bloodbank_dashboard.php");
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: bloodbank_dashboard.php");
    }
    exit();
} else {
    header("Location: bloodbank_dashboard.php");
    exit();
}
?> 