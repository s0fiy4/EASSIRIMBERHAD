<?php
include 'db.php';

$event_id = $_POST['event_id'];
$type = $_POST['notification_type'];
$details = $_POST['details'];
$send_to = $_POST['send_to'];

$sql = "INSERT INTO notification (event_id, notification_type, details, send_to)
        VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isss", $event_id, $type, $details, $send_to);

if ($stmt->execute()) {
    echo "Notification added.";
} else {
    echo "Error: " . $stmt->error;
}
?>
