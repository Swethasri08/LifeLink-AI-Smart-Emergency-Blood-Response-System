<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donor_id = $_SESSION['user_id'];
    $bloodbank_id = $_POST['bloodbank_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Validate input
    if (empty($bloodbank_id) || empty($date) || empty($time)) {
        header("Location: donor_dashboard.php?error=empty_fields");
        exit();
    }

    // Check if date is in the future
    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        header("Location: donor_dashboard.php?error=invalid_date");
        exit();
    }

    // Insert appointment request
    $sql = "INSERT INTO appointments (donor_id, bloodbank_id, date, time) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $donor_id, $bloodbank_id, $date, $time);
    
    if ($stmt->execute()) {
        header("Location: donor_dashboard.php?success=appointment_requested");
    } else {
        header("Location: donor_dashboard.php?error=request_failed");
    }
    exit();
} else {
    header("Location: donor_dashboard.php");
    exit();
}
?> 