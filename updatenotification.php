<?php
include 'db.php';

$id = $_POST['notif_id'];
$details = $_POST['details'];
$type = $_POST['notification_type'];

$sql = "UPDATE notification SET notification_type=?, details=? WHERE notif_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $type, $details, $id);

if ($stmt->execute()) {
    echo "Notification updated.";
} else {
    echo "Error: " . $stmt->error;
}
?>

