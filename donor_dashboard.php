<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a donor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: index.php");
    exit();
}

$donor_id = $_SESSION['user_id'];

// Get donor information
$sql = "SELECT id, name, email, blood_type, last_donation_date, age, weight, 
               COALESCE(has_health_condition, 0) as has_health_condition,
               health_condition_details,
               COALESCE(is_eligible, 0) as is_eligible,
               eligibility_checked_at
        FROM donors WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();

// Get eligibility status
$is_eligible = (bool)($donor['is_eligible'] ?? false);
$eligibility_checked_at = $donor['eligibility_checked_at'] ?? null;

// Get recent appointments
$sql = "SELECT a.*, bb.name as bloodbank_name 
        FROM appointments a 
        JOIN blood_banks bb ON a.bloodbank_id = bb.id 
        WHERE a.donor_id = ? 
        ORDER BY a.date DESC, a.time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$appointments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard - BDMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-danger">
        <div class="container">
            <a class="navbar-brand" href="#">BDMS - <?php echo htmlspecialchars($donor['name']); ?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['eligibility_check'])): ?>
            <div class="alert <?php echo $_SESSION['eligibility_check']['eligible'] ? 'alert-success' : 'alert-danger'; ?>">
                <h5>Eligibility Check Result:</h5>
                <?php if ($_SESSION['eligibility_check']['eligible']): ?>
                    <p>You are eligible to donate blood!</p>
                <?php else: ?>
                    <p>You are not eligible to donate blood for the following reasons:</p>
                    <ul>
                        <?php foreach ($_SESSION['eligibility_check']['reasons'] as $reason): ?>
                            <li><?php echo htmlspecialchars($reason); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php unset($_SESSION['eligibility_check']); ?>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Donation Eligibility Checker</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($eligibility_checked_at): ?>
                            <div class="alert alert-info">
                                Last checked: <?php echo date('M d, Y H:i', strtotime($eligibility_checked_at)); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="check_eligibility.php" method="POST">
                            <div class="mb-3">
                                <label for="last_donation" class="form-label">Last Donation Date</label>
                                <input type="date" class="form-control" name="last_donation" id="last_donation" 
                                       value="<?php echo $donor['last_donation_date'] ?? ''; ?>">
                                <small class="text-muted">Leave empty if this is your first donation</small>
                            </div>
                            <div class="mb-3">
                                <label for="age" class="form-label">Age</label>
                                <input type="number" class="form-control" name="age" id="age" 
                                       value="<?php echo $donor['age'] ?? ''; ?>" required min="18" max="60">
                            </div>
                            <div class="mb-3">
                                <label for="weight" class="form-label">Weight (kg)</label>
                                <input type="number" class="form-control" name="weight" id="weight" 
                                       value="<?php echo $donor['weight'] ?? ''; ?>" required min="50" step="0.1">
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="health_condition" 
                                           id="health_condition" <?php echo ($donor['has_health_condition'] ?? false) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="health_condition">
                                        I have a current health condition
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3" id="health_condition_details_div" style="display: none;">
                                <label for="health_condition_details" class="form-label">Health Condition Details</label>
                                <textarea class="form-control" name="health_condition_details" id="health_condition_details" 
                                          rows="3"><?php echo htmlspecialchars($donor['health_condition_details'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Check Eligibility</button>
                        </form>
                    </div>
                </div>

                <?php if ($is_eligible): ?>
                <div class="card">
                    <div class="card-header">
                        <h5>Schedule Appointment</h5>
                    </div>
                    <div class="card-body">
                        <form action="schedule_appointment.php" method="POST">
                            <div class="mb-3">
                                <label for="bloodbank_id" class="form-label">Select Blood Bank</label>
                                <select class="form-select" name="bloodbank_id" required>
                                    <option value="">Select Blood Bank</option>
                                    <?php
                                    $sql = "SELECT id, name, address FROM blood_banks ORDER BY name";
                                    $result = $conn->query($sql);
                                    while($row = $result->fetch_assoc()) {
                                        echo "<option value='".$row['id']."'>".htmlspecialchars($row['name'])." - ".htmlspecialchars($row['address'])."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="appointment_date" class="form-label">Preferred Date</label>
                                <input type="date" class="form-control" name="appointment_date" required 
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="appointment_time" class="form-label">Preferred Time</label>
                                <input type="time" class="form-control" name="appointment_time" required>
                            </div>
                            <button type="submit" class="btn btn-success">Schedule Appointment</button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-warning">
                    <h5>Not Eligible</h5>
                    <p>You need to be eligible to schedule an appointment. Please complete the eligibility check above.</p>
                    <?php if ($eligibility_checked_at): ?>
                        <p class="mb-0">Your last eligibility check was on <?php echo date('M d, Y H:i', strtotime($eligibility_checked_at)); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>My Appointments</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!$is_eligible): ?>
                            <div class="alert alert-warning">
                                <p>You need to be eligible to view and manage appointments. Please complete the eligibility check first.</p>
                            </div>
                        <?php else: ?>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Blood Bank</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($appointments->num_rows > 0) {
                                    while($row = $appointments->fetch_assoc()) {
                                        $status_class = '';
                                        switch($row['status']) {
                                            case 'pending':
                                                $status_class = 'text-warning';
                                                break;
                                            case 'approved':
                                                $status_class = 'text-success';
                                                break;
                                            case 'rejected':
                                                $status_class = 'text-danger';
                                                break;
                                        }
                                        
                                        echo "<tr>";
                                        echo "<td>".htmlspecialchars($row['bloodbank_name'])."</td>";
                                        echo "<td>".date('M d, Y', strtotime($row['date']))."</td>";
                                        echo "<td>".date('h:i A', strtotime($row['time']))."</td>";
                                        echo "<td class='".$status_class."'>".$row['status']."</td>";
                                        echo "<td>";
                                        if ($row['status'] === 'pending') {
                                            echo "<button type='button' class='btn btn-sm btn-primary me-2' 
                                                  onclick='editAppointment(".$row['id'].")' 
                                                  data-bs-toggle='modal' data-bs-target='#editAppointmentModal'>
                                                  <i class='fas fa-edit'></i> Edit
                                                  </button>";
                                            echo "<button type='button' class='btn btn-sm btn-danger' 
                                                  onclick='deleteAppointment(".$row['id'].")'>
                                                  <i class='fas fa-trash'></i> Delete
                                                  </button>";
                                        } elseif ($row['status'] === 'approved') {
                                            echo "<button type='button' class='btn btn-sm btn-primary' 
                                                  onclick='editApprovedAppointment(".$row['id'].")' 
                                                  data-bs-toggle='modal' data-bs-target='#editApprovedAppointmentModal'>
                                                  <i class='fas fa-edit'></i> Edit
                                                  </button>";
                                        }
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>No appointments found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Appointment Modal -->
    <div class="modal fade" id="editAppointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editAppointmentForm" action="edit_appointment.php" method="POST">
                        <input type="hidden" name="appointment_id" id="edit_appointment_id">
                        <div class="mb-3">
                            <label for="edit_bloodbank_id" class="form-label">Select Blood Bank</label>
                            <select class="form-select" name="bloodbank_id" id="edit_bloodbank_id" required>
                                <option value="">Select Blood Bank</option>
                                <?php
                                $sql = "SELECT id, name, address FROM blood_banks ORDER BY name";
                                $result = $conn->query($sql);
                                while($row = $result->fetch_assoc()) {
                                    echo "<option value='".$row['id']."'>".htmlspecialchars($row['name'])." - ".htmlspecialchars($row['address'])."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_appointment_date" class="form-label">Preferred Date</label>
                            <input type="date" class="form-control" name="appointment_date" id="edit_appointment_date" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="edit_appointment_time" class="form-label">Preferred Time</label>
                            <input type="time" class="form-control" name="appointment_time" id="edit_appointment_time" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Approved Appointment Modal -->
    <div class="modal fade" id="editApprovedAppointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Approved Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editApprovedAppointmentForm" action="edit_approved_appointment.php" method="POST">
                        <input type="hidden" name="appointment_id" id="edit_approved_appointment_id">
                        <div class="mb-3">
                            <label for="edit_approved_date" class="form-label">Preferred Date</label>
                            <input type="date" class="form-control" name="appointment_date" id="edit_approved_date" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="edit_approved_time" class="form-label">Preferred Time</label>
                            <input type="time" class="form-control" name="appointment_time" id="edit_approved_time" required>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteAppointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this appointment?</p>
                    <form id="deleteAppointmentForm" action="delete_appointment.php" method="POST">
                        <input type="hidden" name="appointment_id" id="delete_appointment_id">
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
    <script>
    document.getElementById('health_condition').addEventListener('change', function() {
        document.getElementById('health_condition_details_div').style.display = 
            this.checked ? 'block' : 'none';
    });

    // Show health condition details if already checked
    if (document.getElementById('health_condition').checked) {
        document.getElementById('health_condition_details_div').style.display = 'block';
    }

    // Edit appointment function
    function editAppointment(appointmentId) {
        // Fetch appointment details
        fetch('get_appointment.php?id=' + appointmentId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_appointment_id').value = data.id;
                document.getElementById('edit_bloodbank_id').value = data.bloodbank_id;
                document.getElementById('edit_appointment_date').value = data.date;
                document.getElementById('edit_appointment_time').value = data.time;
            })
            .catch(error => console.error('Error:', error));
    }

    // Edit approved appointment function
    function editApprovedAppointment(appointmentId) {
        // Fetch appointment details
        fetch('get_appointment.php?id=' + appointmentId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('edit_approved_appointment_id').value = data.id;
                document.getElementById('edit_approved_date').value = data.date;
                document.getElementById('edit_approved_time').value = data.time;
            })
            .catch(error => console.error('Error:', error));
    }

    // Delete appointment function
    function deleteAppointment(appointmentId) {
        document.getElementById('delete_appointment_id').value = appointmentId;
        new bootstrap.Modal(document.getElementById('deleteAppointmentModal')).show();
    }
    </script>
</body>
</html> 