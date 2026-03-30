<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/pdf_error.log');
error_reporting(E_ALL);

session_start();
require_once 'config/database.php';
require_once 'vendor/autoload.php';  // Add Composer autoloader

// Check if user is logged in and is a donor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: login.php");
    exit();
}

// Check if appointment ID is provided
if (!isset($_GET['id'])) {
    header("Location: donor_dashboard.php");
    exit();
}

$appointment_id = $_GET['id'];
$donor_id = $_SESSION['user_id'];

// Get appointment details
$sql = "SELECT a.*, d.name as donor_name, bb.name as bloodbank_name 
        FROM appointments a 
        JOIN donors d ON a.donor_id = d.id 
        JOIN blood_banks bb ON a.bloodbank_id = bb.id 
        WHERE a.id = ? AND a.donor_id = ? AND a.status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $appointment_id, $donor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: donor_dashboard.php");
    exit();
}

$appointment = $result->fetch_assoc();

// Set headers for PDF download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="appointment_confirmation.pdf"');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('BDMS');
$pdf->SetTitle('Appointment Confirmation');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', 'B', 16);

// Title
$pdf->Cell(0, 10, 'Appointment Confirmation', 0, 1, 'C');
$pdf->Ln(10);

// Appointment Details
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Appointment Details', 0, 1);
$pdf->SetFont('helvetica', '', 12);

// Create a table for appointment details
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(60, 10, 'Donor Name', 1, 0, 'C', true);
$pdf->Cell(120, 10, $appointment['donor_name'], 1, 1);

$pdf->Cell(60, 10, 'Blood Bank', 1, 0, 'C', true);
$pdf->Cell(120, 10, $appointment['bloodbank_name'], 1, 1);

$pdf->Cell(60, 10, 'Date', 1, 0, 'C', true);
$pdf->Cell(120, 10, date('F j, Y', strtotime($appointment['date'])), 1, 1);

$pdf->Cell(60, 10, 'Time', 1, 0, 'C', true);
$pdf->Cell(120, 10, date('h:i A', strtotime($appointment['time'])), 1, 1);

$pdf->Cell(60, 10, 'Status', 1, 0, 'C', true);
$pdf->Cell(120, 10, ucfirst($appointment['status']), 1, 1);

// Important Notes
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Important Notes', 0, 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->MultiCell(0, 10, '1. Please arrive 15 minutes before your scheduled appointment time.\n2. Bring a valid government-issued ID.\n3. Make sure you have had enough rest and are well-hydrated.\n4. If you need to reschedule, please do so at least 24 hours before your appointment.', 0, 'L');

// Output PDF
$pdf->Output('appointment_confirmation.pdf', 'D');
exit();
?> 