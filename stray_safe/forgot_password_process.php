<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    if (empty($username) || empty($contact)) {
        header("Location: forgot_password.php?error=Please fill in all fields.");
        exit();
    }

    // DEBUG: Uncomment the line below to see what it's searching for on your screen
    // die("Searching for Username: '$username' and Contact: '$contact'");

    $stmt = $conn->prepare("SELECT id, username, contact FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Let's check if the contact matches (using trim to avoid space issues)
        if (trim($row['contact']) === $contact) {
            $_SESSION['reset_user_id'] = $row['id'];
            $_SESSION['reset_username'] = $row['username'];
            
            $stmt->close();
            $conn->close();

            header("Location: reset_password.php");
            exit();
        } else {
            echo "Database has contact: '" . $row['contact'] . "' but you typed: '" . $contact . "'";
            exit();
        }
    } else {
        echo "Username '$username' not found in database at all!";
        exit();
    }
}
?>