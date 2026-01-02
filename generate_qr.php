<?php
require 'phpqrcode/qrlib.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';
include 'db.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Validate input
$email = $_POST['email'] ?? null;
$event_id = $_POST['event_id'] ?? null;

if (!$email || !$event_id) {
    echo json_encode(["success" => false, "error" => "Missing email or event ID."]);
    exit;
}

// Prepare and execute SQL
$sql = "SELECT attendee_name FROM attendees WHERE email_attendee = ? AND event_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("si", $email, $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $attendee_name = $row['attendee_name'];

    // Define QR code content - you can customize URL or data here
    $qrContent = "Name: $attendee_name\nEvent ID: $event_id\nEmail: $email";

    // Ensure qr_codes directory exists
    $qrDir = __DIR__ . '/qr_codes/';
    if (!file_exists($qrDir)) {
        mkdir($qrDir, 0777, true);
    }

    // Generate QR code image file in qr_codes folder
    $qrFilename = 'qr_' . md5($email . $event_id . time()) . '.png';
    $qrPath = $qrDir . $qrFilename;
    QRcode::png($qrContent, $qrPath, QR_ECLEVEL_H, 5);

    // Check if file created successfully
    if (!file_exists($qrPath) || filesize($qrPath) == 0) {
        echo json_encode(["success" => false, "error" => "Failed to generate QR code image."]);
        exit;
    }

    // Prepare to send email with PHPMailer
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sofiyashukri022@gmail.com'; // Your Gmail
        $mail->Password   = 'wshf wpiv xbjd xpqr';       // Your App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('sofiyashukri022@gmail.com', 'SIRIM Event Admin');
        $mail->addAddress($email, $attendee_name);

        // Embed QR code image and attach as file
        $mail->addEmbeddedImage($qrPath, 'qrcode');
        $mail->addAttachment($qrPath, 'QRCode.png');

        $mail->isHTML(true);
        $mail->Subject = "Your Event QR Code";
        $mail->Body = "
            <strong>Notification</strong><br><br>
            Type: QR pass<br>
            Details: Here is your QR code:<br><br>
            <img src='cid:qrcode' alt='QR Code'><br><br>
            Thank you!<br><br>
            Regards,<br>
            SIRIM Event Admin
        ";
        $mail->AltBody = "Notification\n\nType: QR pass\nDetails: Please find your QR code attached.\n\nThank you!\nSIRIM Event Admin";

        $mail->send();

        echo json_encode(["success" => true, "message" => "QR code sent successfully."]);

    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => "Mailer Error: " . $mail->ErrorInfo]);
    }

} else {
    echo json_encode(["success" => false, "error" => "Attendee not found."]);
}

$qr_data = "https://localhost/attendance.php?event_id=$event_id&email=" . urlencode($specific_email);
QRcode::png($qr_data, $attachment);

$stmt->close();
$conn->close();
?>
