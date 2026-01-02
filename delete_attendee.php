<?php
header('Content-Type: application/json');
include 'db.php'; // your DB connection file

if (isset($_POST['attendee_id'])) {
    $attendee_id = $_POST['attendee_id'];

    $stmt = $conn->prepare("DELETE FROM attendees WHERE attendee_id = ?");
    $stmt->bind_param("i", $attendee_id);

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
