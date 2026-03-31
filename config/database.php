<?php
// Database configuration for Blood Donation Management System

// For Render.com, DATABASE_URL is automatically provided
$databaseUrl = getenv('DATABASE_URL');

if ($databaseUrl) {
    // Parse DATABASE_URL: mysql://username:password@host:port/database
    $parsedUrl = parse_url($databaseUrl);
    
    if ($parsedUrl && isset($parsedUrl['scheme']) && $parsedUrl['scheme'] === 'mysql') {
        $host = $parsedUrl['host'] ?? 'localhost';
        $username = $parsedUrl['user'] ?? 'root';
        $password = $parsedUrl['pass'] ?? '';
        $database = ltrim($parsedUrl['path'], '/') ?? 'bdms';
        
        // Add port if specified
        if (isset($parsedUrl['port']) && $parsedUrl['port'] != '3306') {
            $host .= ':' . $parsedUrl['port'];
        }
    } else {
        // Fallback if DATABASE_URL is malformed
        $host = getenv('DB_HOST') ?: 'localhost';
        $username = getenv('DB_USERNAME') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';
        $database = getenv('DB_NAME') ?: 'bdms';
    }
} else {
    // No DATABASE_URL - use individual environment variables
    $host = getenv('DB_HOST') ?: 'localhost';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    $database = getenv('DB_NAME') ?: 'bdms';
}

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    $error = mysqli_connect_error();
    
    // Show user-friendly error page
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
                        <div class="card-header bg-warning text-dark">
                            <h4 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Database Connection Required
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle me-2"></i>Database Setup Needed</h5>
                                <p>Your Blood Donation Management System needs a database connection to function properly.</p>
                            </div>
                            
                            <h6><i class="fas fa-cog me-2"></i>Current Configuration:</h6>
                            <ul class="list-unstyled mb-3">
                                <li><strong>Host:</strong> <?php echo htmlspecialchars($host); ?></li>
                                <li><strong>Database:</strong> <?php echo htmlspecialchars($database); ?></li>
                                <li><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></li>
                                <li><strong>DATABASE_URL:</strong> <?php echo $databaseUrl ? 'Set' : 'Not set'; ?></li>
                            </ul>
                            
                            <h6><i class="fas fa-tools me-2"></i>To Fix This:</h6>
                            <ol>
                                <li>Create a MySQL database on Render</li>
                                <li>Connect it to your web service</li>
                                <li>Import the database schema</li>
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
}

// Set character set for proper encoding
mysqli_set_charset($conn, "utf8mb4");

?>