<?php
header('Content-Type: application/json');
include 'db.php'; // your DB connection file

if (isset($_POST['attendee_id'], $_POST['attendee_name'])) {
    $attendee_id = $_POST['attendee_id'];
    $attendee_name = $_POST['attendee_name'];

    $stmt = $conn->prepare("UPDATE attendees SET attendee_name = ? WHERE attendee_id = ?");
    $stmt->bind_param("si", $attendee_name, $attendee_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}
$conn->close();
?>
