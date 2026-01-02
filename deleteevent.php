<?php
header('Content-Type: application/json');
require_once 'db.php';

// Only accept JSON POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$eventId = $data['event_id'] ?? '';

if (empty($eventId)) {
    echo json_encode(["success" => false, "message" => "Missing event_id"]);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $eventId);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Event deleted successfully"]);
    } else {
        throw new Exception("Delete failed: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
?>


