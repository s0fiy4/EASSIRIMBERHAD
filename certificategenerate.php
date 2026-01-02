<?php
require('fpdf/fpdf.php'); // Make sure fpdf is available

$conn = new mysqli("localhost", "root", "", "sirim"); // change DB if needed
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$event_id = $_POST['event_id'] ?? '';
$email = $_POST['email'] ?? '';

if (!$event_id || !$email) {
    die("Missing event ID or email.");
}

// Fetch attendee info
$stmt = $conn->prepare("SELECT attendee_name, event_name FROM attendees WHERE event_id = ? AND email_attendee = ?");
$stmt->bind_param("ss", $event_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No certificate found for this email and event.");
}

$data = $result->fetch_assoc();
$name = $data['attendee_name'];
$event = $data['event_name'];

// Generate PDF certificate
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 24);
$pdf->Cell(0, 40, 'Certificate of Participation', 0, 1, 'C');
$pdf->SetFont('Arial', '', 16);
$pdf->Cell(0, 10, "This is to certify that", 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 15, $name, 0, 1, 'C');
$pdf->SetFont('Arial', '', 16);
$pdf->Cell(0, 10, "has successfully attended", 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 15, $event, 0, 1, 'C');

$pdf->Output('I', "Certificate_$name.pdf"); // I = inline view
?>

