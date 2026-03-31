<?php
// Database configuration for Blood Donation Management System

// Check if running in Docker/Render environment
$isProduction = (getenv('APP_ENV') === 'production') || (php_sapi_name() !== 'cli' && !getenv('APP_ENV'));

if ($isProduction) {
    // Production environment (Docker/Render)
    $host = getenv('DB_HOST') ?: 'localhost';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $database = getenv('DB_NAME') ?: 'bdms';
    
    // For Render, try to parse DATABASE_URL if available
    $dbUrl = getenv('DATABASE_URL');
    if ($dbUrl) {
        $parsedUrl = parse_url($dbUrl);
        if ($parsedUrl) {
            $host = $parsedUrl['host'] ?? $host;
            $username = $parsedUrl['user'] ?? $username;
            $password = $parsedUrl['pass'] ?? $password;
            $database = ltrim($parsedUrl['path'], '/') ?? $database;
        }
    }
} else {
    // Development environment (local)
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "bdms";
}

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    // In production, show user-friendly error
    if ($isProduction) {
        echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Connection</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body>
    <div class='container mt-5'>
        <div class='alert alert-warning'>
            <h4>Database Connection Setup</h4>
            <p>The application is running but needs database configuration.</p>
            <p><strong>Current Settings:</strong></p>
            <ul>
                <li>Host: $host</li>
                <li>Database: $database</li>
                <li>User: $username</li>
            </ul>
            <p>Please configure your database environment variables in Render dashboard.</p>
            <hr>
            <p><em>This is a demo version of the Blood Donation Management System.</em></p>
        </div>
    </div>
</body>
</html>";
        exit;
    } else {
        die("Connection failed: " . mysqli_connect_error());
    }
}

// Set character set
mysqli_set_charset($conn, "utf8mb4");

?>