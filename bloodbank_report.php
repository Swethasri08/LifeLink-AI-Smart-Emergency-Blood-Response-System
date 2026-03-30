<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a blood bank
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bloodbank') {
    header("Location: login.php");
    exit();
}

$bloodbank_id = $_SESSION['user_id'];

// Get blood bank information
$sql = "SELECT name FROM blood_banks WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bloodbank_id);
$stmt->execute();
$result = $stmt->get_result();
$bloodbank = $result->fetch_assoc();

// Get total blood units by blood type
$sql = "SELECT blood_type, SUM(units) as total_units 
        FROM blood_inventory 
        WHERE bloodbank_id = ? 
        GROUP BY blood_type";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bloodbank_id);
$stmt->execute();
$inventory_result = $stmt->get_result();

$blood_types = [];
$units = [];
while ($row = $inventory_result->fetch_assoc()) {
    $blood_types[] = $row['blood_type'];
    $units[] = $row['total_units'];
}

// Get total requests received
$sql = "SELECT COUNT(*) as total_requests FROM blood_requests WHERE bloodbank_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bloodbank_id);
$stmt->execute();
$total_requests = $stmt->get_result()->fetch_assoc()['total_requests'];

// Get total appointments
$sql = "SELECT COUNT(*) as total_appointments FROM appointments WHERE bloodbank_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bloodbank_id);
$stmt->execute();
$total_appointments = $stmt->get_result()->fetch_assoc()['total_appointments'];

// Get recent requests
$sql = "SELECT br.*, h.name as hospital_name 
        FROM blood_requests br 
        JOIN hospitals h ON br.hospital_id = h.id 
        WHERE br.bloodbank_id = ? 
        ORDER BY br.created_at DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bloodbank_id);
$stmt->execute();
$recent_requests = $stmt->get_result();

// Get recent appointments
$sql = "SELECT a.*, d.name as donor_name 
        FROM appointments a 
        JOIN donors d ON a.donor_id = d.id 
        WHERE a.bloodbank_id = ? 
        ORDER BY a.date DESC, a.time DESC 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bloodbank_id);
$stmt->execute();
$recent_appointments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank Report - BDMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card {
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .stat-card {
            text-align: center;
            padding: 20px;
        }
        .stat-card h3 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #dc3545;
        }
        .stat-card p {
            color: #6c757d;
            margin: 0;
        }
    </style>
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
        <h2 class="mb-4">Blood Bank Report</h2>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stat-card">
                    <h3><?php echo $total_requests; ?></h3>
                    <p>Total Blood Requests</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <h3><?php echo $total_appointments; ?></h3>
                    <p>Total Appointments</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card">
                    <h3><?php echo array_sum($units); ?></h3>
                    <p>Total Blood Units</p>
                </div>
            </div>
        </div>

        <!-- Blood Inventory Chart -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Blood Inventory by Type</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="inventoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Appointments -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Appointments</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Donor Name</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_appointments->num_rows > 0): ?>
                                        <?php while ($appointment = $recent_appointments->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($appointment['donor_name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($appointment['date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($appointment['time'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $appointment['status'] === 'approved' ? 'success' : 
                                                            ($appointment['status'] === 'pending' ? 'warning' : 'danger'); 
                                                    ?>">
                                                        <?php echo ucfirst($appointment['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No recent appointments</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
    <script>
        // Initialize the blood inventory chart
        const ctx = document.getElementById('inventoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($blood_types); ?>,
                datasets: [{
                    label: 'Blood Units Available',
                    data: <?php echo json_encode($units); ?>,
                    backgroundColor: [
                        '#dc3545',
                        '#0d6efd',
                        '#198754',
                        '#ffc107',
                        '#0dcaf0',
                        '#6610f2',
                        '#fd7e14',
                        '#20c997'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Units'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Blood Type'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Current Blood Inventory'
                    }
                }
            }
        });

        function downloadReport() {
            window.location.href = 'download_report.php';
        }
    </script>
</body>
</html> 