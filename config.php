<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Change if needed
define('DB_PASS', '');      // Change if needed
define('DB_NAME', 'login_system');

// Start session 
if (session_status() === PHP_SESSION_NONE) {   
session_start();
}

// Create connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
