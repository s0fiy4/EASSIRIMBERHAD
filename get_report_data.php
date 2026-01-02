<?php
// Set correct content type
header('Content-Type: application/json');

// Database credentials
$host = 'localhost';
$db   = 'sirim';
$user = 'root'; // change if needed
$pass = '';     // change if needed
$charset = 'utf8mb4';

// Connect using PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database connection failed.']);
    exit;
}

// Validate input dates
if (!isset($_GET['from']) || !isset($_GET['to'])) {
    echo json_encode(['error' => 'Missing date parameters.']);
    exit;
}

$from = $_GET['from'];
$to   = $_GET['to'];

// Prepare SQL query
$sql = "
    SELECT e.event_id, e.event_name, e.date, e.location, e.organized_by,
           COUNT(a.attendee_id) AS numAttendees
    FROM events e
    LEFT JOIN attendee a ON e.event_id = a.event_id
    WHERE e.date BETWEEN :from AND :to
    GROUP BY e.event_id, e.event_name, e.date, e.location, e.organized_by
    ORDER BY e.date ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['from' => $from, 'to' => $to]);

$results = $stmt->fetchAll();

echo json_encode($results);




