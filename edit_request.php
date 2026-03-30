<?php
require_once 'config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital') {
    header('Location: login.php');
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $request_id = isset($_POST['request_id']) ? intval($_POST['request_id']) : 0;
    $bloodbank_id = isset($_POST['bloodbank_id']) ? intval($_POST['bloodbank_id']) : 0;
    $blood_type = isset($_POST['blood_type']) ? $_POST['blood_type'] : '';
    $units = isset($_POST['units']) ? intval($_POST['units']) : 0;
    $urgency = isset($_POST['urgency']) ? $_POST['urgency'] : '';

    // Validate input
    if ($request_id <= 0 || $bloodbank_id <= 0 || empty($blood_type) || $units <= 0 || empty($urgency)) {
        $_SESSION['error'] = "All fields are required";
        header('Location: hospital_dashboard.php');
        exit();
    }

    // Check if request exists and belongs to the hospital
    $check_sql = "SELECT id FROM blood_requests WHERE id = ? AND hospital_id = ? AND status = 'pending'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $request_id, $_SESSION['user_id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        $_SESSION['error'] = "Invalid request";
        header('Location: hospital_dashboard.php');
        exit();
    }

    // Check available units
    $check_units_sql = "SELECT units FROM blood_inventory WHERE bloodbank_id = ? AND blood_type = ?";
    $check_units_stmt = $conn->prepare($check_units_sql);
    $check_units_stmt->bind_param("is", $bloodbank_id, $blood_type);
    $check_units_stmt->execute();
    $available_units = $check_units_stmt->get_result()->fetch_assoc()['units'] ?? 0;

    if ($units > $available_units) {
        $_SESSION['error'] = "Requested units exceed available inventory";
        header('Location: hospital_dashboard.php');
        exit();
    }

    // Update request
    $sql = "UPDATE blood_requests 
            SET bloodbank_id = ?, blood_type = ?, units = ?, urgency = ? 
            WHERE id = ? AND hospital_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isissi", $bloodbank_id, $blood_type, $units, $urgency, $request_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Blood request updated successfully";
    } else {
        $_SESSION['error'] = "Error updating blood request: " . $conn->error;
    }

    header('Location: hospital_dashboard.php');
    exit();
} else {
    header('Location: hospital_dashboard.php');
    exit();
}
?> 