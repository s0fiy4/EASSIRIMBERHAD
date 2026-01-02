<?php
header('Content-Type: application/json');

$conn = new mysqli('localhost', 'root', '', 'sirim');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Optional filter by event_id
$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;

$sql = "SELECT 
    e.event_name, 
    e.event_start_date, 
    at.attendee_name, 
    at.position_attendee, 
    at.attendee_phonenum,
    at.company_name,
    at.attendance_status AS status, 
    at.attendance_time
FROM attendees at
JOIN events e ON at.event_id = e.event_id";

if ($event_id) {
    $sql .= " WHERE e.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
} else {
    $sql .= " ORDER BY e.event_name, at.attendee_name";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // Make sure attendance_time is not null, or else replace with "-"
    $row['attendance_time'] = $row['attendance_time'] ?: "-";
    $row['status'] = $row['status'] ?: "-";
    // Position, company, phone fallback
    $row['position_attendee'] = $row['position_attendee'] ?: "-";
    $row['company_name'] = $row['company_name'] ?: "-";
    $row['attendee_phonenum'] = $row['attendee_phonenum'] ?: "-";


    $data[] = $row;
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>
