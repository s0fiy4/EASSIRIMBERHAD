<?php
require('fpdf/fpdf.php');
require('PHPMailer/PHPMailer.php');
require('PHPMailer/SMTP.php');
require('PHPMailer/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = new mysqli("localhost", "root", "", "sirim");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$event_id = $_POST['event_id'] ?? '';
$email = $_POST['email'] ?? '';

if (!$event_id || !$email) {
    die("Please provide both event ID and email.");
}

// Fetch attendee details from database
$stmt = $conn->prepare("SELECT attendee_name, event_name FROM attendees WHERE event_id = ? AND email_attendee = ?");
$stmt->bind_param("ss", $event_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No matching certificate found for this email and event.");
}

$data = $result->fetch_assoc();
$name = $data['attendee_name'];
$event = $data['event_name'];

// Generate the PDF
$pdf = new FPDF('L', 'mm', 'A4'); // Landscape
$pdf->AddPage();
$pdf->SetFillColor(240, 240, 255);
$pdf->Rect(0, 0, 297, 210, 'F');
$pdf->Image('images/LOGOSIRIM.jpg', 10, 10, 30);
$pdf->SetFont('Arial', 'B', 32);
$pdf->SetTextColor(0, 43, 92);
pdf->Cell(0, 40, 'Certificate of Participation', 0, 1, 'C');
$pdf->SetFont('Arial', '', 16);
$pdf->SetTextColor(0, 0, 0);
pdf->Cell(0, 10, "This is to certify that", 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 24);
$pdf->Cell(0, 20, $name, 0, 1, 'C');
$pdf->SetFont('Arial', '', 16);
pdf->Cell(0, 10, "has successfully attended the event:", 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 18);
pdf->Cell(0, 10, $event, 0, 1, 'C');
$pdf->SetY(-50);
$pdf->Image('images/signature.png', 220, $pdf->GetY(), 40);
pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 30, 'Authorized Signature', 0, 1, 'R');

// Save PDF to server
$certDir = __DIR__ . '/certificates/';
if (!is_dir($certDir)) mkdir($certDir, 0777, true);
$certPath = $certDir . "certificate_" . md5($name . $event_id) . ".pdf";
$pdf->Output('F', $certPath);

// Send Email with PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your@gmail.com'; // Replace with your sender email
    $mail->Password = 'your-app-password'; // Replace with your Gmail app password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('your@gmail.com', 'SIRIM Event');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Your Certificate for $event";
    $mail->Body = "Dear $name,<br><br>Attached is your certificate for the event <b>$event</b>.<br><br>Thank you!<br>SIRIM Event Team";

    $mail->addAttachment($certPath);

    $mail->send();
    echo "Certificate generated and emailed to $email.";
} catch (Exception $e) {
    echo "Certificate generated but email failed: {$mail->ErrorInfo}";
}
?>

