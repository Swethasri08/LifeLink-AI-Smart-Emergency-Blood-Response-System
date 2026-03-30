<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a blood bank
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bloodbank') {
    header("Location: index.php");
    exit();
}

$bloodbank_id = $_SESSION['user_id'];

// Get blood bank name
$sql = "SELECT name FROM blood_banks WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bloodbank_id);
$stmt->execute();
$result = $stmt->get_result();
$bloodbank = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Requests - BDMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container">
            <a class="navbar-brand" href="#">BDMS - <?php echo htmlspecialchars($bloodbank['name']); ?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="bloodbank_dashboard.php">Dashboard</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php
                switch($_GET['error']) {
                    case 'request_failed':
                        echo "Failed to process request. Please try again.";
                        break;
                    case 'insufficient_units':
                        echo "Insufficient units in inventory.";
                        break;
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php
                switch($_GET['success']) {
                    case 'request_approved':
                        echo "Blood request approved successfully.";
                        break;
                    case 'request_rejected':
                        echo "Blood request rejected.";
                        break;
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5>Pending Blood Requests</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Hospital</th>
                            <th>Blood Type</th>
                            <th>Units</th>
                            <th>Urgency</th>
                            <th>Request Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT br.*, h.name as hospital_name 
                               FROM blood_requests br 
                               JOIN hospitals h ON br.hospital_id = h.id 
                               WHERE br.bloodbank_id = ? AND br.status = 'pending'
                               ORDER BY 
                                   CASE br.urgency 
                                       WHEN 'emergency' THEN 1 
                                       WHEN 'urgent' THEN 2 
                                       WHEN 'normal' THEN 3 
                                   END,
                                   br.created_at ASC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $bloodbank_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        while($row = $result->fetch_assoc()) {
                            $urgency_class = '';
                            switch($row['urgency']) {
                                case 'emergency':
                                    $urgency_class = 'text-danger fw-bold';
                                    break;
                                case 'urgent':
                                    $urgency_class = 'text-warning';
                                    break;
                                case 'normal':
                                    $urgency_class = 'text-success';
                                    break;
                            }
                            
                            echo "<tr>";
                            echo "<td>".htmlspecialchars($row['hospital_name'])."</td>";
                            echo "<td>".$row['blood_type']."</td>";
                            echo "<td>".$row['units']."</td>";
                            echo "<td class='".$urgency_class."'>".$row['urgency']."</td>";
                            echo "<td>".$row['created_at']."</td>";
                            echo "<td>
                                    <form action='process_request.php' method='POST' class='d-inline'>
                                        <input type='hidden' name='request_id' value='".$row['id']."'>
                                        <input type='hidden' name='action' value='approve'>
                                        <button type='submit' class='btn btn-success btn-sm'>Approve</button>
                                    </form>
                                    <form action='process_request.php' method='POST' class='d-inline'>
                                        <input type='hidden' name='request_id' value='".$row['id']."'>
                                        <input type='hidden' name='action' value='reject'>
                                        <button type='submit' class='btn btn-danger btn-sm'>Reject</button>
                                    </form>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5>Request History</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hospital</th>
                            <th>Blood Type</th>
                            <th>Units</th>
                            <th>Status</th>
                            <th>Request Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT br.*, h.name as hospital_name 
                               FROM blood_requests br 
                               JOIN hospitals h ON br.hospital_id = h.id 
                               WHERE br.bloodbank_id = ? AND br.status != 'pending'
                               ORDER BY br.created_at DESC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $bloodbank_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        while($row = $result->fetch_assoc()) {
                            $status_class = '';
                            switch($row['status']) {
                                case 'approved':
                                    $status_class = 'text-success';
                                    break;
                                case 'rejected':
                                    $status_class = 'text-danger';
                                    break;
                            }
                            
                            echo "<tr>";
                            echo "<td>".htmlspecialchars($row['hospital_name'])."</td>";
                            echo "<td>".$row['blood_type']."</td>";
                            echo "<td>".$row['units']."</td>";
                            echo "<td class='".$status_class."'>".$row['status']."</td>";
                            echo "<td>".$row['created_at']."</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 