<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email required']);
    exit;
}

$email = $data['email'];

// DB Connection
$conn = new mysqli('localhost', 'root', '', 'sirim');
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

// Update attendee payment status by email
$stmt = $conn->prepare("UPDATE attendees SET payment_status='Paid', payment_date=CURDATE() WHERE email_attendee = ?");
$stmt->bind_param('s', $email);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Update failed: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>

