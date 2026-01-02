<?php
include 'db.php';

$id = $_POST['attendee_id'];
$name = $_POST['attendee_name'];
$email = $_POST['email_attendee'];
$phone = $_POST['attendee_phonenum'];
$company = $_POST['company_name'];
$position = $_POST['position_attendee'];
$department = $_POST['department_attendee']; // NEW

$query = "UPDATE attendees SET 
  attendee_name = '$name',
  email_attendee = '$email',
  attendee_phonenum = '$phone',
  company_name = '$company',
  department_attendee = '$department',
  position_attendee = '$position'
  WHERE attendee_id = '$id'";

if (mysqli_query($conn, $query)) {
  echo json_encode(['status' => 'success', 'message' => 'Attendee updated']);
} else {
  echo json_encode(['status' => 'error', 'message' => 'Failed to update']);
}

