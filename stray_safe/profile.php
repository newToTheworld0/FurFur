<?php
session_start();
require 'db.php';

if (!isset($_SESSION['username'])) {
    header("Location: LogIn.php");
    exit();
}

$username = $_SESSION['username'];

$stmt = $conn->prepare("
    SELECT id, username, contact, bio, about_me, location, avatar
    FROM users
    WHERE username = ?
");

$stmt->bind_param("s", $username);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: LogIn.php");
    exit();
}

$userId = (int)$user['id'];
$userBio = !empty($user['bio']) ? $user['bio'] : 'Welcome to my StraySafe profile.';
$userAbout = !empty($user['about_me']) ? $user['about_me'] : 'Community animal lover and supporter.';
$userLocation = !empty($user['location']) ? $user['location'] : 'Pampanga, Philippines';
$userAvatar = !empty($user['avatar']) ? $user['avatar'] : 'paw.png';

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
    <style>
        .profile-cover {
            position: relative !important;
            z-index: 1 !important;
        }

        .profile-info {
            position: relative !important;
            z-index: 2 !important;
        }

        .profile-left {
            display: flex !important;
            align-items: center !important;
            gap: 1.25rem !important;
            overflow: visible !important;
        }

        .profile-picture {
            width: 115px !important;
            height: 115px !important;
            min-width: 115px !important;
            min-height: 115px !important;
            aspect-ratio: 1 / 1 !important;
            border-radius: 50% !important;
            overflow: hidden !important;
            flex-shrink: 0 !important;
            position: relative !important;
            top: 0 !important;
            left: 0 !important;
            transform: none !important;
            margin: 0 !important;
            z-index: 5 !important;
            background-color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
            padding: 0 !important;
        }

        .profile-picture img {
            width: 100% !important;
            height: 100% !important;
            max-width: none !important;
            max-height: none !important;
            object-fit: cover !important;
            object-position: center !important;
            border-radius: 50% !important;
            position: static !important;
            transform: none !important;
            margin: 0 !important;
            display: block !important;
        }

        .edit-modal-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .edit-modal-card {
            background: #ffffff;
            border-radius: 18px;
            width: 100%;
            max-width: 480px;
            padding: 1.75rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.18);
            position: relative;
        }

        .edit-modal-close {
            position: absolute;
            top: 14px;
            right: 18px;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #374151;
        }

        .edit-modal-form {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .avatar-picker-row {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .avatar-preview-img {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #0c9488;
            background: #f1f5f9;
            flex-shrink: 0;
        }

        .edit-field-label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
            color: #111827;
        }

        .edit-input,
        .edit-textarea {
            width: 100%;
            padding: 0.55rem 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.95rem;
            color: #1f2937;
        }

        .edit-textarea {
            resize: vertical;
        }

        .edit-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.65rem;
            margin-top: 0.5rem;
        }

        .btn-cancel-edit {
            padding: 0.55rem 1rem;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #374151;
        }

        .btn-save-edit {
            padding: 0.55rem 1.2rem;
            border: none;
            background: #0c9488;
            color: #ffffff;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-save-edit:hover {
            background: #0a7a70;
        }
    </style>
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
                    <img id="displayAvatar" src="<?php echo htmlspecialchars($userAvatar); ?>" alt="Profile">
                </div>

                <div class="user-details">
                    <h2>
                        <?php echo htmlspecialchars($user['username']); ?>
                    </h2>

                    <p class="username-tag">
                        @<?php echo htmlspecialchars($user['username']); ?>
                    </p>

                    <p class="bio-text" id="displayBio">
                        <?php echo htmlspecialchars($userBio); ?>
                    </p>
                </div>

            </div>

            <button type="button" class="edit-profile-btn" onclick="openEditProfileModal()">
                Edit Profile
            </button>

        </div>

    </section>

    <section class="profile-content">

        <aside class="about-card">

            <h3>About Me</h3>

            <p class="about-text" id="displayAbout">
                <?php echo nl2br(htmlspecialchars($userAbout)); ?>
            </p>

            <div class="info-group">
                <strong>Contact</strong>
                <p id="displayContact"><?php echo htmlspecialchars($user['contact']); ?></p>
            </div>

            <div class="info-group">
                <strong>Location</strong>
                <p id="displayLocation"><?php echo htmlspecialchars($userLocation); ?></p>
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

<div id="editProfileModal" class="edit-modal-backdrop">
    <div class="edit-modal-card">
        <button type="button" class="edit-modal-close" onclick="closeEditProfileModal()" aria-label="Close">&times;</button>
        <h2 style="margin-bottom:1rem; color:#111827;">Edit Profile</h2>

        <form id="editProfileForm" class="edit-modal-form" enctype="multipart/form-data">
            <div class="avatar-picker-row">
                <img id="avatarPreview" src="<?php echo htmlspecialchars($userAvatar); ?>" alt="Avatar preview" class="avatar-preview-img" />
                <div>
                    <label for="avatarInput" class="edit-field-label">Profile Picture</label>
                    <input type="file" id="avatarInput" name="avatar" accept="image/*" />
                </div>
            </div>

            <div>
                <label for="inputBio" class="edit-field-label">Bio</label>
                <input type="text" name="bio" id="inputBio" class="edit-input" value="<?php echo htmlspecialchars($userBio); ?>" maxlength="255" />
            </div>

            <div>
                <label for="inputAbout" class="edit-field-label">About Me</label>
                <textarea name="about_me" id="inputAbout" rows="3" class="edit-textarea"><?php echo htmlspecialchars($userAbout); ?></textarea>
            </div>

            <div>
                <label for="inputContact" class="edit-field-label">Contact</label>
                <input type="text" name="contact" id="inputContact" class="edit-input" value="<?php echo htmlspecialchars($user['contact']); ?>" required />
            </div>

            <div>
                <label for="inputLocation" class="edit-field-label">Location</label>
                <input type="text" name="location" id="inputLocation" class="edit-input" value="<?php echo htmlspecialchars($userLocation); ?>" />
            </div>

            <div class="edit-modal-actions">
                <button type="button" class="btn-cancel-edit" onclick="closeEditProfileModal()">Cancel</button>
                <button type="submit" class="btn-save-edit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

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

function openEditProfileModal() {
    document.getElementById('editProfileModal').style.display = 'flex';
}

function closeEditProfileModal() {
    document.getElementById('editProfileModal').style.display = 'none';
}

document.getElementById('editProfileModal').addEventListener('click', function (e) {
    if (e.target === this) {
        closeEditProfileModal();
    }
});

document.getElementById('avatarInput').addEventListener('change', function () {
    const file = this.files && this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (ev) {
        document.getElementById('avatarPreview').src = ev.target.result;
    };
    reader.readAsDataURL(file);
});

document.getElementById('editProfileForm').addEventListener('submit', async function (e) {
    e.preventDefault();
    const fd = new FormData(this);

    const res = await fetch('update_profile.php', {
        method: 'POST',
        body: fd
    });
    const result = await res.json();

    if (!result.success) {
        alert(result.message || 'Could not update profile.');
        return;
    }

    document.getElementById('displayBio').textContent = result.data.bio;
    document.getElementById('displayAbout').textContent = result.data.about_me;
    document.getElementById('displayContact').textContent = result.data.contact;
    document.getElementById('displayLocation').textContent = result.data.location;
    document.getElementById('displayAvatar').src = result.data.avatar;
    document.getElementById('avatarPreview').src = result.data.avatar;

    closeEditProfileModal();
});

</script>

<script src="script.js"></script>

</body>
</html>