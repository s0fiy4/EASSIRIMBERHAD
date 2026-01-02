<?php
include 'db.php';

$event_id = $_GET['event_id'];

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=attendance_event_' . $event_id . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Name', 'Email', 'Phone', 'Company', 'Status']);

$sql = "SELECT attendee_name, email, attendee_phonenum, company_name, attendance_status FROM attendees WHERE event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
?>
