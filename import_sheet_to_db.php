<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';
header('Content-Type: application/json');

$sheetUrl = $_GET['sheetUrl'] ?? '';
$event_id = $_GET['event_id'] ?? '';

if (!$sheetUrl || !$event_id) {
    echo json_encode(['success' => false, 'message' => 'Missing sheetUrl or event_id']);
    exit;
}

// Fetch the published CSV
$csv = @file_get_contents($sheetUrl);
if (!$csv) {
    echo json_encode(['success' => false, 'message' => 'Failed to fetch sheet']);
    exit;
}

// Parse CSV
$rows = array_map("str_getcsv", explode("\n", $csv));
$header = array_map("trim", $rows[0]);
unset($rows[0]);

$mysqli = new mysqli("localhost", "root", "", "sirim");
if ($mysqli->connect_errno) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

$inserted = 0;

// Get event info once
$countRes = $mysqli->query("SELECT COUNT(*) AS count FROM attendees WHERE event_id = $event_id");
$initialCount = (int)($countRes->fetch_assoc()['count'] ?? 0);

$capRes = $mysqli->query("SELECT capacity FROM events WHERE event_id = $event_id");
$maxCapacity = (int)($capRes->fetch_assoc()['capacity'] ?? 0);

$payRes = $mysqli->query("SELECT payment_required FROM events WHERE event_id = $event_id");
$paymentRequired = (int)($payRes->fetch_assoc()['payment_required'] ?? 0);

foreach ($rows as $row) {
    if (count($row) < 8) continue;

    // Map CSV values
    $title      = trim($row[1]);
    $name       = trim($row[2]);
    $email      = trim($row[3]);
    $phone      = trim($row[4]);
    $position   = trim($row[5]);
    $department = trim($row[6]);
    $company    = trim($row[7]);
    $receipt    = trim($row[8] ?? '');

    // Prevent duplicate
    $check = $mysqli->prepare("SELECT 1 FROM attendees WHERE email_attendee = ? AND event_id = ?");
    $check->bind_param("si", $email, $event_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) continue;

    // Skip if capacity full
    if ($maxCapacity > 0 && ($initialCount + $inserted) >= $maxCapacity) continue;

    // Determine payment status
    $payment_status = 'UNPAID';
    $payment_date = null;
    if ($paymentRequired === 1 && !empty($receipt)) {
        $payment_status = 'PAID';
        $payment_date = date('Y-m-d');
    }

    // Insert into DB
    $stmt = $mysqli->prepare("INSERT INTO attendees 
        (attendee_name, attendee_phonenum, email_attendee, company_name, position_attendee, dept_id, event_id, payment_status, payment_date, receipt) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("ssssssisss", 
        $name, $phone, $email, $company, $position, $department, $event_id, 
        $payment_status, $payment_date, $receipt
    );

    if ($stmt->execute()) {
        $inserted++;
    }
}

// ✅ Notify once if full
if ($maxCapacity > 0 && ($initialCount + $inserted) >= $maxCapacity) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sofiyashukri022@gmail.com'; 
        $mail->Password   = ''; 
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('ai220016@student.uthm.edu.my', 'SIRIM Event System');
        $mail->addAddress('sofiyashukri022@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = '🚨 Event Capacity Reached';
        $mail->Body    = "Event ID <strong>$event_id</strong> has reached its full capacity of $maxCapacity attendees.";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
    }
}

$mysqli->close();
echo json_encode(['success' => true, 'inserted' => $inserted]);




