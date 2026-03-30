<?php
session_start();
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header('Location: login.php');
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bloodbank_id = isset($_POST['bloodbank_id']) ? intval($_POST['bloodbank_id']) : 0;
    $appointment_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : '';
    $appointment_time = isset($_POST['appointment_time']) ? $_POST['appointment_time'] : '';

    // Validate input
    if ($bloodbank_id <= 0 || empty($appointment_date) || empty($appointment_time)) {
        $_SESSION['error'] = "All fields are required";
        header('Location: donor_dashboard.php');
        exit();
    }

    // Check if donor is eligible
    $check_sql = "SELECT is_eligible FROM donors WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $_SESSION['user_id']);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $donor = $result->fetch_assoc();

    if (!$donor['is_eligible']) {
        $_SESSION['error'] = "You must be eligible to schedule an appointment. Please complete the eligibility check first.";
        header('Location: donor_dashboard.php');
        exit();
    }

    // Validate date is not in the past
    if (strtotime($appointment_date) < strtotime(date('Y-m-d'))) {
        $_SESSION['error'] = "Appointment date cannot be in the past";
        header('Location: donor_dashboard.php');
        exit();
    }

    // Check if blood bank exists
    $check_sql = "SELECT id FROM blood_banks WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $bloodbank_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows === 0) {
        $_SESSION['error'] = "Invalid blood bank selected";
        header('Location: donor_dashboard.php');
        exit();
    }

    // Insert appointment
    $sql = "INSERT INTO appointments (donor_id, bloodbank_id, date, time, status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $_SESSION['user_id'], $bloodbank_id, $appointment_date, $appointment_time);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Appointment scheduled successfully";
    } else {
        $_SESSION['error'] = "Error scheduling appointment: " . $conn->error;
    }

    header('Location: donor_dashboard.php');
    exit();
} else {
    header('Location: donor_dashboard.php');
    exit();
}
?> 