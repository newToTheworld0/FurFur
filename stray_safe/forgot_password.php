<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>StraySafe — Forgot Password</title>
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
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: var(--green-dark);
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="reset-container">
        <h2>Reset Password</h2>
        <p>Enter your username and contact/email to verify your account.</p>

        <?php if (isset($_GET['error'])): ?>
            <p style="color: #c53030; font-size: 0.85rem; margin-bottom: 1rem;"><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>

        <form action="forgot_password_process.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Enter your username">
            </div>
            <div class="form-group">
                <label>Email / Contact No.</label>
                <!-- Notice name="contact" matches the database column -->
                <input type="text" name="contact" required placeholder="Enter your registered email or contact">
            </div>
            <button type="submit" class="btn-submit">Verify Account</button>
        </form>

        <a href="LogIn.php" class="back-link">← Back to Log In</a>
    </div>

</body>
</html>