<?php
session_start();
require_once 'config/database.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    switch($_SESSION['role']) {
        case 'hospital':
            header("Location: hospital_dashboard.php");
            break;
        case 'bloodbank':
            header("Location: bloodbank_dashboard.php");
            break;
        case 'donor':
            header("Location: donor_dashboard.php");
            break;
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Blood Donation Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #dc3545;
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        .btn-primary {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-primary:hover {
            background-color: #bb2d3b;
            border-color: #bb2d3b;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">BDMS Login</h3>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger">
                            <?php
                            switch($_GET['error']) {
                                case 'invalid_credentials':
                                    echo "Invalid email or password";
                                    break;
                                case 'empty_fields':
                                    echo "Please fill in all fields";
                                    break;
                                case 'not_authorized':
                                    echo "You are not authorized to access this system";
                                    break;
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <form action="login_process.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Login As</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="hospital">Hospital</option>
                                <option value="bloodbank">Blood Bank</option>
                                <option value="donor">Donor</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                </div>
            </div>
            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 