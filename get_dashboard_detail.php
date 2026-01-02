<?php
$pdo = new PDO('mysql:host=localhost;dbname=sirim', 'root', '');
$type = $_GET['type'] ?? '';
$month = $_GET['month'] ?? '';

$whereClause = '';
$params = [];

if ($month) {
    $whereClause = "AND DATE_FORMAT(event_start_date, '%Y-%m') = ?";
    $params[] = $month;
}

switch ($type) {
  case 'payments':
    $stmt = $pdo->prepare("
      SELECT e.event_name, SUM(e.payment_amount) AS total
      FROM attendees a
      JOIN events e ON a.event_id = e.event_id
      WHERE a.payment_status = 'Paid' AND e.payment_required = 1 AND a.payment_date IS NOT NULL
      " . ($month ? " AND DATE_FORMAT(a.payment_date, '%Y-%m') = ?" : "") . "
      GROUP BY e.event_id
    ");
    $stmt->execute($month ? [$month] : []);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<ul class='space-y-2'>";
    foreach ($rows as $row) {
        echo "<li><strong>{$row['event_name']}</strong> — RM " . number_format($row['total'], 2) . "</li>";
    }
    echo "</ul>";
    break;

  case 'events':
    $stmt = $pdo->prepare("SELECT event_name, event_start_date, organized_by FROM events WHERE 1 $whereClause ORDER BY event_start_date");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<ul class='space-y-2'>";
    foreach ($rows as $row) {
        echo "<li><strong>{$row['event_name']}</strong> — {$row['event_start_date']} by {$row['organized_by']}</li>";
    }
    echo "</ul>";
    break;

  case 'attendees':
    $stmt = $pdo->prepare("
      SELECT a.attendee_name, a.email_attendee, e.event_name 
      FROM attendees a 
      JOIN events e ON a.event_id = e.event_id 
      WHERE 1 " . ($month ? " AND DATE_FORMAT(e.event_start_date, '%Y-%m') = ?" : ""));
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<ul class='space-y-2'>";
    foreach ($rows as $row) {
        echo "<li><strong>{$row['attendee_name']}</strong> ({$row['email_attendee']}) — Event: {$row['event_name']}</li>";
    }
    echo "</ul>";
    break;

  case 'average':
    $stmt = $pdo->prepare("
      SELECT e.event_name, COUNT(a.attendee_id) AS total_attendees 
      FROM events e 
      LEFT JOIN attendees a ON a.event_id = e.event_id 
      WHERE 1 $whereClause 
      GROUP BY e.event_id
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<ul class='space-y-2'>";
    foreach ($rows as $row) {
        echo "<li><strong>{$row['event_name']}</strong> — {$row['total_attendees']} Attendees</li>";
    }
    echo "</ul>";
    break;

    case 'location':
    $location = $_GET['location'] ?? '';
    $sql = "SELECT event_name, event_start_date, organized_by 
            FROM events 
            WHERE event_location = ?";
    if ($month) {
        $sql .= " AND DATE_FORMAT(event_start_date, '%Y-%m') = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$location, $month]);
    } else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$location]);
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<ul class='space-y-2'>";
    foreach ($rows as $row) {
        echo "<li><strong>{$row['event_name']}</strong> — {$row['event_start_date']} by {$row['organized_by']}</li>";
    }
    echo "</ul>";
    break;
    
  default:
    echo "<p class='text-gray-500'>No details available.</p>";
}
?>
