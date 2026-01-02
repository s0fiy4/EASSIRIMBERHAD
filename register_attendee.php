<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// Collect POST data
$eventId = trim($_POST['event_id'] ?? '');
$name    = trim($_POST['attendee_name'] ?? '');
$position= trim($_POST['position_attendee'] ?? '');
$phone   = trim($_POST['attendee_phonenum'] ?? '');
$company = trim($_POST['company_name'] ?? '');
$email   = trim($_POST['attendee_email'] ?? '');

// Basic validation
if (empty($eventId) || empty($name) || empty($phone)) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

try {
    // Prepare receipt and auto‑detect payment status
    $receipt = trim($_POST['receipt'] ?? '');
    $payment_status = !empty($receipt) ? 'PAID' : 'UNPAID';

    // Insert attendee with receipt + payment_status
    $stmt = $conn->prepare("INSERT INTO attendees (
        event_id, attendee_name, position_attendee, attendee_phonenum,
        company_name, attendee_email, receipt, payment_status, attendance_status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Registered')");

    $stmt->bind_param(
        "isssssss",
        $eventId,
        $name,
        $position,
        $phone,
        $company,
        $email,
        $receipt,
        $payment_status
    );

    if (!$stmt->execute()) {
        throw new Exception("Attendee registration failed: " . $stmt->error);
    }
    $stmt->close();

    // Check event capacity
    $eventQuery = $conn->prepare("SELECT event_name, event_capacity FROM events WHERE event_id = ?");
    $eventQuery->bind_param("i", $eventId);
    $eventQuery->execute();
    $eventResult = $eventQuery->get_result();
    $event = $eventResult->fetch_assoc();
    $eventQuery->close();

    if ($event) {
        $eventName = $event['event_name'];
        $capacity  = $event['event_capacity'];

        // Count attendees
        $countQuery = $conn->prepare("SELECT COUNT(*) AS total FROM attendees WHERE event_id = ?");
        $countQuery->bind_param("i", $eventId);
        $countQuery->execute();
        $countResult = $countQuery->get_result();
        $total = $countResult->fetch_assoc()['total'];
        $countQuery->close();

        // Notify admin if capacity reached
        if ($total >= $capacity) {
            $to      = "sofiyashukri022@gmail.com";
            $subject = "⚠️ Event Capacity Reached: $eventName";
            $message = "
                <html>
                <head><title>Event Full</title></head>
                <body>
                    <h2>Event Capacity Reached</h2>
                    <p><strong>Event:</strong> $eventName</p>
                    <p><strong>Capacity:</strong> $capacity</p>
                    <p><strong>Current Registered Attendees:</strong> $total</p>
                </body>
                </html>";
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: sofiyashukri022@gmail.com";

            mail($to, $subject, $message, $headers);
        }
    }

    echo json_encode(["success" => true, "message" => "Attendee registered successfully"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();

