<?php
header('Content-Type: application/json');
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
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

if (empty($eventName) || empty($startDate) || empty($location)) {
    echo json_encode(["success" => false, "message" => "Missing required fields (eventName, startDate, location)"]);
    exit;
}

try {
    if ($eventId === '') {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO events (
            event_name, google_form_link, google_sheet_id, event_start_date,
            event_end_date, event_location, event_capacity, organized_by,
            payment_required, payment_amount
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");


        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

         $stmt->bind_param("ssssssissd", $eventName, $googleForm, $sheetId, $startDate, $endDate, $location, $capacity, $organizedBy, $paymentRequired, $paymentAmount);


        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "New event created successfully", "event_id" => $conn->insert_id]);
        } else {
            throw new Exception("Insert failed: " . $stmt->error);
        }

        $stmt->close();
    } else {
        // UPDATE
        $stmt = $conn->prepare("UPDATE events SET
            event_name = ?, google_form_link = ?, google_sheet_id = ?, event_start_date = ?,
            event_end_date = ?, event_location = ?, event_capacity = ?, organized_by = ?,
            payment_required = ?, payment_amount = ?
            WHERE event_id = ?");

        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        $stmt->bind_param("ssssssisdsi", $eventName, $googleForm, $sheetId, $startDate, $endDate, $location, $capacity, $organizedBy, $paymentRequired, $paymentAmount, $eventId);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Event updated successfully", "event_id" => $eventId]);
        } else {
            throw new Exception("Update failed: " . $stmt->error);
        }

        $stmt->close();
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
?>