<?php
session_start();
// Prevent direct access if verification hasn't passed
if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>StraySafe — Set New Password</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home.css">
    <style>
        .reset-container {
            width: 100%;
            max-width: 450px;
            margin: 4rem auto;
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 16px;
            border: 1px solid var(--border-soft);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .reset-container h2 {
            color: var(--green-dark);
            margin-bottom: 0.5rem;
            font-family: Georgia, serif;
        }
        .reset-container p {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.4rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid #b8c2cc;
            border-radius: 10px;
            font-size: 0.92rem;
        }
        .btn-submit {
            width: 100%;
            background-color: var(--btn-olive);
            color: white;
            border: none;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            margin-top: 1rem;
        }
        .btn-submit:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

    <div class="reset-container">
        <h2>Create New Password</h2>
        <p>Hello <strong><?= htmlspecialchars($_SESSION['reset_username']) ?></strong>, please enter your new password below.</p>

        <?php if (isset($_GET['error'])): ?>
            <p style="color: #c53030; font-size: 0.85rem; margin-bottom: 1rem;"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <form action="reset_password_process.php" method="POST">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required placeholder="Enter new password">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required placeholder="Confirm new password">
            </div>
            <button type="submit" class="btn-submit">Update Password</button>
        </form>
    </div>

</body>
</html>