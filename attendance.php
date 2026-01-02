<?php
include 'db.php';

$event_id = $_GET['event_id'] ?? '';
$email = $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($event_id) && !empty($email)) {

    // Connect to DB
    $conn = new mysqli("localhost", "root", "", "sirim");
    if ($conn->connect_error) {
        die("<h2 style='color:red;'>Database connection failed: " . $conn->connect_error . "</h2>");
    }

    // 1. Check attendee exists
    $stmt = $conn->prepare("SELECT * FROM attendees WHERE event_id = ? AND email_attendee = ?");
    $stmt->bind_param("is", $event_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("<h2 style='color:red;'>No matching attendee found for this QR code.</h2>");
    }

    $attendee = $result->fetch_assoc();
    $attendee_id = $attendee['attendee_id'];

    // 2. Update attendance (both status & time)
    $update = $conn->prepare("UPDATE attendees SET attendance_status = 'Present', attendance_time = NOW() WHERE attendee_id = ?");
    $update->bind_param("i", $attendee_id);
    $update->execute();
    $update->close();

    // 3. Redirect to recording page
    header("Location: attendancerecording.html?event_id=$event_id&email=$email");
    exit;

} else {
    die("<h2 style='color:red;'>Invalid QR code: Missing event ID or email.</h2>");
}
?>
