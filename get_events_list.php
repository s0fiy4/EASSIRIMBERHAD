<?php
header('Content-Type: application/json');
include 'db.php';

$sql = "SELECT event_id, event_name FROM events ORDER BY event_name ASC";
$result = $conn->query($sql);

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode($events);
$conn->close();
?>
