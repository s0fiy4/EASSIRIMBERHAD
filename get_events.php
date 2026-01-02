<?php
// get_events.php
include 'db.php'; 

$query = "SELECT event_id, event_name, google_sheet_id FROM events";
$result = mysqli_query($conn, $query);

$events = [];

while ($row = mysqli_fetch_assoc($result)) {
    $sheetUrl = "https://opensheet.elk.sh/" . $row['google_sheet_id'] . "/Form Responses 1";
    $events[] = [
        'id' => $row['event_id'],
        'name' => $row['event_name'],
        'sheetUrl' => $sheetUrl
    ];
}

header('Content-Type: application/json');
echo json_encode($events);
?>
