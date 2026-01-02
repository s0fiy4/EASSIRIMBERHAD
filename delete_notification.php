<?php
header('Content-Type: application/json');

require_once 'db.php'; // ✅ Replace with your DB connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notif_id = $_POST['notif_id'] ?? '';

    if (empty($notif_id)) {
        echo json_encode(['success' => false, 'message' => 'Missing notification ID']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM notifications WHERE notif_id = ?");
    $stmt->bind_param("s", $notif_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
