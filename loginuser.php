<?php
session_start();

$mysqli = new mysqli("localhost", "root", "", "sirim");

if ($mysqli->connect_errno) {
    die("❌ Database connection failed: " . $mysqli->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['user-name'] ?? '';
    $password = $_POST['password'] ?? '';

    // Fetch user by username
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE user_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        // Check password
        if (password_verify($password, $user['password'])) {

            if ($user['is_verified'] == 0) {
                echo "❌ Please verify your email before logging in.";
                exit;
            }

            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['email'] = $user['email'];

            echo "✅ Login successful! Redirecting...";
            header("refresh:1;url=homepage.html"); // Redirect to your homepage
            exit;

        } else {
            echo "❌ Incorrect password.";
        }
    } else {
        echo "❌ Username not found.";
    }

    $stmt->close();
}
$mysqli->close();
?>
