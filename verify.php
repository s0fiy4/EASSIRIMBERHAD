<?php
$mysqli = new mysqli("localhost", "root", "", "sirim");

$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

$stmt = $mysqli->prepare("SELECT * FROM users WHERE email = ? AND verification_token = ?");
$stmt->bind_param("ss", $email, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $update = $mysqli->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE email = ?");
    $update->bind_param("s", $email);
    $update->execute();
    echo "✅ Email verified. You can now log in.";
} else {
    echo "❌ Invalid or expired verification link.";
}
?>
