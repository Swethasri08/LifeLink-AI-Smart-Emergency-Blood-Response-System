<?php
session_start();
require_once 'config/database.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is a blood bank
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'bloodbank') {
    header("Location: login.php?error=not_authorized");
    exit();
}

// Get blood bank details
$bloodbank_id = $_SESSION['user_id'];

$sql = "SELECT name FROM blood_banks WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bloodbank_id);
$stmt->execute();
$result = $stmt->get_result();
$bloodbank = $result->fetch_assoc();
$bloodbank_name = $bloodbank['name'] ?? 'Blood Bank'; // Use fetched name, default if not found

// Debug information
error_log("Current Blood Bank ID: " . $bloodbank_id);

// Remove expired blood before fetching inventory
$conn->query("DELETE FROM blood_inventory WHERE expiry_date < CURDATE()");

// Get current inventory (summing units for non-expired blood by type)
$inventory_sql = "SELECT blood_type, SUM(units) as total_units
                  FROM blood_inventory
                  WHERE bloodbank_id = ? AND expiry_date >= CURDATE()
                  GROUP BY blood_type
                  ORDER BY blood_type";
$inventory_stmt = $conn->prepare($inventory_sql);
$inventory_stmt->bind_param("i", $bloodbank_id);
$inventory_stmt->execute();
$current_inventory = $inventory_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Debug information
error_log("Inventory: " . print_r($current_inventory, true));

// Get pending hospital requests
$hospital_requests_sql = "SELECT br.*, h.name as hospital_name
                         FROM blood_requests br
                         JOIN hospitals h ON br.hospital_id = h.id
                         WHERE br.bloodbank_id = ? AND br.status = 'pending'
                         ORDER BY FIELD(br.urgency, 'emergency', 'urgent', 'normal'), br.created_at ASC"; // Emergency first, then urgent, then normal
$hospital_requests_stmt = $conn->prepare($hospital_requests_sql);
$hospital_requests_stmt->bind_param("i", $bloodbank_id);
$hospital_requests_stmt->execute();
$pending_hospital_requests = $hospital_requests_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Debug information
error_log("Number of requests found: " . $result->num_rows);

$hospital_requests = $result->fetch_all(MYSQLI_ASSOC);

// Debug information
error_log("Requests data: " . print_r($hospital_requests, true));

// Get pending donor appointments
$donor_appointments_sql = "SELECT a.*, d.name as donor_name, d.blood_type
                          FROM appointments a
                          JOIN donors d ON a.donor_id = d.id
                          WHERE a.bloodbank_id = ? AND a.status = 'pending'
                          ORDER BY a.date ASC, a.time ASC";
$donor_appointments_stmt = $conn->prepare($donor_appointments_sql);
$donor_appointments_stmt->bind_param("i", $bloodbank_id);
$donor_appointments_stmt->execute();
$pending_donor_appointments = $donor_appointments_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Debug information
error_log("Donor appointments: " . print_r($pending_donor_appointments, true));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($bloodbank_name); ?> Dashboard - BDMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-header {
            background-color: #dc3545;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: none;
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
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
        }
        .status-pending {
            background-color: #ffc107;
            color: #000;
        }
        .status-approved {
            background-color: #28a745;
            color: #fff;
        }
        .status-rejected {
            background-color: #dc3545;
            color: #fff;
        }
        .urgency-high {
            color: #dc3545;
            font-weight: bold;
        }
        .urgency-medium {
            color: #ffc107;
        }
        .urgency-low {
            color: #28a745;
        }
        .nav-tabs .nav-link {
            color: #495057;
        }
        .nav-tabs .nav-link.active {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Blood Bank Dashboard - <?php echo htmlspecialchars($bloodbank_name); ?></h1>
                <div>
                    <a href="bloodbank_report.php" class="btn btn-light me-2">View Report</a>
                    <a href="logout.php" class="btn btn-light">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Add Blood Inventory</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                <?php
                                switch($_GET['error']) {
                                    case 'invalid_input':
                                        echo "Please enter valid blood type and units";
                                        break;
                                    case 'update_failed':
                                        echo "Failed to update inventory. Please try again.";
                                        break;
                                    case 'insufficient_inventory':
                                        echo "Insufficient blood units available";
                                        break;
                                    default:
                                        echo htmlspecialchars($_GET['error']);
                                }
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">
                                <?php
                                switch($_GET['success']) {
                                    case 'inventory_updated':
                                        echo "Inventory updated successfully!";
                                        break;
                                    case 'request_updated':
                                        echo "Request updated successfully!";
                                        break;
                                    case 'appointment_updated':
                                        echo "Appointment updated successfully!";
                                        break;
                                }
                                ?>
                            </div>
                        <?php endif; ?>

                        <form action="add_inventory.php" method="POST">
                            <div class="mb-3">
                                <label for="blood_type" class="form-label">Blood Type</label>
                                <select class="form-select" id="blood_type" name="blood_type" required>
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
                            <div class="mb-3">
                                <label for="units" class="form-label">Number of Units (from a single collection)</label>
                                <input type="number" class="form-control" id="units" name="units" min="1" required value="1"> <!-- Units per collection is often 1 -->
                            </div>
                            <!-- collected_at and expiry_date will be set by the backend -->
                            <button type="submit" class="btn btn-primary">Add to Inventory</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Current Inventory (Non-Expired)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Blood Type</th>
                                        <th>Available Units</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($current_inventory)): ?>
                                        <tr><td colspan="2" class="text-center">No non-expired inventory found</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($current_inventory as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['blood_type']); ?></td>
                                            <td><?php echo htmlspecialchars($item['total_units']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#hospital-requests">
                                    Hospital Requests
                                    <?php if (!empty($pending_hospital_requests)): ?>
                                        <span class="badge bg-danger rounded-pill"><?php echo count($pending_hospital_requests); ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#donor-appointments">
                                    Donor Appointments
                                    <?php if (!empty($pending_donor_appointments)): ?>
                                        <span class="badge bg-danger rounded-pill"><?php echo count($pending_donor_appointments); ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Hospital Requests Tab -->
                            <div class="tab-pane fade show active" id="hospital-requests">
                                <?php if (empty($pending_hospital_requests)): ?>
                                    <p class="text-muted">No pending hospital requests</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Hospital</th>
                                                    <th>Blood Type</th>
                                                    <th>Units</th>
                                                    <th>Urgency</th>
                                                    <th>Request Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pending_hospital_requests as $request): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($request['hospital_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($request['blood_type']); ?></td>
                                                    <td><?php echo htmlspecialchars($request['units']); ?></td>
                                                    <td>
                                                        <span class="urgency-<?php echo strtolower($request['urgency']); ?>">
                                                            <?php echo htmlspecialchars($request['urgency']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y H:i', strtotime($request['created_at'])); ?></td>
                                                    <td>
                                                        <!-- Approve button triggers fulfillment -->
                                                        <form action="process_request.php" method="POST" class="d-inline">
                                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                            <input type="hidden" name="action" value="approve">
                                                            <input type="hidden" name="bloodbank_id" value="<?php echo $bloodbank_id; ?>">
                                                            <input type="hidden" name="blood_type" value="<?php echo htmlspecialchars($request['blood_type']); ?>">
                                                            <input type="hidden" name="units" value="<?php echo htmlspecialchars($request['units']); ?>">
                                                            <button type="submit" class="btn btn-success btn-sm me-1">Approve & Deliver</button>
                                                        </form>
                                                        <form action="process_request.php" method="POST" class="d-inline">
                                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <input type="hidden" name="bloodbank_id" value="<?php echo $bloodbank_id; ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Donor Appointments Tab -->
                            <div class="tab-pane fade" id="donor-appointments">
                                <?php if (empty($pending_donor_appointments)): ?>
                                    <p class="text-muted">No pending donor appointments</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Donor</th>
                                                    <th>Blood Type</th>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pending_donor_appointments as $appointment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($appointment['donor_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($appointment['blood_type']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($appointment['date'])); ?></td>
                                                    <td><?php echo date('h:i A', strtotime($appointment['time'])); ?></td>
                                                    <td>
                                                        <!-- Approve button triggers adding unit to inventory -->
                                                        <form action="process_appointment.php" method="POST" class="d-inline">
                                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                            <input type="hidden" name="action" value="approve">
                                                            <input type="hidden" name="bloodbank_id" value="<?php echo $bloodbank_id; ?>">
                                                            <input type="hidden" name="blood_type" value="<?php echo htmlspecialchars($appointment['blood_type']); ?>">
                                                            <!-- Assuming 1 unit per donation appointment -->
                                                            <input type="hidden" name="units" value="1">
                                                            <button type="submit" class="btn btn-success btn-sm me-1">Approve & Add Unit</button>
                                                        </form>
                                                        <form action="process_appointment.php" method="POST" class="d-inline">
                                                            <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                            <input type="hidden" name="action" value="reject">
                                                            <input type="hidden" name="bloodbank_id" value="<?php echo $bloodbank_id; ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 