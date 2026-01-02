<?php
// Database connection
$servername = "localhost";
$username = "root";  // your MySQL username
$password = "";      // your MySQL password
$dbname = "sirim";   // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    // Check if user exists in the database
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ? OR email = ?");
    
    if ($stmt === false) {
        die('Error in query preparation: ' . $conn->error);
    }

    $stmt->bind_param("ss", $input_username, $input_username);  // Bind parameters
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // User exists, fetch the hashed password
        $stmt->bind_result($hashed_password);
        $stmt->fetch();

        // Verify the provided password with the hashed password
        if (password_verify($input_password, $hashed_password)) {
            echo "Login successful! Welcome, " . htmlspecialchars($input_username);
            
            // Redirect to homepage after successful login
            header("Location: homepage.html");  // Change 'homepage.html' to your homepage URL
            exit;  // Don't forget to exit after the redirect
        } else {
            echo "Invalid password. Please try again.";
        }
    } else {
        echo "No account found with that username or email.";
    }

    $stmt->close();
}

// Close the connection
$conn->close();
?>
