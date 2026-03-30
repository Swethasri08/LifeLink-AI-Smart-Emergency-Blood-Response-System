<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a blood bank
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bloodbank') {
    $_SESSION['error_message'] = "Not authorized to access this page.";
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bloodbank_id = $_SESSION['user_id'];
    $blood_type = trim($_POST['blood_type']);
    $units = (int)$_POST['units'];
    $collected_at = date('Y-m-d'); // Set collected date to today
    // Calculate expiry date (e.g., 42 days for red blood cells)
    // You might need to adjust this based on the specific blood product type.
    $expiry_date = date('Y-m-d', strtotime($collected_at . ' +42 days'));

    // Validate input
    if (empty($blood_type) || $units < 1) {
        $_SESSION['error_message'] = "Invalid blood type or units.";
        header("Location: bloodbank_dashboard.php");
        exit();
    }

    // Insert new batch into inventory
    $sql = "INSERT INTO blood_inventory (bloodbank_id, blood_type, units, collected_at, expiry_date)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    // Use 's' for blood_type as it's a string
    $stmt->bind_param("isiss", $bloodbank_id, $blood_type, $units, $collected_at, $expiry_date);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Inventory updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating inventory: " . $conn->error;
    }

    header("Location: bloodbank_dashboard.php");
    exit();

} else {
    $_SESSION['error_message'] = "Invalid request method.";
    header("Location: bloodbank_dashboard.php");
    exit();
}
?>