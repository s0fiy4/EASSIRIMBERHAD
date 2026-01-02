<?php
require 'db.php';

if (isset($_GET['notif_id'])) {
    $notif_id = $_GET['notif_id'];
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE notif_id = ?");
    $stmt->bind_param("i", $notif_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($data = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Notification not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing notif_id']);
}
?>


