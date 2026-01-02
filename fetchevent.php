<?php
include 'db.php';

$result = $conn->query("SELECT * FROM events ORDER BY event_start_date ASC");
// or
// $result = $conn->query("SELECT * FROM events ORDER BY event_id ASC");

$rows = [];

while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);
?>
