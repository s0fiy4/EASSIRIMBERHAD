<?php
// CONFIG
$sheetName = 'Form Responses 1';
$apiKey='';

// Step 1: Get event_id from URL
$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
if ($eventId === 0) {
    die("❌ No event ID provided.");
}

// Step 2: Connect to DB
$conn = new mysqli('localhost', 'root', '', 'sirim');
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

// Step 3: Get Google Sheet ID AND API key from the events table
$stmt = $conn->prepare("SELECT google_sheet_id, api_key FROM events WHERE event_id = ?");
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();
$stmt->close();

if (!$event || empty($event['google_sheet_id']) || empty($event['api_key'])) {
    die("❌ Missing Sheet ID or API key for event ID $eventId");
}

$spreadsheetId = $event['google_sheet_id'];
$apiKey = $event['api_key'];


// Step 4: Fetch Google Sheet data
$url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheetId/values/" . urlencode($sheetName) . "?key=$apiKey";
$response = file_get_contents($url);
$data = json_decode($response, true);

if (!isset($data['values'])) {
    die("❌ Failed to fetch or parse sheet data.");
}

$rows = $data['values'];
$headers = array_shift($rows); // Remove header row

$updated = 0;

foreach ($rows as $row) {
    $email = isset($row[3]) ? trim(strtolower($row[3])) : '';  // "EMAIL ADDRESS:"
    $receipt = isset($row[8]) ? trim($row[8]) : '';            // "PAYMENT:" (Google Drive link)

    if (!empty($email) && !empty($receipt)) {
        // Update attendee record for this event and email
        $update = $conn->prepare("
            UPDATE attendees 
            SET payment_status = 'PAID', receipt = ?, payment_date = CURDATE()
            WHERE LOWER(email_attendee) = ? AND event_id = ?
        ");
        $update->bind_param("ssi", $receipt, $email, $eventId);
        $update->execute();
        if ($update->affected_rows > 0) $updated++;
        $update->close();
    }
}

$conn->close();

echo "✅ Sync complete. $updated attendee(s) marked as PAID for event ID $eventId.\n";
?>




