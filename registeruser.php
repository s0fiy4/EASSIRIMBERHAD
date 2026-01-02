<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';


$mysqli = new mysqli("localhost", "root", "", "sirim");
if ($mysqli->connect_errno) {
    die("DB connection failed.");
}

$username = $_POST['username'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(16));
$is_verified = 0;

// Save user
$stmt = $mysqli->prepare("INSERT INTO users (username, phone, email, password, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssi", $username, $phone, $email, $password, $token, $is_verified);
$stmt->execute();

// Send verification email
$verifyLink = "http://localhost/verify.php?email=" . urlencode($email) . "&token=" . $token;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'sofiyashukri022@gmail.com'; 
    $mail->Password = 'wshf wpiv xbjd xpqr'; 
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('sofiyashukri022@gmail.com', 'SIRIM Registration');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "Verify your SIRIM account";
    $mail->Body = "Hi $username,<br><br>Please click this link to verify your account:<br><a href='$verifyLink'>$verifyLink</a>";

    $mail->send();
    echo "✅ Registered successfully. Please verify your email.";
} catch (Exception $e) {
    echo "❌ Email failed: {$mail->ErrorInfo}";
}
?>
