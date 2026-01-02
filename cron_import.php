<?php
header('Content-Type: application/json');

$mysqli = new mysqli("localhost", "root", "", "sirim");
if ($mysqli->connect_errno) {
    die(json_encode(['success' => false, 'message' => 'DB connection failed']));
}

// Load sheet sources
$sources = json_decode(file_get_contents('sheet_sources.json'), true);
if (!is_array($sources)) {
    die(json_encode(['success' => false, 'message' => 'Invalid source config']));
}

$total_inserted = 0;

foreach ($sources as $source) {
    $event_id = $source['event_id'];
    $url = $source['sheet_url'];

    $data = json_decode(file_get_contents($url), true);
    if (!is_array($data)) continue;

    foreach ($data as $row) {
        $name = $row['NAME:'] ?? '';
        $email = $row['EMAIL ADDRESS:'] ?? '';
        $phone = $row['PHONE NUMBER:'] ?? '';
        $position = $row['POSITION:'] ?? '';
        $department = $row['DEPARTMENT:'] ?? '';
        $company = $row['COMPANY NAME:'] ?? '';
        $receipt = $row['RECEIPT'] ?? '';
        $requires_payment = $row['REQUIRES PAYMENT'] ?? '';
        $payment_status = ($requires_payment === 'Yes' && !empty($receipt)) ? 'PAID' : 'UNPAID';

        if (!$email || !$name) continue;

        // Skip duplicates
        $check = $mysqli->prepare("SELECT 1 FROM attendees WHERE email_attendee = ? AND event_id = ?");
        $check->bind_param("si", $email, $event_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) continue;

        // Insert new record
        $stmt = $mysqli->prepare("INSERT INTO attendees (attendee_name, attendee_phonenum, email_attendee, company_name, position_attendee, dept_id, event_id, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssis", $name, $phone, $email, $company, $position, $department, $event_id, $payment_status);
        $stmt->execute();
        $total_inserted++;
    }
}

echo json_encode(['success' => true, 'inserted' => $total_inserted]);
