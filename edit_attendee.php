<?php
require_once 'db.php'; // include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendee_id = $_POST['attendee_id'] ?? '';
    $attendee_name = $_POST['attendee_name'] ?? '';
    $dept_id = $_POST['dept_id'] ?? '';
    $email_attendee = $_POST['email_attendee'] ?? '';
    $attendee_phonenum = $_POST['attendee_phonenum'] ?? '';
    $company_name = $_POST['company_name'] ?? '';

    if (empty($attendee_id) || empty($attendee_name) || empty($dept_id) || empty($email_attendee)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE attendees SET attendee_name = ?, dept_id = ?, email_attendee = ?, attendee_phonenum = ?, company_name = ? WHERE attendee_id = ?");
    $stmt->bind_param("sssssi", $attendee_name, $dept_id, $email_attendee, $attendee_phonenum, $company_name, $attendee_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed.']);
    }

    $stmt->close();
    $conn->close();
}
?>
