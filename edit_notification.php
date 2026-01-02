<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

ob_start();

header('Content-Type: application/json');

if (!isset($_POST['notif_id'])) {
    echo json_encode(['success' => false, 'message' => 'Notification ID missing']);
    exit;
}

// Extract data from POST
$notif_id = $_POST['notif_id'];
$event_id = $_POST['event_id'] ?? '';
$notification_type = $_POST['notification_type'] ?? '';
$details = $_POST['details'] ?? '';
$send_to = $_POST['send_to'] ?? '';
$specific_email = $_POST['specific_email'] ?? '';

// Validate inputs (simple example, you can expand)
if (!$notif_id || !$event_id || !$notification_type || !$details || !$send_to) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Connect to DB (update with your credentials)
$mysqli = new mysqli("localhost", "root", "", "sirim");
if ($mysqli->connect_errno) {
    echo json_encode(['success' => false, 'message' => 'Failed to connect to DB']);
    exit;
}

// Prepare update query
$stmt = $mysqli->prepare("UPDATE notifications SET event_id=?, notification_type=?, details=?, send_to=?, specific_email=? WHERE notif_id=?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param("sssssi", $event_id, $notification_type, $details, $send_to, $specific_email, $notif_id);

if ($stmt->execute()) {
    // Send email if 'Specific' selected
    if ($send_to === 'Specific' && !empty($specific_email)) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sofiyashukri022@gmail.com'; 
        $mail->Password   = 'wshf wpiv xbjd xpqr';   // Gmail app password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('sofiyashukri022@gmail.com', 'SIRIM Notification');
        $mail->addAddress($specific_email);

        // Content
        $mail->isHTML(false);
        $mail->Subject = "Notification: $notification_type";
        $mail->Body    = "You have a new notification for Event ID: $event_id\n\nDetails:\n$details";

        $mail->send();
        // Optional: log success
    } catch (Exception $e) {
        error_log("Mail error: {$mail->ErrorInfo}");
    }
}

    ob_end_clean();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$mysqli->close();
?>

