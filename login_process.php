<?php
session_start();
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Debug information
    error_log("Login attempt - Email: $email, Role: $role");

    // Validate input
    if (empty($email) || empty($password) || empty($role)) {
        header("Location: login.php?error=empty_fields");
        exit();
    }

    // Prepare SQL based on role
    $table = '';
    switch($role) {
        case 'hospital':
            $table = 'hospitals';
            break;
        case 'bloodbank':
            $table = 'blood_banks';
            break;
        case 'donor':
            $table = 'donors';
            break;
        case 'admin':
            $table = 'admins';
            break;
        default:
            header("Location: login.php?error=invalid_role");
            exit();
    }

    // Check credentials
    $sql = "SELECT * FROM $table WHERE email = ?";
    
    // Debug information
    error_log("SQL Query: " . $sql);
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed: " . $conn->error);
        header("Location: login.php?error=system_error");
        exit();
    }
    
    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        header("Location: login.php?error=system_error");
        exit();
    }
    
    $result = $stmt->get_result();
    
    // Debug information
    error_log("Number of users found: " . $result->num_rows);

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Debug information
        error_log("User found: " . print_r($user, true));
        
        // Check if password is hashed or plain text
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
            
            // Debug information
            error_log("Login successful - User ID: " . $user['id'] . ", Role: " . $role);
            
            // Redirect based on role
            switch($role) {
                case 'hospital':
                    header("Location: hospital_dashboard.php");
                    break;
                case 'bloodbank':
                    header("Location: bloodbank_dashboard.php");
                    break;
                case 'donor':
                    header("Location: donor_dashboard.php");
                    break;
                case 'admin':
                    header("Location: admin_dashboard.php");
                    break;
            }
            exit();
        } else {
            error_log("Password verification failed");
            header("Location: login.php?error=invalid_credentials");
            exit();
        }
    } else {
        error_log("No user found with email: " . $email);
        header("Location: login.php?error=invalid_credentials");
        exit();
    }
} else {
    // If not POST request, redirect to login page
    header("Location: login.php");
    exit();
}
?> 