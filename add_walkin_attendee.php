<?php
require_once 'db.php';
require_once 'phpqrcode/qrlib.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

$event_id       = $_POST['event_id'] ?? '';
$attendee_name  = $_POST['attendee_name'] ?? '';
$email          = $_POST['email_attendee'] ?? '';
$phone          = $_POST['attendee_phonenum'] ?? '';
$company        = $_POST['company_name'] ?? '';
$position       = $_POST['position_attendee'] ?? '';
$department     = $_POST['dept_id'] ?? $_POST['department_attendee'] ?? $_POST['department'] ?? '';
$status         = 'Present';  //  it's case-sensitive
$is_walkin      = 1;

// Log all received input
error_log("🚀 Received walk-in data: event_id=$event_id | name=$attendee_name | email=$email | phone=$phone | position=$position | dept=$department");

// Basic validation
if (!$event_id || !$attendee_name || !$email) {
    error_log("❌ Missing required fields");
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_log("❌ Invalid email format: $email");
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

// Check for duplicate attendee
$check_stmt = $conn->prepare("SELECT 1 FROM attendees WHERE email_attendee = ? AND event_id = ?");
$check_stmt->bind_param("si", $email, $event_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
if ($result->num_rows > 0) {
    error_log("❗ Duplicate attendee found: $email already registered for event $event_id");
    echo json_encode(["status" => "error", "message" => "Attendee with this email already exists for this event."]);
    exit;
}
$check_stmt->close();

// Insert attendee
$insert_sql = "INSERT INTO attendees (
    event_id, attendee_name, email_attendee, attendee_phonenum, company_name,
    dept_id, position_attendee, attendance_status, is_walkin, attendance_time
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($insert_sql);
if (!$stmt) {
    error_log("❌ Prepare failed: " . $conn->error);
    echo json_encode(["status" => "error", "message" => "Prepare failed"]);
    exit;
}

$stmt->bind_param("isssssssi", $event_id, $attendee_name, $email, $phone, $company, $department, $position, $status, $is_walkin);

if ($stmt->execute()) {
    error_log("✅ Walk-in attendee inserted successfully: $attendee_name ($email)");

    // Generate QR Code
    $qr_dir = 'qr_codes/';
    if (!file_exists($qr_dir)) {
        mkdir($qr_dir, 0755, true);
    }

    $qr_url = "http://localhost/attendancerecording.html?event_id={$event_id}&email={$email}";
    $filename = $qr_dir . "walkin_" . uniqid() . ".png";
    QRcode::png($qr_url, $filename, QR_ECLEVEL_L, 4);

    echo json_encode([
        "success" => true,
        "qr_code" => $filename,
        "attendee_email" => $email
    ]);
} else {
    error_log("❌ Insert failed: " . $stmt->error);
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
}

$stmt->close();
$conn->close();












