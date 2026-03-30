<?php
// Database configuration for Blood Donation Management System

// Development environment (local)
if (getenv('APP_ENV') === 'development' || !getenv('DATABASE_URL')) {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "bdms";
} 
// Production environment (Vercel)
else {
    // Parse Vercel database URL
    $dbUrl = getenv('DATABASE_URL');
    
    if ($dbUrl) {
        // Parse DATABASE_URL: mysql://username:password@host:port/database
        $parsedUrl = parse_url($dbUrl);
        
        $host = $parsedUrl['host'] ?? 'localhost';
        $username = $parsedUrl['user'] ?? '';
        $password = $parsedUrl['pass'] ?? '';
        $database = ltrim($parsedUrl['path'], '/') ?? 'bdms';
        $port = $parsedUrl['port'] ?? '3306';
        
        // Add port to host if specified
        if ($port && $port !== '3306') {
            $host .= ':' . $port;
        }
    } else {
        // Fallback for Vercel
        $host = getenv('DB_HOST') ?? 'localhost';
        $username = getenv('DB_USERNAME') ?? 'root';
        $password = getenv('DB_PASSWORD') ?? '';
        $database = getenv('DB_NAME') ?? 'bdms';
    }
}

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    // In production, don't expose detailed error
    if (getenv('APP_ENV') === 'production') {
        die("Database connection failed. Please check configuration.");
    } else {
        die("Connection failed: " . mysqli_connect_error());
    }
}

// Set character set
mysqli_set_charset($conn, "utf8mb4");

?>