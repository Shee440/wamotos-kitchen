<?php
// Set session save path (adjust this path to where you want to store session data)
ini_set('session.save_path', __DIR__ . '/sessions');  // Save in the "sessions" directory in the current directory

// Start the session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Unset all of the session variables
$_SESSION = array();

// Destroy the session
session_unset();
session_destroy();

// Redirect to the login page
header("Location: ../frontend/login.html");
exit;
?>
