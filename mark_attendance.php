<?php
require 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email_attendee'] ?? $_POST['email'] ?? '';
    $event_id = $_POST['event_id'] ?? '';

    if (!$email || !$event_id) {
        echo json_encode(["success" => false, "message" => "Missing email or event_id"]);
        exit;
    }

$stmt = $conn->prepare("UPDATE attendees 
    SET attendance_status = 'Present', attendance_time = NOW() 
    WHERE email_attendee = ? AND event_id = ?");
    $stmt->bind_param("si", $email, $event_id);

    if ($stmt->execute() && $stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Attendance marked successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "No matching attendee found"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}




