<?php
require_once 'config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header('Location: login.php');
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;

    // Validate input
    if ($appointment_id <= 0) {
        $_SESSION['error'] = "Invalid appointment ID";
        header('Location: donor_dashboard.php');
        exit();
    }

    // Check if appointment exists and belongs to the donor
    $check_sql = "SELECT id FROM appointments WHERE id = ? AND donor_id = ? AND status = 'pending'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        $_SESSION['error'] = "Invalid appointment";
        header('Location: donor_dashboard.php');
        exit();
    }

    // Delete appointment
    $sql = "DELETE FROM appointments WHERE id = ? AND donor_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Appointment deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting appointment: " . $conn->error;
    }

    header('Location: donor_dashboard.php');
    exit();
} else {
    header('Location: donor_dashboard.php');
    exit();
}
?> 