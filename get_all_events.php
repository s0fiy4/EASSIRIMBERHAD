<?php
// get_all_events.php

header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sirim";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Query to fetch all events
$sql = "SELECT event_id, event_name FROM events"; 
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['error' => 'Query failed: ' . $conn->error]);
    exit;
}

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'event_id' => $row['event_id'],
        'event_name' => $row['event_name']
    ];
}

// Output JSON
echo json_encode($events);

$conn->close();
?>
