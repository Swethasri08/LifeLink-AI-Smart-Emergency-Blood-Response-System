<?php
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Diagnostic Information</h2>";

// Check blood_requests table structure
$sql = "DESCRIBE blood_requests";
$result = $conn->query($sql);

echo "<h3>Table Structure:</h3>";
echo "<pre>";
if ($result) {
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error describing table: " . $conn->error;
}
echo "</pre>";

// Check blood_requests data
$sql = "SELECT * FROM blood_requests";
$result = $conn->query($sql);

echo "<h3>Table Data:</h3>";
echo "<pre>";
if ($result) {
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error querying data: " . $conn->error;
}
echo "</pre>";

// Check if there are any requests for the current hospital
if (isset($_SESSION['user_id'])) {
    $hospital_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM blood_requests WHERE hospital_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $hospital_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<h3>Requests for Current Hospital (ID: $hospital_id):</h3>";
    echo "<pre>";
    if ($result) {
        while($row = $result->fetch_assoc()) {
            print_r($row);
        }
    } else {
        echo "Error querying hospital requests: " . $stmt->error;
    }
    echo "</pre>";
}
?> 