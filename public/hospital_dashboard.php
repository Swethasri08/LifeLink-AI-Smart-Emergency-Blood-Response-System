<?php
session_start();
require_once 'config/database.php';

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is a hospital
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'hospital') {
    header("Location: index.php");
    exit();
}

$hospital_id = $_SESSION['user_id'];

// Get hospital name
$sql = "SELECT name FROM hospitals WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $hospital_id);
$stmt->execute();
$result = $stmt->get_result();
$hospital = $result->fetch_assoc();
$hospital_name = $hospital['name'] ?? 'Hospital';

// --- Fetch Inventory for Specific Blood Banks ---
// Assuming Central Blood Center has ID 1 and City Blood Bank has ID 2
// You might need to adjust these IDs based on your `blood_banks` table

$central_blood_bank_id = 1;
$city_blood_bank_id = 2;

// Function to fetch inventory for a given blood bank ID
function fetchBloodBankInventory($conn, $bloodbank_id) {
    try {
        // Remove expired blood before fetching inventory
        $conn->query("DELETE FROM blood_inventory WHERE expiry_date < CURDATE()");
    } catch (mysqli_sql_exception $e) {
        // Log the error but continue
        error_log("Error deleting expired blood: " . $e->getMessage());
    }

    $sql = "SELECT blood_type, COALESCE(SUM(units), 0) as total_units
            FROM blood_inventory
            WHERE bloodbank_id = ? AND expiry_date >= CURDATE()
            GROUP BY blood_type
            ORDER BY FIELD(blood_type, 'A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-')";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Prepare failed for bloodbank ID " . $bloodbank_id . ": " . $conn->error);
        return false; // Indicate failure
    }

    $stmt->bind_param("i", $bloodbank_id);
    if (!$stmt->execute()) {
        error_log("Execute failed for bloodbank ID " . $bloodbank_id . ": " . $stmt->error);
         return false; // Indicate failure
    }

    $result = $stmt->get_result();
    $inventory_data = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    return $inventory_data;
}

$central_inventory = fetchBloodBankInventory($conn, $central_blood_bank_id);
$city_inventory = fetchBloodBankInventory($conn, $city_blood_bank_id);

// Get blood bank names for display
$blood_bank_names = [];
$names_sql = "SELECT id, name FROM blood_banks WHERE id IN (?, ?)";
$names_stmt = $conn->prepare($names_sql);

if ($names_stmt) {
    $names_stmt->bind_param("ii", $central_blood_bank_id, $city_blood_bank_id);
    $names_stmt->execute();
    $names_result = $names_stmt->get_result();
    while($row = $names_result->fetch_assoc()) {
        $blood_bank_names[$row['id']] = $row['name'];
    }
    $names_stmt->close();
}

$central_bank_name = $blood_bank_names[$central_blood_bank_id] ?? 'Central Blood Center';
$city_bank_name = $blood_bank_names[$city_blood_bank_id] ?? 'City Blood Bank';

// --- End Fetch Inventory ---


// Get hospital's blood requests (show all statuses for history)
$requests_sql = "SELECT br.*, bb.name as bloodbank_name
                 FROM blood_requests br
                 LEFT JOIN blood_banks bb ON br.bloodbank_id = bb.id
                 WHERE br.hospital_id = ?
                 ORDER BY br.created_at DESC"; // Show most recent requests first
$requests_stmt = $conn->prepare($requests_sql);
$requests_stmt->bind_param("i", $hospital_id);
$requests_stmt->execute();
$hospital_requests = $requests_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$requests_stmt->close();

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hospital_name); ?> Dashboard - BDMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
     <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-header {
            background-color: #dc3545;
            color: white;
            padding: 15px 0;
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
        .status-pending { color: #ffc107; } /* warning */
        .status-approved { color: #198754; } /* success */
        .status-rejected { color: #dc3545; } /* danger */
        .status-delivered { color: #0d6efd; } /* info or primary */

        .urgency-normal { color: #198754; } /* success */
        .urgency-urgent { color: #ffc107; } /* warning */
        .urgency-emergency { color: #dc3545; font-weight: bold; } /* danger */

        /* Removed #inventory-display styles as it's split */

         #central-inventory-display, #city-inventory-display {
             margin-top: 15px;
         }

         .inventory-table-container table {
             margin-top: 10px; /* Reduced margin */
         }

         /* Style for disabled button */
         .btn:disabled {
             opacity: 0.6;
             cursor: not-allowed;
         }
    </style>
</head>
<body>
    <script>console.log('Very early script check.');</script> <!-- Debug log -->
    <div class="dashboard-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="mb-0">Hospital Dashboard - <?php echo htmlspecialchars($hospital_name); ?></h1>
                <a href="logout.php" class="btn btn-light">Logout</a>
            </div>
        </div>
    </div>

    <div class="container">
         <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo htmlspecialchars($central_bank_name); ?> Inventory</h5>
                    </div>
                    <div class="card-body">
                         <div id="central-inventory-display" class="inventory-table-container">
                            <?php if ($central_inventory === false): // Check for fetch error ?>
                                <p class="text-danger">Error loading inventory.</p>
                            <?php elseif (empty($central_inventory)): ?>
                                <p class="text-muted">No non-expired inventory available.</p>
                            <?php else: ?>
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Blood Type</th>
                                            <th>Units</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $inventoryMap = array_column($central_inventory, 'total_units', 'blood_type');
                                        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                        foreach ($bloodTypes as $bloodType) {
                                            $units = $inventoryMap[$bloodType] ?? 0;
                                            $isAvailable = $units > 0;
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($bloodType) . '</td>';
                                            echo '<td>' . htmlspecialchars($units) . '</td>';
                                            echo '<td>';
                                            echo '<button class="btn btn-sm btn-primary request-btn"';
                                            echo 'data-bloodbank-id="' . htmlspecialchars($central_blood_bank_id) . '"';
                                            echo 'data-bloodbank-name="' . htmlspecialchars($central_bank_name) . '"';
                                            echo 'data-blood-type="' . htmlspecialchars($bloodType) . '"';
                                            echo 'data-available-units="' . htmlspecialchars($units) . '"';
                                            if (!$isAvailable) { echo ' disabled'; }
                                            echo '>Request</button>';
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                         </div>
                    </div>
                </div>

                <div class="card mt-4"> <!-- Added margin-top -->
                    <div class="card-header">
                        <h5><?php echo htmlspecialchars($city_bank_name); ?> Inventory</h5>
                    </div>
                    <div class="card-body">
                         <div id="city-inventory-display" class="inventory-table-container">
                             <?php if ($city_inventory === false): // Check for fetch error ?>
                                <p class="text-danger">Error loading inventory.</p>
                            <?php elseif (empty($city_inventory)): ?>
                                <p class="text-muted">No non-expired inventory available.</p>
                            <?php else: ?>
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Blood Type</th>
                                            <th>Units</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $inventoryMap = array_column($city_inventory, 'total_units', 'blood_type');
                                        $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                        foreach ($bloodTypes as $bloodType) {
                                            $units = $inventoryMap[$bloodType] ?? 0;
                                            $isAvailable = $units > 0;
                                            echo '<tr>';
                                            echo '<td>' . htmlspecialchars($bloodType) . '</td>';
                                            echo '<td>' . htmlspecialchars($units) . '</td>';
                                            echo '<td>';
                                            echo '<button class="btn btn-sm btn-primary request-btn"';
                                            echo 'data-bloodbank-id="' . htmlspecialchars($city_blood_bank_id) . '"';
                                            echo 'data-bloodbank-name="' . htmlspecialchars($city_bank_name) . '"';
                                            echo 'data-blood-type="' . htmlspecialchars($bloodType) . '"';
                                            echo 'data-available-units="' . htmlspecialchars($units) . '"';
                                            if (!$isAvailable) { echo ' disabled'; }
                                            echo '>Request</button>';
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                         </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>My Blood Requests</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Blood Type</th>
                                    <th>Units</th>
                                    <th>Urgency</th>
                                    <th>Status</th>
                                    <th>Request Date</th>
                                    <th>Blood Bank</th>
                                        <!-- Actions could go here later -->
                                </tr>
                            </thead>
                            <tbody>
                                    <?php if (empty($hospital_requests)): ?>
                                        <tr><td colspan="6" class="text-center">No blood requests found</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($hospital_requests as $request): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($request['blood_type']); ?></td>
                                            <td><?php echo htmlspecialchars($request['units']); ?></td>
                                            <td class="urgency-<?php echo strtolower($request['urgency']); ?>"><?php echo htmlspecialchars($request['urgency']); ?></td>
                                            <td class="status-<?php echo strtolower($request['status']); ?>"><?php echo htmlspecialchars($request['status']); ?></td>
                                            <td><?php echo date('M d, Y H:i', strtotime($request['created_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($request['bloodbank_name'] ?? 'N/A'); ?></td>
                                            <!-- Actions could go here -->
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Blood Modal -->
    <div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
            <h5 class="modal-title" id="requestModalLabel">Request <span id="modal-blood-type"></span> from <span id="modal-blood-bank-name"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
            <form id="requestForm" action="request_blood.php" method="POST">
              <input type="hidden" name="bloodbank_id" id="modal-bloodbank-id">
              <input type="hidden" name="blood_type" id="modal-blood-type-input">
                        <div class="mb-3">
                <label for="units" class="form-label">Number of Units</label>
                <input type="number" class="form-control" name="units" id="modal-units" min="1" required>
                <small class="text-muted" id="modal-available-units">Available: --</small>
                        </div>
                        <div class="mb-3">
                <label for="urgency" class="form-label">Urgency Level</label>
                <select class="form-select" name="urgency" id="modal-urgency" required>
                                <option value="normal">Normal</option>
                                <option value="urgent">Urgent</option>
                  <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>
    <script>
        console.log('Main script started.'); // Debug log to see if the script runs at all
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded fired.'); // Debug log
            // Removed blood bank buttons as inventory is displayed directly
            // const bloodBankButtons = document.querySelectorAll('.blood-bank-btn');
            
            // Removed inventoryDisplayDiv as it is split into central and city
            // const inventoryDisplayDiv = document.getElementById('inventory-display');

            const requestModal = new bootstrap.Modal(document.getElementById('requestModal'));
            const modalBloodTypeSpan = document.getElementById('modal-blood-type');
            const modalBloodBankNameSpan = document.getElementById('modal-blood-bank-name');
            const modalBloodbankIdInput = document.getElementById('modal-bloodbank-id');
            const modalBloodTypeInput = document.getElementById('modal-blood-type-input');
            const modalUnitsInput = document.getElementById('modal-units');
            const modalAvailableUnitsSmall = document.getElementById('modal-available-units');

            // Removed fetchAndDisplayInventory function
            // Removed htmlspecialchars function as PHP is handling display

            // Add event listeners to the new request buttons
            // These buttons are now rendered directly by PHP, so add listeners after DOM is ready
            document.querySelectorAll('.request-btn').forEach(button => {
                button.addEventListener('click', openRequestModal);
            });


            // Function to open the request modal
            function openRequestModal() {
                const bloodbankId = this.dataset.bloodbankId;
                const bloodbankName = this.dataset.bloodbankName;
                const bloodType = this.dataset.bloodType;
                const availableUnits = parseInt(this.dataset.availableUnits) || 0;

                console.log('Opening request modal for:', { bloodbankId, bloodbankName, bloodType, availableUnits }); // Debug log

                // Populate the modal
                modalBloodTypeSpan.textContent = bloodType;
                modalBloodBankNameSpan.textContent = bloodbankName;
                modalBloodbankIdInput.value = bloodbankId;
                modalBloodTypeInput.value = bloodType;
                modalAvailableUnitsSmall.textContent = `Available: ${availableUnits}`;
                modalUnitsInput.max = availableUnits;
                modalUnitsInput.value = '';

                // Enable/disable submit button based on available units
                const submitButton = document.querySelector('#requestModal button[type="submit"]');
                submitButton.disabled = availableUnits === 0;
                modalUnitsInput.disabled = availableUnits === 0;

                requestModal.show();
            }


            // Optional: Load inventory for the first blood bank on page load
            // Removed as inventory is now displayed directly by PHP

        });
    </script>
</body>
</html> 