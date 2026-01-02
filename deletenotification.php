<?php
include 'db.php';

$id = $_POST['notif_id'];

$sql = "DELETE FROM notification WHERE notif_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "Notification deleted.";
} else {
    echo "Error: " . $stmt->error;
}
?>
