<?php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$password = "";
$dbname = "sirim"; // adjust if yours is different

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database connection failed."]));
}

$sql = "SELECT * FROM events ORDER BY event_id DESC";
$result = $conn->query($sql);

$events = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

echo json_encode($events);
?>


