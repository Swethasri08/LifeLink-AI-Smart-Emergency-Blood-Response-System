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
    $bloodbank_id = isset($_POST['bloodbank_id']) ? intval($_POST['bloodbank_id']) : 0;
    $appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : '';
    $appointment_time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : '';

    // Validate input
    if ($appointment_id <= 0 || $bloodbank_id <= 0 || empty($appointment_date) || empty($appointment_time)) {
        $_SESSION['error'] = "All fields are required";
        header('Location: donor_dashboard.php');
        exit();
    }

    // Validate date is not in the past
    if (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
        $_SESSION['error'] = "Appointment date cannot be in the past";
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

    // Update appointment
    $sql = "UPDATE appointments 
            SET bloodbank_id = ?, date = ?, time = ? 
            WHERE id = ? AND donor_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issii", $bloodbank_id, $appointment_date, $appointment_time, $appointment_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Appointment updated successfully";
    } else {
        $_SESSION['error'] = "Error updating appointment: " . $conn->error;
    }

    header('Location: donor_dashboard.php');
    exit();
} else {
    header('Location: donor_dashboard.php');
    exit();
}
?> 