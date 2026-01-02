<?php
require 'phpqrcode/qrlib.php'; // QR Code library
require 'vendor/autoload.php'; // PHPMailer via Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
$conn = new mysqli('localhost', 'root', '', 'sirim');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$attendee_id = $_POST['attendee_id'] ?? '';

if (!$attendee_id) {
    exit("Attendee ID is required.");
}

// Fetch attendee & event info
$sql = "SELECTe.event_name, e.event_location, e.event_start_date, a.attendee_name, a.company_name,a.dept_id
        FROM attendee a
        JOIN events e ON a.event_id = e.event_id
        WHERE a.attendee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $attendee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit("No attendee found.");
}

$data = $result->fetch_assoc();
$name = $data['attendee_name'];
$company = $data['company_name'];
$email = $data['email_attendee'];
$event = $data['event_name'];
$location = $data['event_location'];
$date = $data['date_event'];


// Generate QR Code content
$qr_content = "Name: $name\nCompany: $company\nTime: $time\nDate: $date\nEvent: $event\nLocation: $location";

// Create QR Code
$qr_dir = 'qrcodes/';
if (!is_dir($qr_dir)) mkdir($qr_dir);
$qr_file = $qr_dir . "qr_$attendee_id.png";
QRcode::png($qr_content, $qr_file, QR_ECLEVEL_L, 4);

// Send email with PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'sofiyashukri022@gmail.com';
    $mail->Password = 'wshf wpiv xbjd xpqr';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('sofiyashukri022@gmail.com', 'SIRIM Event Admin');
    $mail->addAddress($email, $name);

    $mail->Subject = "Your Event QR Code - $event";
    $mail->Body = "Dear $name,\n\nAttached is your QR code for event check-in.\n\nRegards,\nSIRIM Event Team";
    $mail->addAttachment($qr_file);

    $mail->send();
    echo "QR Code sent successfully to $email.";
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
