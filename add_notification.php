<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';
require('phpqrcode/qrlib.php');

header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "sirim");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

// Input fields
$event_id = $_POST['event_id'] ?? null;
$notification_type = trim($_POST['notification_type'] ?? '');
$send_to = $_POST['send_to'] ?? '';
$specific_email = trim($_POST['specific_email'] ?? '');
$attachment = '';
$attendee_name = '';
$event_name = '';
$details = '';
if (isset($_POST['details']) && !empty(trim($_POST['details']))) {
    $details = trim($_POST['details']);
}

// Validate required input
if ($event_id === null || $event_id === '' || $event_id == 0) {
    echo json_encode(["success" => false, "error" => "Invalid or missing event_id"]);
    $conn->close();
    exit;
}


// Handle QR Pass generation
if (strtolower($notification_type) === 'qr pass' && $send_to === 'Specific') {
    if (empty($specific_email)) {
        echo json_encode(['success' => false, 'error' => 'Email required for QR Pass.']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT e.event_name, e.event_location, e.event_start_date, a.attendee_name, a.company_name 
        FROM events e 
        JOIN attendees a ON e.event_id = a.event_id 
        WHERE e.event_id = ? AND a.email_attendee = ?
    ");
    $stmt->bind_param("is", $event_id, $specific_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "error" => "Attendee not found."]);
        exit;
    }

    $data = $result->fetch_assoc();
    $stmt->close();

    $attendee_name = $data['attendee_name'];
    $event_name = $data['event_name'];
    $baseUrl = "https://localhost/attendance.php";
    $qr_data = $baseUrl . "?event_id=" . urlencode($event_id) . "&email=" . urlencode($specific_email);


    $qr_dir = __DIR__ . '/qrcodes/';
    if (!is_dir($qr_dir)) mkdir($qr_dir, 0777, true);
    $filename = $qr_dir . "qr_" . md5($specific_email . $event_id . time()) . '.png';

    QRcode::png($qr_data, $filename);
    $attachment = $filename;
    $details = "QR Pass for {$attendee_name} attached.";
}

// Handle normal file upload if not QR
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
    $uploadDir = __DIR__ . "/uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $filename = time() . '_' . basename($_FILES['attachment']['name']);
    $targetFile = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetFile)) {
        $attachment = $targetFile;
        $details = $details ?: "File uploaded.";
    }
}

if ($_POST['notification_type'] === 'Certificate') {
    $certificate_link = "http://localhost/certificatedownload.html";
    $details = "🎓 Your certificate is now ready!<br>" .
               "Please visit the link below and enter your email and Event ID to download it:<br>" .
               "<a href=\"$certificate_link\">Download Certificate</a>";
}

// Save notification in DB
$sql = "INSERT INTO notifications (event_id, notification_type, details, attachment, send_to, specific_email, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssss", $event_id, $notification_type, $details, $attachment, $send_to, $specific_email);
$insertSuccess = $stmt->execute();
$stmt->close();

// Send Email if specific
if ($send_to === 'Specific' && !empty($specific_email)) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'sofiyashukri022@gmail.com';
        $mail->Password = '';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('sofiyashukri022@gmail.com', 'SIRIM Event');
        $mail->addAddress($specific_email);

        $mail->isHTML(true);
        $mail->Subject = "Notification: $notification_type";

        $body = "Dear " . ($attendee_name ?: 'Participant') . ",<br><br>";
        $body .= $details . "<br><br>Thank you.<br>SIRIM Event Team";

        if (!empty($attachment) && file_exists($attachment)) {
            $mail->addAttachment($attachment);
        }


        $mail->Body = $body;
        $mail->send();
    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
    }
}

// Send Email to ALL attendees for this event
if ($send_to === 'ALL') {
    $query = $conn->prepare("SELECT email_attendee, attendee_name FROM attendees WHERE event_id = ?");
    $query->bind_param("i", $event_id);
    $query->execute();
    $result = $query->get_result();

    while ($row = $result->fetch_assoc()) {
        $email = $row['email_attendee'];
        $attendeeName = $row['attendee_name'];

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sofiyashukri022@gmail.com';
            $mail->Password = '';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('sofiyashukri022@gmail.com', 'SIRIM Event');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Notification: $notification_type";

            $body = "Dear " . ($attendeeName ?: 'Participant') . ",<br><br>";
            $body .= $details . "<br><br>Thank you.<br>SIRIM Event Team";

            if (!empty($attachment) && file_exists($attachment)) {
                $mail->addAttachment($attachment);
            }

            $mail->Body = $body;
            $mail->send();
        } catch (Exception $e) {
            error_log("Mail to $email failed: " . $mail->ErrorInfo);
        }
    }

    $query->close();
}

//send to everyone in the attendees database
if ($send_to === 'ALL') {
    $query = $conn->prepare("SELECT email_attendee, attendee_name FROM attendees");

        $query->execute();
    $result = $query->get_result();

    while ($row = $result->fetch_assoc()) {
        $email = $row['email_attendee'];
        $attendeeName = $row['attendee_name'];

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sofiyashukri022@gmail.com';
            $mail->Password = '';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('sofiyashukri022@gmail.com', 'SIRIM Event');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Notification: $notification_type";

            $mail->Body = "Dear $attendeeName,<br><br>" . $details . "<br><br>Thank you,<br>SIRIM Event Team";

            if (!empty($attachment) && file_exists($attachment)) {
                $mail->addAttachment($attachment);
            }

            $mail->send();
        } catch (Exception $e) {
            error_log("Failed to send to $email: " . $mail->ErrorInfo);
        }
    }

    $query->close();
}


// Handle QR Pass block
if (strtolower($notification_type) === 'qr pass' && $send_to === 'Specific') {
    // generates $details
}

// Handle file upload
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
    // may set $details
}

// ✅ Finally: allow manual override if user typed something
if (isset($_POST['details']) && !empty(trim($_POST['details']))) {
    $details = trim($_POST['details']);
}


echo json_encode([
    'success' => $insertSuccess,
    'notification_type' => $notification_type,
    'sent_to' => $send_to,
    'email' => $specific_email,
    'attachment' => $attachment,
    'details' => $details
]);

$conn->close();
?>


