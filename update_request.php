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
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];
    $request_type = $_POST['request_type'];

    // Validate input
    if (empty($request_id) || empty($action) || empty($request_type)) {
        header("Location: bloodbank_dashboard.php?error=invalid_input");
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get request details
        $sql = "SELECT * FROM blood_requests WHERE id = ? AND bloodbank_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $request_id, $bloodbank_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Request not found");
        }

        $request = $result->fetch_assoc();
        $request['blood_type'] = trim($request['blood_type']);

        if ($action === 'approve') {
            if ($request_type === 'hospital') {
                // For hospital requests, check and update inventory
                $sql = "SELECT units FROM blood_inventory WHERE bloodbank_id = ? AND blood_type = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $bloodbank_id, $request['blood_type']);
                $stmt->execute();
                $inventory = $stmt->get_result()->fetch_assoc();

                if (!$inventory || $inventory['units'] < $request['units']) {
                    throw new Exception("Insufficient blood units available");
                }

                // Update inventory - reduce the units
                $sql = "UPDATE blood_inventory SET units = units - ? WHERE bloodbank_id = ? AND blood_type = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iis", $request['units'], $bloodbank_id, $request['blood_type']);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update inventory");
                }

                // Update request status to 'delivered'
                $sql = "UPDATE blood_requests SET status = 'delivered', delivered_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $request_id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update request status");
                }
            } else if ($request_type === 'donor') {
                // For donor requests, add to inventory
                $sql = "INSERT INTO blood_inventory (bloodbank_id, blood_type, units) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE units = units + ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isii", $bloodbank_id, $request['blood_type'], $request['units'], $request['units']);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update inventory");
                }
            }
        }

        // Update request status
        $sql = "UPDATE blood_requests SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $action, $request_id);
        $stmt->execute();

        $conn->commit();
        header("Location: bloodbank_dashboard.php?success=request_updated");
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