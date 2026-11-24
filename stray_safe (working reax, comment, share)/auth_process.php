<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $contact = trim($_POST['email'] ?? '');

    if (empty($username) || empty($password)) {
        header("Location: LogIn.php?error=Fill in all fields");
        exit;
    }

    // Check if the user is signing up
    if ($action === 'signup') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $contact = trim($_POST['contact'] ?? '');

        if (empty($username) || empty($password) || empty($contact)) {
            header("Location: LogIn.php?error=Fill in all fields");
            exit;
        }
        try {
            // Hash the password securely before saving
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepare the insert statement
            $stmt = $conn->prepare("INSERT INTO users (username, contact, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $contact, $hashed_password);
            
            // Attempt to execute registration
            $stmt->execute();
            $stmt->close();
            
            // Redirect on success
            header("Location: LogIn.php?success=registered");
            exit();

        } catch (mysqli_sql_exception $e) {
            // Error code 1062 means duplicate entry (username already exists)
            if ($e->getCode() == 1062) {
                header("Location: LogIn.php?error=usernametaken");
                exit();
            } else {
                throw $e;
            }
        }
    } 
    // Otherwise, handle Login
    else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password'])) {
                    session_regenerate_id(true);

                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];

                    $stmt->close();
                    header("Location: home.php");
                    exit;
                }
        }
        
        $stmt->close();
        header("Location: LogIn.php?error=Invalid username or password");
        exit;
    }
}
$conn->close();
?>