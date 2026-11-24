<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user is not logged in
if (!isset($_SESSION['username'])) {
    header("Location: LogIn.php");
    exit();
}

$host = "localhost";
$dbname = "stray_safe_db";
$dbuser = "root";
$dbpass = "";

$error = "";
$success = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

$currentUsername = $_SESSION['username'];

// Fetch current user's contact and hashed password
$stmt = $pdo->prepare("SELECT id, username, contact, password FROM users WHERE username = :username LIMIT 1");
$stmt->execute([':username' => $currentUsername]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: LogIn.php");
    exit();
}

// Handle Form Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. CHANGE PASSWORD
    if ($action === 'change_password') {
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';

        if ($oldPassword === '' || $newPassword === '') {
            $error = "Please fill in both password fields.";
        } elseif (!password_verify($oldPassword, $user['password'])) {
            $error = "Old password is incorrect.";
        } elseif (strlen($newPassword) < 6) {
            $error = "New password must be at least 6 characters long.";
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $upd = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
            $upd->execute([':password' => $newHash, ':id' => $user['id']]);
            $user['password'] = $newHash;
            $success = "Password updated successfully!";
        }
    }

    // 2. CHANGE EMAIL / CONTACT
    elseif ($action === 'change_email') {
        $newContact = trim($_POST['new_contact'] ?? '');
        $confirmPass = $_POST['confirm_password'] ?? '';

        if ($newContact === '' || $confirmPass === '') {
            $error = "Please enter your new email/contact and current password.";
        } elseif (!password_verify($confirmPass, $user['password'])) {
            $error = "Password verification failed. Email was not changed.";
        } else {
            $upd = $pdo->prepare("UPDATE users SET contact = :contact WHERE id = :id");
            $upd->execute([':contact' => $newContact, ':id' => $user['id']]);
            $user['contact'] = $newContact;
            $success = "Email / contact updated successfully!";
        }
    }

    // 3. DELETE ACCOUNT
    elseif ($action === 'delete_account') {
        $confirmText = trim($_POST['confirm_delete'] ?? '');

        if ($confirmText !== 'CONFIRM') {
            $error = 'Please type "CONFIRM" in all caps to delete your account.';
        } else {
            $del = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $del->execute([':id' => $user['id']]);

            $_SESSION = [];
            session_destroy();
            header("Location: LogIn.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StraySafe — Settings</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Bitter:wght@700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="settings.css" />
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Decorative Paws -->
    <img src="paws.png" alt="" class="decor-paw paw-right" aria-hidden="true" />
    <img src="paws.png" alt="" class="decor-paw paw-left" aria-hidden="true" />

    <main class="settings-wrapper">
        <!-- Gradient Header Card -->
        <div class="settings-banner">
            <h2>Settings</h2>
            <p>Modify your account</p>
        </div>

        <!-- Feedback Messages -->
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- 3-Column Settings Grid -->
        <section class="settings-grid">
            <!-- Column 1: Change Password -->
            <form action="settings.php" method="POST" class="settings-col">
                <input type="hidden" name="action" value="change_password" />
                <h3>Change Password</h3>

                <div class="field-group">
                    <label for="old_password">Old Password :</label>
                    <input type="password" id="old_password" name="old_password" placeholder="Enter Old Password" required />
                </div>

                <div class="field-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter New Password" required />
                </div>

                <button type="submit" class="btn-olive">Confirm</button>
            </form>

            <!-- Column 2: Change Email -->
            <form action="settings.php" method="POST" class="settings-col">
                <input type="hidden" name="action" value="change_email" />
                <h3>Change Email</h3>

                <div class="field-group">
                    <label for="new_contact">Email: <?php echo htmlspecialchars($user['contact']); ?></label>
                    <input type="text" id="new_contact" name="new_contact" placeholder="Enter new email:" required />
                </div>

                <div class="field-group">
                    <label for="confirm_password">Enter Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Password" required />
                </div>

                <button type="submit" class="btn-olive">Confirm</button>
            </form>

            <!-- Column 3: Delete Account -->
            <form action="settings.php" method="POST" class="settings-col" onsubmit="return confirm('Are you sure you want to permanently delete your account?');">
                <input type="hidden" name="action" value="delete_account" />
                <h3>Delete Account</h3>

                <div class="field-group">
                    <label for="confirm_delete">Type &ldquo; CONFIRM&rdquo; to delete account</label>
                    <input type="text" id="confirm_delete" name="confirm_delete" placeholder="CONFIRM" required />
                </div>

                <button type="submit" class="btn-olive btn-delete">DELETE</button>
            </form>
        </section>
    </main>

    <script>
        // Hamburger dropdown toggle
        const menuBtn = document.getElementById('menu');
        const dropdown = document.getElementById('dropdown');

        if (menuBtn && dropdown) {
            menuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('open');
            });

            document.addEventListener('click', (e) => {
                if (!dropdown.contains(e.target) && !menuBtn.contains(e.target)) {
                    dropdown.classList.remove('open');
                }
            });
        }
    </script>
</body>
</html>