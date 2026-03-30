<?php
session_start();
require_once 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $blood_type = isset($_POST['blood_type']) ? $_POST['blood_type'] : null;

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required";
    } else {
        // Check if email already exists
        $table = '';
        switch($role) {
            case 'donor':
                $table = 'donors';
                break;
            case 'bloodbank':
                $table = 'blood_banks';
                break;
            case 'hospital':
                $table = 'hospitals';
                break;
            default:
                $error = "Invalid role selected";
                break;
        }

        if (!empty($table)) {
            $sql = "SELECT id FROM $table WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error = "Email already exists";
            } else {
                // Insert new user
                $sql = "INSERT INTO $table (name, email, password, phone, address" . 
                       ($role == 'donor' ? ", blood_type" : "") . 
                       ") VALUES (?, ?, ?, ?, ?" . 
                       ($role == 'donor' ? ", ?" : "") . ")";
                
                $stmt = $conn->prepare($sql);
                if ($role == 'donor') {
                    $stmt->bind_param("ssssss", $name, $email, $password, $phone, $address, $blood_type);
                } else {
                    $stmt->bind_param("sssss", $name, $email, $password, $phone, $address);
                }
                
                if ($stmt->execute()) {
                    header("Location: index.php?success=registration");
                    exit();
                } else {
                    $error = "Registration failed";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BDMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Register</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="role" class="form-label">Select Role</label>
                                <select class="form-select" name="role" id="role" required onchange="toggleBloodType()">
                                    <option value="">Choose Role</option>
                                    <option value="donor">Donor</option>
                                    <option value="bloodbank">Blood Bank</option>
                                    <option value="hospital">Hospital</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" required>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                            </div>
                            <div class="mb-3" id="bloodTypeDiv" style="display: none;">
                                <label for="blood_type" class="form-label">Blood Type</label>
                                <select class="form-select" name="blood_type" id="blood_type">
                                    <option value="">Select Blood Type</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Register</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <a href="index.php">Already have an account? Login here</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleBloodType() {
            var role = document.getElementById('role').value;
            var bloodTypeDiv = document.getElementById('bloodTypeDiv');
            var bloodType = document.getElementById('blood_type');
            
            if (role === 'donor') {
                bloodTypeDiv.style.display = 'block';
                bloodType.required = true;
            } else {
                bloodTypeDiv.style.display = 'none';
                bloodType.required = false;
            }
        }
    </script>
</body>
</html> 