<?php
// Database configuration for Blood Donation Management System

// Check environment and configure database accordingly
$appEnv = getenv('APP_ENV') ?: 'development';

// For Render.com, environment variables are automatically set
if ($appEnv === 'production') {
    // Render.com environment - use Render database
    $host = getenv('DB_HOST') ?: 'localhost';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $database = getenv('DB_NAME') ?: 'bdms';
    
    // Try to parse DATABASE_URL if available (Render provides this)
    $databaseUrl = getenv('DATABASE_URL');
    if ($databaseUrl) {
        $parsedUrl = parse_url($databaseUrl);
        if ($parsedUrl && isset($parsedUrl['scheme']) && $parsedUrl['scheme'] === 'mysql') {
            $host = $parsedUrl['host'] ?? $host;
            $username = $parsedUrl['user'] ?? $username;
            $password = $parsedUrl['pass'] ?? $password;
            $database = ltrim($parsedUrl['path'], '/') ?? $database;
            
            // Add port if specified
            if (isset($parsedUrl['port']) && $parsedUrl['port'] != '3306') {
                $host .= ':' . $parsedUrl['port'];
            }
        }
    }
} else {
    // Development environment (localhost)
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "bdms";
}

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection and handle errors appropriately
if (!$conn) {
    $error = mysqli_connect_error();
    
    if ($appEnv === 'production') {
        // In production, show a user-friendly error page
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Database Connection - Blood Donation Management System</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        </head>
        <body class="bg-light">
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header bg-danger text-white">
                                <h4 class="mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Database Connection Required
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <h5><i class="fas fa-info-circle me-2"></i>Database Setup Needed</h5>
                                    <p>The Blood Donation Management System requires a database connection to function properly.</p>
                                </div>
                                
                                <h6><i class="fas fa-cog me-2"></i>Current Configuration:</h6>
                                <ul class="list-unstyled mb-3">
                                    <li><strong>Host:</strong> <?php echo htmlspecialchars($host); ?></li>
                                    <li><strong>Database:</strong> <?php echo htmlspecialchars($database); ?></li>
                                    <li><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></li>
                                    <li><strong>Environment:</strong> <?php echo htmlspecialchars($appEnv); ?></li>
                                </ul>
                                
                                <h6><i class="fas fa-tools me-2"></i>To Fix This:</h6>
                                <ol>
                                    <li>Go to your Render dashboard</li>
                                    <li>Create a MySQL database service</li>
                                    <li>Connect it to your web service</li>
                                    <li>Import the database schema from <code>database_complete.sql</code></li>
                                    <li>Restart your web service</li>
                                </ol>
                                
                                <div class="text-center mt-4">
                                    <a href="https://render.com/docs/databases" target="_blank" class="btn btn-primary me-2">
                                        <i class="fas fa-external-link-alt me-2"></i>Render Database Docs
                                    </a>
                                    <a href="https://github.com/Swethasri08/LifeLink-AI-Smart-Emergency-Blood-Response-System" target="_blank" class="btn btn-outline-secondary">
                                        <i class="fab fa-github me-2"></i>View on GitHub
                                    </a>
                                </div>
                            </div>
                            <div class="card-footer text-muted text-center">
                                <small>Blood Donation Management System - Professional Healthcare Application</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    } else {
        // In development, show the actual error
        die("Connection failed: " . $error);
    }
}

// Set character set for proper encoding
mysqli_set_charset($conn, "utf8mb4");

// Optional: Set timezone
date_default_timezone_set('UTC');

?>