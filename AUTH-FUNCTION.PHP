<?php
require_once 'config.php';

// User registration
function registerUser($username, $email, $password, $full_name) {
    global $conn;
    
    $username = sanitizeInput($username);
    $email = sanitizeInput($email);
    $full_name = sanitizeInput($full_name);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (username, email, password, full_name) 
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $full_name);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// User login
function loginUser($username, $password) {
    global $conn;
    
    $username = sanitizeInput($username);
    
    $sql = "SELECT user_id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
    }
    return false;
}

// Check if username exists
function usernameExists($username) {
    global $conn;
    
    $sql = "SELECT user_id FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

// Check if email exists
function emailExists($email) {
    global $conn;
    
    $sql = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}
?>