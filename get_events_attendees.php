<?php
header('Content-Type: application/json');
$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if ($eventId <= 0) {
    echo json_encode(['error' => 'Invalid event ID']);
    exit;
}

$conn = new mysqli('localhost', 'root', "", 'sirim');
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Fetch payment details
$stmt = $conn->prepare("SELECT payment_required, payment_amount FROM events WHERE event_id = ?");
$stmt->bind_param('i', $eventId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Event not found']);
    exit;
}

$event = $result->fetch_assoc();
$stmt->close();

// Fetch attendees
$stmt = $conn->prepare("SELECT attendee_id, attendee_name, payment_status, payment_date, receipt FROM attendees WHERE event_id = ?");
$stmt->bind_param('i', $eventId);
$stmt->execute();
$attendeesResult = $stmt->get_result();

$attendees = [];
while ($row = $attendeesResult->fetch_assoc()) {
    $attendees[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    'payment_required' => (bool)$event['payment_required'],
    'payment_amount' => (float)$event['payment_amount'],
    'attendees' => $attendees
]);
?>
