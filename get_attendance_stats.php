<?php
header('Content-Type: application/json');
require 'db.php';

if (!isset($_GET['event_id']) || !is_numeric($_GET['event_id'])) {
    echo json_encode(['error' => 'Invalid event ID']);
    exit;
}

$event_id = intval($_GET['event_id']);

// Total Present
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM attendees WHERE event_id = ? AND attendance_status = 'Present'");
$stmt->bind_param('i', $event_id);
$stmt->execute();
$result = $stmt->get_result();
$present = $result->fetch_assoc()['count'] ?? 0;
$stmt->close();

// Total Absent (either 'Absent' or NULL)
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM attendees WHERE event_id = ? AND (attendance_status = 'Absent' OR attendance_status IS NULL)");
$stmt->bind_param('i', $event_id);
$stmt->execute();
$result = $stmt->get_result();
$absent = $result->fetch_assoc()['count'] ?? 0;
$stmt->close();

$conn->close();

echo json_encode([
    'present' => (int)$present,
    'absent' => (int)$absent
]);
