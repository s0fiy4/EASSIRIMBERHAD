<?php
header('Content-Type: application/json');

// Connect to your database
$conn = new mysqli('localhost', 'root', '', 'sirim');
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get event_id from query params safely
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if ($event_id <= 0) {
    echo json_encode(['error' => 'Invalid event ID']);
    exit;
}

// Query to get attendees for this event
$sql = "SELECT attendee_id, attendee_name, dept_id, attendee_phonenum, email_attendee, company_name, payment_status FROM attendees WHERE event_id = $event_id";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit;
}

$attendees = [];
while ($row = $result->fetch_assoc()) {
    $attendees[] = $row;
}

echo json_encode($attendees);
$conn->close();
?>
