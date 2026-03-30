<?php
session_start();
require_once 'config/database.php';
require_once 'vendor/autoload.php';  // Add Composer autoloader

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

// Get blood inventory data
$sql = "SELECT blood_type, SUM(units) as total_units 
        FROM blood_inventory 
        WHERE bloodbank_id = ? 
        GROUP BY blood_type";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $bloodbank_id);
$stmt->execute();
$inventory_result = $stmt->get_result();

// Get total requests and appointments
$sql = "SELECT 
        (SELECT COUNT(*) FROM blood_requests WHERE bloodbank_id = ?) as total_requests,
        (SELECT COUNT(*) FROM appointments WHERE bloodbank_id = ?) as total_appointments";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $bloodbank_id, $bloodbank_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Set headers for PDF download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="blood_bank_report.pdf"');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('BDMS');
$pdf->SetTitle('Blood Bank Report - ' . $bloodbank['name']);

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', 'B', 16);

// Title
$pdf->Cell(0, 10, 'Blood Bank Report', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, $bloodbank['name'], 0, 1, 'C');
$pdf->Ln(10);

// Statistics
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Statistics', 0, 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Total Blood Requests: ' . $stats['total_requests'], 0, 1);
$pdf->Cell(0, 10, 'Total Appointments: ' . $stats['total_appointments'], 0, 1);
$pdf->Ln(10);

// Blood Inventory
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Blood Inventory', 0, 1);
$pdf->SetFont('helvetica', '', 12);

// Table header
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(80, 10, 'Blood Type', 1, 0, 'C', true);
$pdf->Cell(80, 10, 'Available Units', 1, 1, 'C', true);

// Table data
while ($row = $inventory_result->fetch_assoc()) {
    $pdf->Cell(80, 10, $row['blood_type'], 1, 0, 'C');
    $pdf->Cell(80, 10, $row['total_units'], 1, 1, 'C');
}

// Output PDF
$pdf->Output('blood_bank_report.pdf', 'D');
?> 