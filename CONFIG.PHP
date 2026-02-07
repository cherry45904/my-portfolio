<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nepal_unrevealed');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Start session
session_start();

// Function to sanitize input data
function sanitizeInput($data) {
    global $conn;
    return htmlspecialchars(strip_tags(trim($conn->real_escape_string($data)));
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user ID
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>