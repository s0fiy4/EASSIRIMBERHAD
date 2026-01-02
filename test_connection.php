<?php
$conn = new mysqli("localhost", "root", "", "sirim");

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
echo "✅ Connected to MySQL successfully!";
?>
