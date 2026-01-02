<?php

// Database connection
$servername = "localhost";
$username = "root";  // your MySQL username
$password = "";      // your MySQL password
$dbname = "sirim";  // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $user_name = $_POST['user-name'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO USERS (username, phone, email, password) VALUES (?, ?, ?, ?)");

    // Check if the statement was prepared successfully
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("ssss", $user_name, $phone, $email, $hashed_password);

    // Execute the prepared statement
    if ($stmt->execute()) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();
?>