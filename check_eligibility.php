<?php
session_start();
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php?error=not_authorized");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donor_id = $_SESSION['user_id'];
    $last_donation = $_POST['last_donation'];
    $age = (int)$_POST['age'];
    $weight = (float)$_POST['weight'];
    $has_health_condition = isset($_POST['health_condition']) ? true : false;
    $health_condition_details = $_POST['health_condition_details'] ?? '';

    $eligibility = [
        'eligible' => true,
        'reasons' => []
    ];

    // Check last donation date (must be ≥ 3 months ago)
    if (!empty($last_donation)) {
        $last_donation_date = new DateTime($last_donation);
        $three_months_ago = new DateTime();
        $three_months_ago->modify('-3 months');
        
        if ($last_donation_date > $three_months_ago) {
            $eligibility['eligible'] = false;
            $eligibility['reasons'][] = "Last donation was less than 3 months ago";
        }
    }

    // Check age (must be between 18-60)
    if ($age < 18 || $age > 60) {
        $eligibility['eligible'] = false;
        $eligibility['reasons'][] = "Age must be between 18 and 60 years";
    }

    // Check weight (must be ≥ 50 kg)
    if ($weight < 50) {
        $eligibility['eligible'] = false;
        $eligibility['reasons'][] = "Weight must be at least 50 kg";
    }

    // Check health conditions
    if ($has_health_condition) {
        $eligibility['eligible'] = false;
        $eligibility['reasons'][] = "Current health condition: " . $health_condition_details;
    }

    // Update donor's eligibility status in database
    $sql = "UPDATE donors SET 
            last_donation_date = ?,
            age = ?,
            weight = ?,
            has_health_condition = ?,
            health_condition_details = ?,
            is_eligible = ?,
            eligibility_checked_at = NOW()
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $is_eligible = $eligibility['eligible'] ? 1 : 0;
    $stmt->bind_param("sidissi", 
        $last_donation, 
        $age, 
        $weight, 
        $has_health_condition, 
        $health_condition_details, 
        $is_eligible,
        $donor_id
    );
    $stmt->execute();

    // Store eligibility result in session
    $_SESSION['eligibility_check'] = $eligibility;

    header("Location: donor_dashboard.php");
    exit();
} else {
    header("Location: donor_dashboard.php");
    exit();
}
?> 