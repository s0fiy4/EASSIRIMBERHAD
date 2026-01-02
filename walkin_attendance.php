<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;

if ($event_id === 0) {
    echo json_encode(["error" => "Missing event_id"]);
    exit;
}

$sql = "SELECT * FROM attendees WHERE event_id = ? AND attendance_status = 'Present'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

$walkins = [];
while ($row = $result->fetch_assoc()) {
    $walkins[] = $row;
}

echo json_encode(["walkin_attendees" => $walkins]);

$stmt->close();
$conn->close();



