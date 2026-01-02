<?php
include('db.php');
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// 1) Read exactly the POST keys your form sends:
$eventId      = isset($_POST['event_id'])      ? intval($_POST['event_id']) : null;
$eventName    = $_POST['eventName']    ?? '';
$googleForm   = $_POST['google_form']  ?? '';
$startDate    = $_POST['eventStartDate'] ?? '';
$endDate      = $_POST['eventEndDate']   ?? '';
$location     = $_POST['eventLocation'] ?? '';
$capacity     = intval($_POST['eventCapacity'] ?? 0);
$organizedBy  = $_POST['organizedBy']   ?? '';

// 2) Decide insert vs. update
if ($eventId) {
    // UPDATE
    $sql = "UPDATE events
            SET event_name       = ?,
                google_form_link = ?,
                start_date       = ?,
                end_date         = ?,
                location         = ?,
                capacity         = ?,
                organized_by     = ?
            WHERE event_id       = ?";
    $stmt = $conn->prepare($sql);
    // types: s (name), s (form link), s (start), s (end), s (location), i (capacity), s (organized_by), i (event_id)
    $stmt->bind_param("ssssssis",
        $eventName,
        $googleForm,
        $startDate,
        $endDate,
        $location,
        $capacity,
        $organizedBy,
        $eventId
    );
} else {
    // INSERT
    $sql = "INSERT INTO events 
            (event_name, google_form_link, start_date, end_date, location, capacity, organized_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssis",
        $eventName,
        $googleForm,
        $startDate,
        $endDate,
        $location,
        $capacity,
        $organizedBy
    );
}

if (!$stmt) {
    echo json_encode(['success' => false, 'error' => $conn->error]);
    exit;
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();

