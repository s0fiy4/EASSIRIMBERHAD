<?php
header('Content-Type: application/json');
include 'db.php';

$startDate = isset($_GET['event_start_date']) ? $_GET['event_start_date'] : null;
$endDate = isset($_GET['event_end_date']) ? $_GET['event_end_date'] : null;

$sql = "SELECT event_id, event_name, start_date FROM events";
$conditions = [];

if ($startDate && $endDate) {
    $conditions[] = "event_start_date BETWEEN ? AND ?";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY start_date DESC";

$stmt = $conn->prepare($sql);

if ($startDate && $endDate) {
    $stmt->bind_param("ss", $startDate, $endDate);
}

$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}

echo json_encode($events);
$conn->close();
?>
