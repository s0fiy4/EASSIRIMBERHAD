<?php
header('Content-Type: application/json');
include 'db.php'; // Make sure this connects to your DB

$eventId = $_GET['event_id'] ?? null;

if (!$eventId) {
    echo json_encode(["error" => "Missing event ID"]);
    exit;
}

$sql = "
    SELECT 
        attendee_name, 
        position_attendee, 
        company_name,
        attendee_phonenum, 
        attendance_status AS status, 
        attendance_time AS checkin_time
    FROM attendees
    WHERE event_id = ?
    ORDER BY attendee_name ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);
$conn->close();

