<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// Read and decode the JSON payload
$data = json_decode(file_get_contents("php://input"), true);

// Check for missing fields
if (
    !isset($data['attendee_id']) || 
    !isset($data['event_id']) || 
    !isset($data['attendee_name']) || 
    !isset($data['payment_status'])
) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

include 'db.php'; 

$attendee_id = $data['attendee_id'];
$event_id = $data['event_id'];
$name = $data['attendee_name'];
$status = $data['payment_status'];
$payment_date = $data['payment_date'] ?: NULL;

// Prepare and execute update
$stmt = $conn->prepare("UPDATE attendees SET attendee_name = ?, payment_status = ?, payment_date = ? WHERE attendee_id = ? AND event_id = ?");
$stmt->bind_param("sssii", $name, $status, $payment_date, $attendee_id, $event_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
