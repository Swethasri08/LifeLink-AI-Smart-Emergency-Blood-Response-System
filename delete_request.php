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

    // Validate input
    if ($request_id <= 0) {
        $_SESSION['error'] = "Invalid request ID";
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

    // Delete request
    $sql = "DELETE FROM blood_requests WHERE id = ? AND hospital_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $request_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Blood request deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting blood request: " . $conn->error;
    }

    header('Location: hospital_dashboard.php');
    exit();
} else {
    header('Location: hospital_dashboard.php');
    exit();
}
?> 