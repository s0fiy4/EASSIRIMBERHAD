<?php
include 'db.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$sql = "SELECT * FROM notification";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $to = $row['send_to'];
    $subject = $row['notification_type'];
    $message = $row['details'];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@gmail.com';
        $mail->Password = 'your_password';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your_email@gmail.com', 'Event Team');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
        echo "Email sent to $to<br>";
    } catch (Exception $e) {
        echo "Mailer Error ({$to}): {$mail->ErrorInfo}<br>";
    }
}
?>
