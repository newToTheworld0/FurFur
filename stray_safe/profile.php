<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: LogIn.php");
    exit();
}

$username = $_SESSION['username'];

$stmt = $conn->prepare("
    SELECT id, username, contact
    FROM users
    WHERE username = ?
");

$stmt->bind_param("s", $username);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

$userId = (int)$user['id'];

/* MY POSTS */

$postStmt = $conn->prepare("
    SELECT *
    FROM posts
    WHERE username = ?
    ORDER BY id DESC
");

$postStmt->bind_param("s", $username);
$postStmt->execute();

$posts = $postStmt->get_result();

/* SAVED POSTS */

$savedStmt = $conn->prepare("
    SELECT p.*
    FROM post_bookmarks pb
    JOIN posts p ON pb.post_id = p.id
    WHERE pb.user_id = ?
    ORDER BY pb.created_at DESC
");

$savedStmt->bind_param(
    "i",
    $userId
);

$savedStmt->execute();

$savedPosts = $savedStmt->get_result();

/* LIKED POSTS */

$likedStmt = $conn->prepare("
    SELECT p.*
    FROM post_likes pl
    JOIN posts p ON pl.post_id = p.id
    WHERE pl.user_id = ?
    ORDER BY pl.id DESC
");

$likedStmt->bind_param(
    "i",
    $userId
);

$likedStmt->execute();

$likedPosts = $likedStmt->get_result();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - StraySafe</title>

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="profile.css">
</head>

<body>

<?php include 'navbar.php'; ?>

<main class="profile-wrapper">

    <section class="profile-banner">

        <div class="profile-cover">
            <h2>Profile Banner</h2>
        </div>

        <div class="profile-info">

            <div class="profile-left">

                <div class="profile-picture">
                    <img src="paw.png" alt="Profile">
                </div>

                <div class="user-details">
                    <h2>
                        <?php echo htmlspecialchars($user['username']); ?>
                    </h2>

                    <p class="username-tag">
                        @<?php echo htmlspecialchars($user['username']); ?>
                    </p>

                    <p class="bio-text">
                        Welcome to my StraySafe profile.
                    </p>
                </div>

            </div>

            <button class="edit-profile-btn">
                Edit Profile
            </button>

        </div>

    </section>

    <section class="profile-content">

        <aside class="about-card">

            <h3>About Me</h3>

            <p class="about-text">
                Community animal lover and supporter.
            </p>

            <div class="info-group">
                <strong>Contact</strong>
                <p><?php echo htmlspecialchars($user['contact']); ?></p>
            </div>

            <div class="info-group">
                <strong>Location</strong>
                <p>Pampanga, Philippines</p>
            </div>

        </aside>

        <section class="posts-area">

            <div class="profile-tabs">

                <button class="tab-btn active"
                        onclick="showTab('posts', this)">
                    My Posts
                </button>

                <button class="tab-btn"
                        onclick="showTab('liked', this)">
                    Liked
                </button>

                <button class="tab-btn"
                        onclick="showTab('saved', this)">
                    Saved
                </button>

            </div>

            <div id="posts-tab" class="tab-content">

                <div class="post-grid">

                    <?php while($post = $posts->fetch_assoc()): ?>

                        <?php
                            $images = json_decode($post['images'], true);

                            $image = (!empty($images) && isset($images[0]))
                                ? $images[0]
                                : 'paw.png';
                        ?>

                        <div class="profile-post-card">

                            <img
                                src="<?php echo htmlspecialchars($image); ?>"
                                alt="Animal"
                            >

                            <div class="card-info">

                                <span class="animal-badge">
                                    <?php echo htmlspecialchars($post['animal_type']); ?>
                                </span>

                                <h3>
                                    <?php echo htmlspecialchars($post['animal_type']); ?>
                                </h3>

                                <p class="post-location">
                                    <?php echo htmlspecialchars($post['location_text']); ?>
                                </p>

                                <div class="description-box">
                                    <?php echo htmlspecialchars($post['description']); ?>
                                </div>

                                <a
                                    href="community.php#post-<?php echo $post['id']; ?>"
                                    class="view-post-btn"
                                >
                                    View Post
                                </a>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            </div>

           <div id="liked-tab" class="tab-content" style="display:none;">

                    <div class="post-grid">

                    <?php while($post = $likedPosts->fetch_assoc()): ?>

                        <?php
                            $images = json_decode($post['images'], true);

                            $image = (!empty($images) && isset($images[0]))
                                ? $images[0]
                                : 'paw.png';
                        ?>

                        <div class="profile-post-card">

                            <img
                                src="<?php echo htmlspecialchars($image); ?>"
                                alt="Animal"
                            >

                            <div class="card-info">

                                <span class="animal-badge">
                                    <?php echo htmlspecialchars($post['animal_type']); ?>
                                </span>

                                <h3>
                                    <?php echo htmlspecialchars($post['animal_type']); ?>
                                </h3>

                                <p class="post-location">
                                    <?php echo htmlspecialchars($post['location_text']); ?>
                                </p>

                                <div class="description-box">
                                    <?php echo htmlspecialchars($post['description']); ?>
                                </div>

                                <a
                                    href="community.php#post-<?php echo $post['id']; ?>"
                                    class="view-post-btn"
                                >
                                    View Post
                                </a>

                            </div>

                        </div>

                <?php endwhile; ?>

                </div>

            </div>

            <div id="saved-tab" class="tab-content" style="display:none;">

    <div class="post-grid">

    <?php while($post = $savedPosts->fetch_assoc()): ?>

        <?php
            $images = json_decode($post['images'], true);

            $image = (!empty($images) && isset($images[0]))
                ? $images[0]
                : 'paw.png';
        ?>

        <div class="profile-post-card">

            <img
                src="<?php echo htmlspecialchars($image); ?>"
                alt="Animal"
            >

            <div class="card-info">

                <span class="animal-badge">
                    <?php echo htmlspecialchars($post['animal_type']); ?>
                </span>

                <h3>
                    <?php echo htmlspecialchars($post['animal_type']); ?>
                </h3>

                <p class="post-location">
                    <?php echo htmlspecialchars($post['location_text']); ?>
                </p>

                <div class="description-box">
                    <?php echo htmlspecialchars($post['description']); ?>
                </div>

                <a
                    href="community.php#post-<?php echo $post['id']; ?>"
                    class="view-post-btn"
                >
                    View Post
                </a>

            </div>

        </div>

    <?php endwhile; ?>

    </div>

</div>

        </section>

    </section>

</main>

<script>

function showTab(tabName, button){

    document.querySelectorAll('.tab-content')
        .forEach(tab => {
            tab.style.display = 'none';
        });

    document.querySelectorAll('.tab-btn')
        .forEach(btn => {
            btn.classList.remove('active');
        });

    document.getElementById(tabName + '-tab').style.display = 'block';

    button.classList.add('active');
}

</script>

<script src="script.js"></script>

</body>
</html>