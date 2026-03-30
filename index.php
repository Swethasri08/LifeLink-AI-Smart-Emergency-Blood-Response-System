<?php
session_start();
// Removed auto-redirect to dashboard. Login form will always show.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BDMS - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('images/bdms.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .card {
            background: rgba(255,255,255,0.95);
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Blood Donation Management System</h3>
                    </div>
                    <div class="card-body">
                        <form action="login_process.php" method="POST">
                            <div class="mb-3">
                                <label for="role" class="form-label">Select Role</label>
                                <select class="form-select" name="role" id="role" required>
                                    <option value="">Choose Role</option>
                                    <option value="donor">Donor</option>
                                    <option value="bloodbank">Blood Bank</option>
                                    <option value="hospital">Hospital</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="register.php">Don't have an account? Register here</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 