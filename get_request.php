<?php
require_once 'config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get request ID
$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($request_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request ID']);
    exit();
}

// Get request details
$sql = "SELECT id, bloodbank_id, blood_type, units, urgency 
        FROM blood_requests 
        WHERE id = ? AND hospital_id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $request_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Request not found']);
    exit();
}

$request = $result->fetch_assoc();
echo json_encode($request);
?> 