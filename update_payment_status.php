<?php
header('Content-Type: application/json');

// Parse POST data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['attendee_id']) || !isset($data['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing attendee_id or event_id']);
    exit;
}

$attendee_id = $data['attendee_id'];
$event_id = intval($data['event_id']);

// Database connection
$conn = new mysqli('localhost', 'root', '', 'sirim');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Update attendee's payment status
$stmt = $conn->prepare("UPDATE attendees SET payment_status = 'Paid', payment_date = CURDATE() WHERE attendee_id = ? AND event_id = ?");
$stmt->bind_param('si', $attendee_id, $event_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Payment updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed']);
}

$stmt->close();
$conn->close();
?>
