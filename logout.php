<?php
// Start the session
session_start();

// Destroy the session to log the user out
session_unset();  // Remove all session variables
session_destroy();  // Destroy the session

// Redirect to the homepage or login page
header("Location: login.html");  // Redirect to login page or homepage
exit();
?>