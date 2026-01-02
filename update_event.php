<?php
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'sirim');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

$eventId     = trim($_POST['event_id'] ?? '');
$eventName   = trim($_POST['event_name'] ?? '');
$googleForm  = trim($_POST['google_form'] ?? '');
$sheetId     = trim($_POST['sheet_id'] ?? '');
$startDate   = trim($_POST['eventStartDate'] ?? '');
$endDate     = trim($_POST['eventEndDate'] ?? '');
$location    = trim($_POST['location'] ?? '');
$organizedBy = trim($_POST['organizedBy'] ?? '');
$capacity    = is_numeric($_POST['capacity'] ?? '') ? (int)$_POST['capacity'] : 0;
$paymentRequired = isset($_POST['payment_required']) ? (int)$_POST['payment_required'] : 0;
$paymentAmount   = isset($_POST['payment_amount']) ? (float)$_POST['payment_amount'] : 0.00;

$sql = "UPDATE events SET 
            event_name = ?, 
            google_form_link = ?, 
            google_sheet_id = ?, 
            event_start_date = ?, 
            event_end_date = ?, 
            event_location = ?, 
            event_capacity = ?, 
            organized_by = ?, 
            payment_required = ?, 
            payment_amount = ?
        WHERE event_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssisdsi", $eventName, $googleForm, $sheetId, $startDate, $endDate, $location, $capacity, $organizedBy, $paymentRequired, $paymentAmount, $eventId);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Event updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update event.']);
}

$stmt->close();
$conn->close();
?>


