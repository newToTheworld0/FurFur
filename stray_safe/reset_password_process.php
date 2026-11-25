<?php
session_start();
require 'db.php';

if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        header("Location: reset_password.php?error=Please fill in all fields.");
        exit();
    }

    if ($new_password !== $confirm_password) {
        header("Location: reset_password.php?error=Passwords do not match.");
        exit();
    }

    // Hash the new password securely
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $user_id = $_SESSION['reset_user_id'];

    // Update database
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    if ($stmt->execute()) {
        // Clear reset session variables
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['reset_username']);
        
        $stmt->close();
        $conn->close();

        header("Location: LogIn.php?success=passwordchanged");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: reset_password.php?error=Database error. Try again.");
        exit();
    }
}
?>