<?php
include 'db.php';

$attendee_id = $_POST['attendee_id'] ?? null;

if (!$attendee_id) {
    echo json_encode(['success' => false, 'message' => 'Missing attendee ID.']);
    exit;
}

$update = $conn->prepare("UPDATE attendees SET attendance_status = 'Present', attendance_time = NOW() WHERE attendee_id = ?");
$update->bind_param("i", $attendee_id);

if ($update->execute()) {
    echo json_encode(['success' => true, 'message' => 'Attendance marked.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}

$update->close();
$conn->close();
?>
