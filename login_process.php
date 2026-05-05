<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        header("Location: index.php?error=Please fill in all fields");
        exit();
    }
    
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT id, username, password, created_at FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
$user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['created_at'] = $user['created_at'];
            $_SESSION['logged_in'] = true;
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: index.php?error=Invalid password");
            exit();
        }
    } else {
        header("Location: index.php?error=User not found");
        exit();
    }
    
    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>
