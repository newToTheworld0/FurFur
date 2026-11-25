<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);
$isRecognitionPage = (strpos($_SERVER['SCRIPT_NAME'], '/pet_recognition/') !== false);
$basePath = $isRecognitionPage ? '../stray_safe/' : '';
$petScanPath = $isRecognitionPage
    ? 'recognition.php'
    : '../pet_recognition/recognition.php';
?>


<header>

    <div id="menu">
        <svg viewBox="0 0 24 24" width="32" height="26" stroke="#737373"
             stroke-width="2.5" stroke-linecap="round" fill="none">
            <line x1="3" y1="6" x2="21" y2="6" />
            <line x1="3" y1="12" x2="21" y2="12" />
            <line x1="3" y1="18" x2="21" y2="18" />
        </svg>
    </div>

    <div class="menu-area">
        <div id="dropdown">
            <a href="<?= $basePath ?>settings.php">SETTINGS</a>
            <a href="<?= $basePath ?>logout.php">LOG OUT</a>
        </div>
    </div>

    <img src="<?= $basePath ?>paw.png" alt="cyan paw" id="logo">
    <h1>StraySafe</h1>

    <nav>
        <ul>
            <li>
                <a href="<?= $basePath ?>home.php"
                   class="<?= ($currentPage === 'home.php') ? 'active-nav' : '' ?>">
                    Home
                </a>
            </li>

            <li>
                <a href="<?= $basePath ?>publish.php"
                   class="<?= ($currentPage === 'publish.php') ? 'active-nav' : '' ?>">
                    Publish Post
                </a>
            </li>

            <li>
                <a href="<?= $basePath ?>community.php"
                   class="<?= ($currentPage === 'community.php') ? 'active-nav' : '' ?>">
                    Community Feed
                </a>
            </li>

            <li>
                <a href="<?= $basePath ?>find.php"
                   class="<?= ($currentPage === 'find.php') ? 'active-nav' : '' ?>">
                    Find a Pet
                </a>
            </li>

            <li>
                <a href="<?= $basePath ?>../pet_recognition/recognition.php"
                class="<?= ($currentPage === 'recognition.php') ? 'active-nav' : '' ?>">
                    PetScan
                </a>
            </li>

            <li>
                <a href="<?= $basePath ?>about.php"
                class="<?php echo (strpos($currentPage, 'about') !== false) ? 'active-nav' : ''; ?>">
                    About
                </a>
            </li>
        </ul>
    </nav>

    <?php if (isset($_SESSION['username'])): ?>

        <div
            id="quick-action"
            class="<?= ($currentPage === 'profile.php') ? 'active-profile' : '' ?>"
            style="background-color:#0C9488;"
            onclick="window.location.href='profile.php'"
        >
            <p>
                <?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?>
            </p>
        </div>

    <?php else: ?>

        <div id="quick-action"
             onclick="window.location.href='LogIn.php'">
            <p>Log in/Sign up</p>
        </div>

    <?php endif; ?>

</header>
