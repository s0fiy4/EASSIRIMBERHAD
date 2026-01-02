<?php
header('Content-Type: application/json');
include 'db.php';

$eventId = $_GET['event_id'] ?? '';
if (empty($eventId)) {
    echo json_encode(['error' => 'Invalid event ID']);
    exit;
}

if ($eventId <= 0) {
    echo json_encode(['error' => 'Invalid event ID']);
    exit;
}

// Get Present attendees
// Get Present attendees
$stmt = $conn->prepare("SELECT attendee_name, email_attendee AS email, company_name, position_attendee, attendee_phonenum, dept_id, attendance_status, is_walkin FROM attendees WHERE event_id = ? AND attendance_status = 'Present'");
$stmt->bind_param('s', $eventId); // use 's' for string
$stmt->execute();
$presentResult = $stmt->get_result();

$presentAttendees = [];
while ($row = $presentResult->fetch_assoc()) {
    $presentAttendees[] = $row;
}
$presentCount = count($presentAttendees);
$stmt->close();

// Get Absent attendees
$stmt = $conn->prepare("SELECT attendee_name, email_attendee AS email, company_name, position_attendee, attendee_phonenum, dept_id, attendance_status, is_walkin FROM attendees WHERE event_id = ? AND (attendance_status IS NULL OR attendance_status != 'Present')");
$stmt->bind_param('s', $eventId); // use 's' for string
$stmt->execute();
$absentResult = $stmt->get_result();

$absentAttendees = [];
while ($row = $absentResult->fetch_assoc()) {
    $absentAttendees[] = $row;
}
$absentCount = count($absentAttendees);
$stmt->close();
$conn->close();

// Final JSON output
echo json_encode([
    'present_count' => $presentCount,
    'absent_count' => $absentCount,
    'present_attendees' => $presentAttendees,
    'absent_attendees' => $absentAttendees
]);


