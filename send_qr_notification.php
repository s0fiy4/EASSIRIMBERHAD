<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'phpqrcode/qrlib.php';

header('Content-Type: application/json');

// Get email from POST
$specificEmail = isset($_POST['specific_email']) ? trim($_POST['specific_email']) : '';

if (empty($specificEmail)) {
    echo json_encode(['status' => 'error', 'message' => 'Recipient email is empty.']);
    exit;
}

// Customize QR content as needed
$qrContent = "Your QR Code for event attendance: $specificEmail";

$qrDir = __DIR__ . '/qr_codes/';
if (!file_exists($qrDir)) {
    mkdir($qrDir, 0777, true);
}

$qrFilename = 'qr_' . md5($specificEmail . time()) . '.png';
$qrPath = $qrDir . $qrFilename;

QRcode::png($qrContent, $qrPath, QR_ECLEVEL_L, 5);

if (!file_exists($qrPath)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to generate QR code.']);
    exit;
}

$mail = new PHPMailer(true);

try {
    // SMTP settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';       
    $mail->SMTPAuth = true;
    $mail->Username = 'sofiyashukri022@gmail.com';    
    $mail->Password = ''; // Store securely
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Email settings
    $mail->setFrom('sofiyashukri022@gmail.com', 'Event QR Notification');
    $mail->addAddress($specificEmail);
    $mail->Subject = 'Your QR Pass';

    // Embed the QR code image for inline display
    $mail->addEmbeddedImage($qrPath, 'qrimg');

    // Compose HTML email body
    $mail->isHTML(true);
    $mail->Body = "
        Hi,<br><br>
        Here is your QR Pass for the event:<br><br>
        <img src='cid:qrimg' alt='QR Code'><br><br>
        Thank you!<br>
    ";

    // Fallback plain-text body
    $mail->AltBody = "Hi,\n\nHere is your QR Pass for the event. Please see the attached QR code.\n\nThank you!";

    // Attach QR code as file
    $mail->addAttachment($qrPath, 'Your_QR_Pass.png');

    $mail->send();

    echo json_encode(['status' => 'success', 'message' => 'QR Pass sent successfully!']);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Email error: ' . $mail->ErrorInfo]);
}
?>

