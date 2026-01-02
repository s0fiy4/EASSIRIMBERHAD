<?php
$host = 'localhost'; 
$dbname = 'sirim';
$username = 'root';
$password = '';

// Create DB connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check DB connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Extract POST data
$eventId         = isset($_POST['event_id']) ? intval($_POST['event_id']) : null;
$eventName       = $_POST['event_name'] ?? '';
$googleForm      = $_POST['google_form'] ?? '';
$eventDate       = $_POST['event_date'] ?? '';
$eventTime       = $_POST['event_time'] ?? '';
$eventVenue      = $_POST['event_venue'] ?? '';
$adminUsername   = $_POST['admin_username'] ?? '';
$capacity        = $_POST['event_capacity'] ?? null;

// Combine date and time if needed
$eventDateTime = $eventDate;
if (!empty($eventTime)) {
    $eventDateTime .= ' ' . $eventTime;
}

// INSERT or UPDATE logic
if ($eventId) {
    // UPDATE
    $sql = "UPDATE events 
            SET event_name = ?, google_form_url = ?, event_date = ?, location = ?, capacity = ?, organized_by = ? 
            WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisi", $eventName, $googleForm, $eventDateTime, $eventVenue, $capacity, $adminUsername, $eventId);
} else {
    // INSERT
    $sql = "INSERT INTO events (event_name, google_form_url, event_date, location, capacity, organized_by) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssis", $eventName, $googleForm, $eventDateTime, $eventVenue, $capacity, $adminUsername);
}

// Execute query
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
