<?php
require('fpdf/fpdf.php');

// DB config
$pdo = new PDO('mysql:host=localhost;dbname=sirim', 'root', '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $event_id = $_POST['event_id'] ?? '';

    $stmt = $pdo->prepare("
        SELECT a.*, e.event_name, e.event_start_date 
        FROM attendees a 
        JOIN events e ON a.event_id = e.event_id 
        WHERE a.email_attendee = ? AND a.event_id = ?
    ");
    $stmt->execute([$email, $event_id]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // ✅ Attendee found
        $name = $row['attendee_name'];
        $event = $row['event_name'];
        $date = $row['event_start_date'];

        class PDF extends FPDF {
            function Header() {
                $this->Image('images/LOGOSIRIM.jpg', 10, 6, 30); // top-left logo
            }

            function Footer() {
                $this->SetY(-20);
                if (file_exists('images/signature.png')) {
                    $this->Image('images/signature.png', 230, 160, 40); // digital signature
                }
                $this->SetFont('Arial', 'I', 10);
                $this->Cell(0, 10, 'Authorized by SIRIM Berhad', 0, 0, 'C');
            }
        }

        $pdf = new PDF('L', 'mm', 'A4');
        $pdf->AddPage();

        // Certificate styling
        $pdf->SetFont('Arial', 'B', 28);
        $pdf->Cell(0, 30, 'Certificate of Attendance', 0, 1, 'C');

        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 15, 'This is to certify that', 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 26);
        $pdf->Cell(0, 20, strtoupper($name), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 18);
        $pdf->Cell(0, 12, 'has attended', 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 22);
        $pdf->Cell(0, 16, $event, 0, 1, 'C');

        $pdf->SetFont('Arial', '', 16);
        $pdf->Cell(0, 14, 'on ' . date('F j, Y', strtotime($date)), 0, 1, 'C');

        // Output PDF
        $fileName = "certificate_" . $event_id . "_" . $email . ".pdf";
        $pdf->Output('I', $fileName); // Inline view in browser
        exit;

    } else {
        echo "❌ No certificate found for this email and event ID.";
    }
}
?>
