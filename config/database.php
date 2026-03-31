<?php
// Database configuration for Blood Donation Management System

// For Render.com, DATABASE_URL is automatically provided
$databaseUrl = getenv('DATABASE_URL');

if ($databaseUrl) {
    // Parse DATABASE_URL: postgres://username:password@host:port/database
    $parsedUrl = parse_url($databaseUrl);
    
    if ($parsedUrl && isset($parsedUrl['scheme'])) {
        $scheme = $parsedUrl['scheme'];
        $host = $parsedUrl['host'] ?? 'localhost';
        $username = $parsedUrl['user'] ?? 'postgres';
        $password = $parsedUrl['pass'] ?? '';
        $database = ltrim($parsedUrl['path'], '/') ?? 'bdms';
        
        // Add port if specified
        if (isset($parsedUrl['port']) && $parsedUrl['port'] != '5432') {
            $host .= ':' . $parsedUrl['port'];
        }
        
        // Use PostgreSQL connection
        if ($scheme === 'postgres' || $scheme === 'postgresql') {
            try {
                $conn = pg_connect("host=$host dbname=$database user=$username password=$password");
                if (!$conn) {
                    throw new Exception("PostgreSQL connection failed");
                }
            } catch (Exception $e) {
                // Fallback to MySQL if PostgreSQL fails
                $conn = mysqli_connect($host, $username, $password, $database);
            }
        } else {
            // Fallback for MySQL
            $conn = mysqli_connect($host, $username, $password, $database);
        }
    } else {
        // Fallback if DATABASE_URL is malformed
        $host = getenv('DB_HOST') ?: 'localhost';
        $username = getenv('DB_USERNAME') ?: 'postgres';
        $password = getenv('DB_PASSWORD') ?: '';
        $database = getenv('DB_NAME') ?: 'bdms';
        
        // Try PostgreSQL first, then MySQL
        try {
            $conn = pg_connect("host=$host dbname=$database user=$username password=$password");
            if (!$conn) {
                throw new Exception("PostgreSQL connection failed");
            }
        } catch (Exception $e) {
            $conn = mysqli_connect($host, $username, $password, $database);
        }
    }
} else {
    // No DATABASE_URL - use individual environment variables
    $host = getenv('DB_HOST') ?: 'localhost';
    $username = getenv('DB_USERNAME') ?: 'postgres';
    $password = getenv('DB_PASSWORD') ?: '';
    $database = getenv('DB_NAME') ?: 'bdms';
    
    // Try PostgreSQL first, then MySQL
    try {
        $conn = pg_connect("host=$host dbname=$database user=$username password=$password");
        if (!$conn) {
            throw new Exception("PostgreSQL connection failed");
        }
    } catch (Exception $e) {
        $conn = mysqli_connect($host, $username, $password, $database);
    }
}

// Check if tables exist - if not, redirect to installation
if ($conn) {
    $is_postgresql = is_resource($conn) && get_resource_type($conn) === 'pgsql link';
    
    if ($is_postgresql) {
        // Check if admins table exists in PostgreSQL
        $result = pg_query($conn, "SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'admins')");
        $row = pg_fetch_row($result);
        $tables_exist = $row[0] == 't';
    } else {
        // Check if admins table exists in MySQL
        $result = mysqli_query($conn, "SHOW TABLES LIKE 'admins'");
        $tables_exist = mysqli_num_rows($result) > 0;
    }
    
    // If tables don't exist, redirect to installation
    if (!$tables_exist) {
        header('Location: install_database.php');
        exit;
    }
}

// Check connection
if (!$conn) {
    $error = pg_last_error() ?: mysqli_connect_error();
    
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
                            
                            <h6><i class="fas fa-tools me-2"></i>Quick Setup with Render PostgreSQL:</h6>
                            <ol>
                                <li>Create the PostgreSQL database on Render (Free plan)</li>
                                <li>Wait for database to be ready (2-3 minutes)</li>
                                <li>Visit install_database.php to auto-install schema</li>
                                <li>Restart your web service</li>
                            </ol>
                            
                            <div class="text-center mt-4">
                                <a href="install_database.php" class="btn btn-primary me-2">
                                    <i class="fas fa-database me-2"></i>Install Database
                                </a>
                                <a href="https://render.com/docs/databases" target="_blank" class="btn btn-outline-secondary">
                                    <i class="fas fa-external-link-alt me-2"></i>Render Database Docs
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
if (is_resource($conn) && get_resource_type($conn) === 'pgsql link') {
    // PostgreSQL connection
    pg_query($conn, "SET NAMES 'utf8mb4'");
} else {
    // MySQL connection
    mysqli_set_charset($conn, "utf8mb4");
}

?>