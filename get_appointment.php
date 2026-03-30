<?php
require_once 'config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get appointment ID
$appointment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($appointment_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid appointment ID']);
    exit();
}

// Get appointment details
$sql = "SELECT id, bloodbank_id, date, time 
        FROM appointments 
        WHERE id = ? AND donor_id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $appointment_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Appointment not found']);
    exit();
}

$appointment = $result->fetch_assoc();
echo json_encode($appointment);
?> 