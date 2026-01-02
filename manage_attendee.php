<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sirim";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed']));
}

$action = $_GET['action'] ?? '';

if ($action === 'fetch') {
    $event_id = $_GET['event_id'] ?? '';
    if (!$event_id) {
        echo json_encode(['error' => 'No event_id provided']);
        exit;
    }
    $stmt = $conn->prepare("SELECT attendee_id, dept_id, attendee_name, attendee_phonenum, email_attendee, company_name, position_attendee, payment_status, payment_date, receipt FROM attendees WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendees = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($attendees);
    exit;

}

if ($action === 'add') {
    $data = json_decode(file_get_contents('php://input'), true);
    $event_id = $data['event_id'] ?? '';
    $dept_id = $data['dept_id'] ?? '';
    $name = $data['attendee_name'] ?? '';
    $phone = $data['attendee_phonenum'] ?? '';
    $email = $data['email_attendee'] ?? '';
    $company = $data['company_name'] ?? '';
    $position = $data['position_attendee'] ?? '';
    $receipt = $data['receipt'] ?? '';

    // Auto mark as "Paid" if receipt is provided
    $payment_status = $receipt ? 'Paid' : 'Unpaid';
    $payment_date = $receipt ? date('Y-m-d') : null;

    $stmt = $conn->prepare("INSERT INTO attendees 
        (event_id, dept_id, attendee_name, attendee_phonenum, email_attendee, company_name, position_attendee, payment_status, payment_date, receipt) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "isssssssss", 
        $event_id, 
        $dept_id, 
        $name, 
        $phone, 
        $email, 
        $company, 
        $position, 
        $payment_status, 
        $payment_date, 
        $receipt
    );

    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'update') {
    $data = json_decode(file_get_contents('php://input'), true);
    $attendee_id = $data['attendee_id'] ?? '';
    $name = $data['attendee_name'] ?? '';
    $dept_id = $data['dept_id'] ?? '';
    $phone = $data['attendee_phonenum'] ?? '';
    $email = $data['email_attendee'] ?? '';
    $company = $data['company_name'] ?? '';
    $position = $data['position_attendee'] ?? '';
    $attendance_status = $data['attendance_status'] ?? '';
    $payment_status = $data['payment_status'] ?? '';
    $receipt = $data['receipt'] ?? '';

    // Auto set payment_date if marked as Paid and no payment_date exists
    $payment_date = ($payment_status === 'Paid') ? date('Y-m-d') : null;

    $stmt = $conn->prepare("UPDATE attendees SET 
        dept_id = ?, 
        attendee_name = ?, 
        attendee_phonenum = ?, 
        email_attendee = ?, 
        company_name = ?, 
        position_attendee = ?, 
        attendance_status = ?, 
        payment_status = ?, 
        payment_date = ?, 
        receipt = ?
        WHERE attendee_id = ?");

    $stmt->bind_param(
        "ssssssssssi",
        $dept_id,
        $name,
        $phone,
        $email,
        $company,
        $position,
        $attendance_status,
        $payment_status,
        $payment_date,
        $receipt,
        $attendee_id
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    exit;
}

//Delete
if ($action === 'delete') {
    $attendee_id = $_GET['attendee_id'] ?? '';
    if ($attendee_id) {
        $stmt = $conn->prepare("DELETE FROM attendees WHERE attendee_id = ?");
        $stmt->bind_param("i", $attendee_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'No attendee_id provided']);
    }
    exit;
}

if ($stmt->error) {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
    exit;
}

?>
