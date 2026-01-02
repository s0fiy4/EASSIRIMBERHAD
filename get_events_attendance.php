<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db.php';

// Debug log (optional, remove in production)
file_put_contents("debug_event_id.log", json_encode($_GET) . PHP_EOL, FILE_APPEND);

// Validate event_id
if (!isset($_GET['event_id']) || !preg_match('/^\d+$/', $_GET['event_id'])) {
    echo json_encode(['error' => 'Missing or non-numeric event ID']);
    exit;
}

$eventId = (int)$_GET['event_id'];
if ($eventId <= 0) {
    echo json_encode(['error' => 'Event ID must be greater than zero']);
    exit;
}

// Optional filters
$statusFilter = $_GET['status'] ?? '';
$walkinFilter = $_GET['walkin'] ?? '';

// Build dynamic SQL
$sql = "
    SELECT 
        attendee_id, 
        attendee_name, 
        dept_id, 
        attendee_phonenum, 
        email_attendee AS email, 
        company_name, 
        COALESCE(attendance_status, 'Absent') AS attendance_status,
        is_walkin
    FROM attendees 
    WHERE event_id = ?
";

$params = [$eventId];
$types = 'i';

if ($statusFilter !== '') {
    $sql .= " AND attendance_status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if ($walkinFilter === '0' || $walkinFilter === '1') {
    $sql .= " AND is_walkin = ?";
    $params[] = (int)$walkinFilter;
    $types .= 'i';
}

// Prepare statement
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'SQL prepare failed: ' . $conn->error]);
    exit;
}

// Bind & execute
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Fetch data
$attendees = [];
while ($row = $result->fetch_assoc()) {
    $attendees[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($attendees);
?>
