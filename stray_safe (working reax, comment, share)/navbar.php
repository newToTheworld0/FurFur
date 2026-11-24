<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <div id="menu">
        <svg viewBox="0 0 24 24" width="32" height="26" stroke="#737373" stroke-width="2.5" stroke-linecap="round" fill="none">
            <line x1="3" y1="6" x2="21" y2="6" />
            <line x1="3" y1="12" x2="21" y2="12" />
            <line x1="3" y1="18" x2="21" y2="18" />
        </svg>
    </div>

    <div class="menu-area">
        <div id="dropdown">
            <a href="settings.php">SETTINGS</a>
            <a href="logout.php">LOG OUT</a>
        </div>
    </div>

    <img src="paw.png" alt="cyan paw" id="logo">
    <h1>StraySafe</h1>

    <nav>    
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="publish.php">Publish Post</a></li>
            <li><a href="community.php">Community Feed</a></li>
            <li><a href="find.php">Find a pet</a></li>
            <li><a href="about.php">About</a></li>
        </ul>
    </nav>

    <?php if (isset($_SESSION['username'])): ?>
        <div id="quick-action" style="background-color: #0C9488; cursor: default;">
            <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
        </div>
    <?php else: ?>
        <div id="quick-action" onclick="window.location.href='LogIn.php'">
            <p>Log in/Sign up</p>
        </div>
    <?php endif; ?>
</header>