<?php
include 'db.php';

$id = $_POST['attendee_id'];
$query = "DELETE FROM attendees WHERE attendee_id = '$id'";

if (mysqli_query($conn, $query)) {
  echo json_encode(['status' => 'success', 'message' => 'Attendee deleted']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Failed to delete']);
}

