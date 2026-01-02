<?php
include 'db.php';

$event_id = $_GET['event_id'] ?? '';
$email = $_GET['email'] ?? '';

if (!$event_id) {
    echo "<p>Please provide an event ID in the URL, e.g., attendance_recording.php?event_id=123</p>";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM attendees WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Optional: mark attendance in database
    $update = $conn->prepare("UPDATE attendees SET attendance_status = 'Present' WHERE event_id = ? AND email_attendee = ?");
    $update->bind_param("is", $event_id, $email);
    $update->execute();
    
    // Redirect to HTML with parameters (simulate scan result)
    $query = http_build_query([
        'attendee_id' => $row['attendee_id'],
        'attendee_name' => $row['attendee_name'],
        'dept_id' => $row['dept_id'],
        'attendee_phonenum' => $row['attendee_phonenum'],
        'email' => $row['email_attendee'],
        'company_name' => $row['company_name'],
    ]);
    header("Location: attendancerecording.html?$query");
    exit;
} else {
    echo "Attendee not found.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendance Recording</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .present { background-color: #d4edda; }
        .absent { background-color: #f8d7da; }
    </style>
</head>
<body>
<h2>Attendance Recording for Event ID: <?= htmlspecialchars($event_id) ?></h2>

<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Company</th>
            <th>Status</th>
            <th>Mark</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr class="<?= $row['attendance_status'] === 'Present' ? 'present' : ($row['attendance_status'] === 'Absent' ? 'absent' : '') ?>">
            <td><?= htmlspecialchars($row['attendee_name']) ?></td>
            <td><?= htmlspecialchars($row['email_attendee']) ?></td>
            <td><?= htmlspecialchars($row['company_name']) ?></td>
            <td><?= $row['attendance_status'] ?: 'Not Marked' ?></td>
            <td>
                <form method="POST" action="mark_attendance.php" style="display:inline-block">
                    <input type="hidden" name="event_id" value="<?= $event_id ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($row['email_attendee']) ?>">
                    <input type="hidden" name="status" value="Present">
                    <button type="submit">Present</button>
                </form>
                <form method="POST" action="mark_attendance.php" style="display:inline-block">
                    <input type="hidden" name="event_id" value="<?= $event_id ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($row['email_attendee']) ?>">
                    <input type="hidden" name="status" value="Absent">
                    <button type="submit">Absent</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
</body>
</html>
<?php $conn->close(); ?>
