<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a blood bank
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bloodbank') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bloodbank_id = $_SESSION['user_id'];
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    // Validate input
    if (empty($request_id) || empty($action)) {
        header("Location: bloodbank_requests.php?error=invalid_input");
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get request details
        $sql = "SELECT * FROM blood_requests WHERE id = ? AND bloodbank_id = ? AND status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $request_id, $bloodbank_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            throw new Exception("Invalid request");
        }

        $request = $result->fetch_assoc();

        if ($action === 'approve') {
            // Check inventory
            $sql = "SELECT units FROM blood_inventory 
                   WHERE bloodbank_id = ? AND blood_type = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $bloodbank_id, $request['blood_type']);
            $stmt->execute();
            $result = $stmt->get_result();
            $inventory = $result->fetch_assoc();

            if ($inventory['units'] < $request['units']) {
                throw new Exception("Insufficient units");
            }

            // Update request status
            $sql = "UPDATE blood_requests SET status = 'approved' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $request_id);
            $stmt->execute();

            // Update inventory
            $sql = "UPDATE blood_inventory 
                   SET units = units - ? 
                   WHERE bloodbank_id = ? AND blood_type = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $request['units'], $bloodbank_id, $request['blood_type']);
            $stmt->execute();

            $conn->commit();
            header("Location: bloodbank_requests.php?success=request_approved");
        } else if ($action === 'reject') {
            // Update request status
            $sql = "UPDATE blood_requests SET status = 'rejected' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $request_id);
            $stmt->execute();

            $conn->commit();
            header("Location: bloodbank_requests.php?success=request_rejected");
        } else {
            throw new Exception("Invalid action");
        }
    } catch (Exception $e) {
        $conn->rollback();
        if ($e->getMessage() === "Insufficient units") {
            header("Location: bloodbank_requests.php?error=insufficient_units");
        } else {
            header("Location: bloodbank_requests.php?error=request_failed");
        }
    }
    exit();
} else {
    header("Location: bloodbank_requests.php");
    exit();
}
?> 