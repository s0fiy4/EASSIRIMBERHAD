<?php
include 'db.php'; // Make sure this connects and $conn is your mysqli connection

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$query = "SELECT notif_id, event_id, notification_type, details, send_to, attachment, specific_email, created_at FROM notifications ORDER BY notif_id ASC";
$result = $conn->query($query);

if (!$result) {
    echo json_encode([
        "success" => false,
        "error" => "Database query failed: " . $conn->error
    ]);
    exit;
}

$notifications = [];
while ($row = $result->fetch_assoc()) {
    // Normalize keys if needed (e.g. specific_email vs email)
    $notifications[] = $row;
}

echo json_encode([
    "success" => true,
    "data" => $notifications
]);

$conn->close();
?>



