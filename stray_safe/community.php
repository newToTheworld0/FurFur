<?php
session_start();
require_once 'db.php';

$currentUsername = isset($_SESSION['username']) ? trim((string)$_SESSION['username']) : '';
$currentUserId = 0;

$posts = [];
$commentsByPost = [];

if (isset($conn) && ($conn instanceof mysqli) && !$conn->connect_error) {
    if ($currentUsername !== '') {
        $uStmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $uStmt->bind_param("s", $currentUsername);
        $uStmt->execute();
        $uRow = $uStmt->get_result()->fetch_assoc();
        $uStmt->close();
        if ($uRow) {
            $currentUserId = (int)$uRow['id'];
        }
    }

    $sql = "
        SELECT
            p.id,
            p.username,
            u.avatar AS author_avatar,
            p.description,
            p.animal_type,
            p.location_text,
            p.maps_link,
            p.animal_condition,
            p.status,
            p.images,
            p.created_at,
            (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.id) AS like_count,
            " . ($currentUserId > 0
            ? "(SELECT COUNT(*) FROM post_likes pl2 WHERE pl2.post_id = p.id AND pl2.user_id = " . $currentUserId . ") AS user_liked,

            (SELECT COUNT(*) FROM post_bookmarks pb
                WHERE pb.post_id = p.id
                AND pb.user_id = " . $currentUserId . ") AS user_bookmarked,"
            : "0 AS user_liked,
            0 AS user_bookmarked,") . "
            (SELECT COUNT(*) FROM post_comments pc WHERE pc.post_id = p.id) AS comment_count
        FROM posts p
        LEFT JOIN users u ON u.username = p.username
        ORDER BY p.id DESC
    ";

    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $decoded = json_decode($row['images'] ?? '[]', true);
            $row['parsed_images'] = is_array($decoded) ? array_values(array_filter($decoded, fn($v) => is_string($v) && trim($v) !== '')) : [];
            $posts[] = $row;
        }
        $result->free();
    }

    $cRes = $conn->query("SELECT id, post_id, username, comment_text, created_at FROM post_comments ORDER BY id ASC");
    if ($cRes) {
        while ($cRow = $cRes->fetch_assoc()) {
            $pid = (int)$cRow['post_id'];
            $commentsByPost[$pid][] = $cRow;
        }
        $cRes->free();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StraySafe - Community Feed</title>
    <link rel="stylesheet" href="home.css" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .posts-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .sighting-card {
            position: relative;
            background: #ffffff;
            border: 2px solid #edf1f4;
            border-radius: 26px;
            padding: 1.5rem 1.75rem 1.25rem;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }

        .card-dismiss-btn {
            position: absolute;
            top: 16px;
            right: 20px;
            background: none;
            border: none;
            font-size: 1.25rem;
            font-weight: 700;
            color: #111827;
            cursor: pointer;
            line-height: 1;
        }

        .sighting-card-grid {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 1.5rem;
            align-items: start;
        }

        .author-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.9rem;
        }

        .author-avatar {
            width: 52px;
            height: 52px;
            border-radius: 6px;
            object-fit: cover;
            background: #e2e8f0;
            flex-shrink: 0;
        }

        .author-name {
            font-size: 1.25rem;
            font-weight: 800;
            color: #111827;
            line-height: 1.15;
        }

        .post-date-sub {
            font-size: 0.85rem;
            color: #374151;
            margin-top: 0.2rem;
        }

        .media-pair {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.65rem;
        }

        .media-pair.single-photo {
            grid-template-columns: 1fr;
        }

        .media-pair img {
            width: 100%;
            height: 112px;
            object-fit: cover;
            border-radius: 4px;
            background: #e2e8f0;
            display: block;
            cursor: zoom-in;
            transition: transform 0.15s ease, opacity 0.15s ease;
        }

        .media-pair img:hover {
            transform: scale(1.02);
            opacity: 0.95;
        }

        .sighting-details {
            padding-top: 0.2rem;
            padding-right: 1rem;
        }

        .sighting-location {
            font-size: 1.35rem;
            font-weight: 800;
            color: #111827;
            line-height: 1.25;
            margin-bottom: 0.35rem;
        }

        .view-location-link {
            display: inline-block;
            font-size: 0.95rem;
            font-weight: 700;
            color: #525b66;
            text-decoration: none;
            margin-bottom: 0.9rem;
        }

        .view-location-link:hover {
            text-decoration: underline;
            color: #0c9488;
        }

        .sighting-description {
            font-size: 0.98rem;
            line-height: 1.5;
            color: #1f2937;
            margin-bottom: 1rem;
            white-space: pre-line;
        }

        .sighting-condition {
            font-size: 0.95rem;
            color: #1f2937;
            font-weight: 500;
        }

        .card-actions-bar {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            align-items: center;
            margin-top: 1.25rem;
            padding-top: 0.65rem;
            border-top: 1px solid #f1f5f9;
        }

        .card-action-btn {
            background: none;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 1.05rem;
            font-weight: 600;
            color: #111827;
            cursor: pointer;
            padding: 0.35rem 0.5rem;
            border-radius: 8px;
            transition: background-color 0.15s ease, transform 0.15s ease;
        }

        .card-action-btn:hover {
            background-color: #f8fafc;
        }

        .card-action-btn.liked i {
            color: #c62828;
        }

        .card-action-btn i.fa-heart {
            color: #c62828;
            font-size: 1.25rem;
        }

        .card-action-btn i.fa-comment-dots,
        .card-action-btn i.fa-share,
        .card-action-btn i.fa-bookmark {
            font-size: 1.25rem;
            color: #111827;
        }

        .card-action-btn.bookmarked i.fa-bookmark {
            color: #0c9488;
        }

        .comments-drawer {
            margin-top: 0.9rem;
            padding-top: 0.9rem;
            border-top: 1px solid #e5e7eb;
        }

        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
            max-height: 210px;
            overflow-y: auto;
        }

        .comment-item {
            background: #f8fafc;
            border-radius: 8px;
            padding: 0.55rem 0.75rem;
            font-size: 0.9rem;
        }

        .comment-item strong {
            color: #111827;
        }

        .comment-item span.comment-time {
            font-size: 0.75rem;
            color: #6b7280;
            margin-left: 0.4rem;
        }

        .comment-form {
            display: flex;
            gap: 0.5rem;
        }

        .comment-form input {
            flex: 1;
            padding: 0.5rem 0.75rem;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        .comment-form button {
            background: #0c9488;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            cursor: pointer;
        }

        .comment-form button:hover {
            background: #0a7a70;
        }

        .community-empty {
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
            padding: 2rem;
            text-align: center;
            color: #4b5563;
        }

        /* Full-image Lightbox Modal */
        .image-lightbox {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.85);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .image-lightbox.open {
            display: flex;
        }

        .lightbox-inner {
            position: relative;
            max-width: 92vw;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .lightbox-img {
            max-width: 90vw;
            max-height: 82vh;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
            background: #0f172a;
        }

        .lightbox-close-btn {
            position: fixed;
            top: 20px;
            right: 26px;
            background: rgba(15, 23, 42, 0.75);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            width: 42px;
            height: 42px;
            border-radius: 50%;
            font-size: 1.5rem;
            line-height: 1;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .lightbox-close-btn:hover {
            background: #0c9488;
        }

        .lightbox-nav-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #f8fafc;
            font-size: 0.92rem;
            font-weight: 600;
        }

        .lightbox-nav-btn {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 8px;
            padding: 0.4rem 0.85rem;
            cursor: pointer;
            font-weight: 600;
        }

        .lightbox-nav-btn:hover:not(:disabled) {
            background: #0c9488;
        }

        .lightbox-nav-btn:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }

        @media (max-width: 680px) {
            .sighting-card-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="main-content" style="margin-top: 2rem; max-width: 1080px; margin-left: auto; margin-right: auto; padding: 0 1rem;">
        <section class="community-section">
            <div class="banner-header">
                <h1>Stray Safe Community</h1>
                <p>See what people share about sightings!</p>
                <p class="sub-banner">Explore the Community</p>
            </div>

            <div class="community-layout">
                <div class="feed-column">
                    <div class="feed-filter-bar">
                        <h2>Latest sightings</h2>
                        <select id="statusFilter">
                            <option value="All">All Posts</option>
                            <option value="Adoption">Adoption</option>
                            <option value="Stray">Stray</option>
                        </select>
                    </div>

                    <div id="postsContainer" class="posts-list">
                        <?php if (empty($posts)): ?>
                            <div class="community-empty">No sightings posted yet. Be the first to share one!</div>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <?php
                                $postId = (int)$post['id'];
                                $postStatus = trim((string)($post['status'] ?? 'Stray'));
                                $imgs = $post['parsed_images'] ?? [];
                                $postComments = $commentsByPost[$postId] ?? [];
                                $formattedDate = !empty($post['created_at']) ? date('F j, Y', strtotime($post['created_at'])) : '';
                                $rawAvatar = trim((string)($post['author_avatar'] ?? ''));
                                $authorAvatar = $rawAvatar !== '' ? ltrim($rawAvatar, '/') : 'paw.png';
                                ?>
                                <article class="sighting-card" data-post-id="<?= $postId ?>" data-status="<?= htmlspecialchars($postStatus, ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="button" class="card-dismiss-btn" title="Hide card" aria-label="Hide card">&times;</button>

                                    <div class="sighting-card-grid">
                                        <div>
                                            <div class="author-row">
                                                <img src="<?= htmlspecialchars($authorAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars((string)$post['username'], ENT_QUOTES, 'UTF-8') ?> avatar" class="author-avatar" />
                                                <div>
                                                    <div class="author-name"><?= htmlspecialchars((string)$post['username'], ENT_QUOTES, 'UTF-8') ?></div>
                                                    <div class="post-date-sub"><?= htmlspecialchars($formattedDate, ENT_QUOTES, 'UTF-8') ?></div>
                                                </div>
                                            </div>

                                            <?php if (!empty($imgs)): ?>
                                                <?php $cleanImgs = array_map(fn($u) => ltrim($u, '/'), array_slice($imgs, 0, 2)); ?>
                                                <div class="media-pair <?= count($cleanImgs) === 1 ? 'single-photo' : '' ?>">
                                                    <?php foreach ($cleanImgs as $idx => $imgSrc): ?>
                                                        <img
                                                            src="<?= htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8') ?>"
                                                            alt="Sighting photo"
                                                            class="clickable-image"
                                                            data-gallery="<?= htmlspecialchars(json_encode($cleanImgs), ENT_QUOTES, 'UTF-8') ?>"
                                                            data-index="<?= (int)$idx ?>"
                                                        />
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="sighting-details">
                                            <h3 class="sighting-location"><?= htmlspecialchars((string)$post['location_text'], ENT_QUOTES, 'UTF-8') ?></h3>

                                            <?php if (!empty($post['maps_link'])): ?>
                                                <a href="<?= htmlspecialchars((string)$post['maps_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer" class="view-location-link">View Specific Location</a>
                                            <?php else: ?>
                                                <span class="view-location-link">View Specific Location</span>
                                            <?php endif; ?>

                                            <p class="sighting-description"><?= htmlspecialchars((string)$post['description'], ENT_QUOTES, 'UTF-8') ?></p>

                                            <div class="sighting-condition">
                                                Condition: <?= htmlspecialchars((string)($post['animal_condition'] ?: 'Needs assistance'), ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-actions-bar">
                                        <button type="button" class="card-action-btn like-btn <?= ((int)$post['user_liked'] > 0) ? 'liked' : '' ?>" data-post-id="<?= $postId ?>">
                                            <i class="<?= ((int)$post['user_liked'] > 0) ? 'fa-solid' : 'fa-regular' ?> fa-heart"></i>
                                            <span class="like-count"><?= (int)$post['like_count'] ?></span>
                                        </button>

                                        <button type="button" class="card-action-btn comment-toggle-btn" data-post-id="<?= $postId ?>">
                                            <i class="fa-regular fa-comment-dots"></i>
                                            <span class="comment-count"><?= (int)$post['comment_count'] ?></span>
                                        </button>

                                        <button type="button" class="card-action-btn share-btn" data-post-id="<?= $postId ?>" title="Copy link">
                                            <i class="fa-solid fa-share"></i>
                                        </button>

                                        <button
                                            type="button"
                                            class="card-action-btn bookmark-btn <?php echo ($post['user_bookmarked'] ? 'bookmarked' : ''); ?>"
                                            data-post-id="<?php echo $postId; ?>"
                                            title="Bookmark post">
                                            <i class="<?php echo ($post['user_bookmarked'] ? 'fa-solid' : 'fa-regular'); ?> fa-bookmark"></i>
                                        </button>
                                    </div>

                                    <div class="comments-drawer" id="comments-drawer-<?= $postId ?>" hidden>
                                        <div class="comments-list" id="comments-list-<?= $postId ?>">
                                            <?php if (empty($postComments)): ?>
                                                <div class="comment-item" style="color:#6b7280;">No comments yet.</div>
                                            <?php else: ?>
                                                <?php foreach ($postComments as $c): ?>
                                                    <div class="comment-item">
                                                        <strong><?= htmlspecialchars((string)$c['username'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                        <span class="comment-time"><?= htmlspecialchars((string)$c['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
                                                        <div><?= nl2br(htmlspecialchars((string)$c['comment_text'], ENT_QUOTES, 'UTF-8')) ?></div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>

                                        <form class="comment-form" data-post-id="<?= $postId ?>">
                                            <input type="text" name="comment_text" placeholder="Write a helpful comment..." required />
                                            <button type="submit">Post</button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <aside class="sidebar-column">
                    <div class="info-card">
                        <span class="card-tag">COMMUNITY REMINDER</span>
                        <h4>Observe safely.</h4>
                        <p>Do not chase, corner, or handle an unfamiliar animal. If it appears seriously injured or dangerous, contact local animal welfare services or your local authorities.</p>
                    </div>

                    <div class="info-card">
                        <span class="card-tag">HOW IT WORKS</span>
                        <div class="step-item">
                            <h4>Connect</h4>
                            <p>Let nearby community members know.</p>
                        </div>
                        <hr />
                        <div class="step-item">
                            <h4>Help responsibly</h4>
                            <p>Share verified updates and contact appropriate helpers.</p>
                        </div>
                    </div>

                    <div class="adopt-cta-card">
                        <h3>Looking to adopt?</h3>
                        <p>Explore responsible adoption information and learn what to consider before welcoming a pet.</p>
                        <a href="find.php" class="adopt-link">Find a Pet &rarr;</a>
                    </div>
                </aside>
            </div>
        </section>
    </main>

    <!-- Full-size Photo Lightbox -->
    <div id="imageLightbox" class="image-lightbox" role="dialog" aria-modal="true" aria-label="Full photo preview">
        <button type="button" id="lightboxCloseBtn" class="lightbox-close-btn" title="Close" aria-label="Close">&times;</button>
        <div class="lightbox-inner">
            <img id="lightboxImg" src="" alt="Full sighting photo" class="lightbox-img" />
            <div id="lightboxNavRow" class="lightbox-nav-row" style="display:none;">
                <button type="button" id="lightboxPrevBtn" class="lightbox-nav-btn">&larr; Prev</button>
                <span id="lightboxCounter">1 / 1</span>
                <button type="button" id="lightboxNextBtn" class="lightbox-nav-btn">Next &rarr;</button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const container = document.getElementById('postsContainer');
            const filterSelect = document.getElementById('statusFilter');

            // Lightbox elements
            const lightbox = document.getElementById('imageLightbox');
            const lightboxImg = document.getElementById('lightboxImg');
            const lightboxCloseBtn = document.getElementById('lightboxCloseBtn');
            const lightboxNavRow = document.getElementById('lightboxNavRow');
            const lightboxPrevBtn = document.getElementById('lightboxPrevBtn');
            const lightboxNextBtn = document.getElementById('lightboxNextBtn');
            const lightboxCounter = document.getElementById('lightboxCounter');

            let currentGallery = [];
            let currentIndex = 0;

            function renderLightbox() {
                if (!currentGallery.length) return;
                lightboxImg.src = currentGallery[currentIndex];
                if (currentGallery.length > 1) {
                    lightboxNavRow.style.display = 'flex';
                    lightboxCounter.textContent = `${currentIndex + 1} / ${currentGallery.length}`;
                    lightboxPrevBtn.disabled = currentIndex === 0;
                    lightboxNextBtn.disabled = currentIndex === currentGallery.length - 1;
                } else {
                    lightboxNavRow.style.display = 'none';
                }
            }

            function openLightbox(gallery, index) {
                currentGallery = Array.isArray(gallery) && gallery.length ? gallery : [];
                currentIndex = Math.max(0, Math.min(index || 0, currentGallery.length - 1));
                if (!currentGallery.length) return;
                renderLightbox();
                lightbox.classList.add('open');
            }

            function closeLightbox() {
                lightbox.classList.remove('open');
                lightboxImg.src = '';
                currentGallery = [];
                currentIndex = 0;
            }

            if (lightboxCloseBtn) {
                lightboxCloseBtn.addEventListener('click', closeLightbox);
            }

            if (lightboxPrevBtn) {
                lightboxPrevBtn.addEventListener('click', function () {
                    if (currentIndex > 0) {
                        currentIndex--;
                        renderLightbox();
                    }
                });
            }

            if (lightboxNextBtn) {
                lightboxNextBtn.addEventListener('click', function () {
                    if (currentIndex < currentGallery.length - 1) {
                        currentIndex++;
                        renderLightbox();
                    }
                });
            }

            if (lightbox) {
                lightbox.addEventListener('click', function (e) {
                    if (e.target === lightbox) {
                        closeLightbox();
                    }
                });
            }

            document.addEventListener('keydown', function (e) {
                if (!lightbox || !lightbox.classList.contains('open')) return;
                if (e.key === 'Escape') {
                    closeLightbox();
                } else if (e.key === 'ArrowLeft' && currentIndex > 0) {
                    currentIndex--;
                    renderLightbox();
                } else if (e.key === 'ArrowRight' && currentIndex < currentGallery.length - 1) {
                    currentIndex++;
                    renderLightbox();
                }
            });

            if (filterSelect && container) {
                const cards = Array.from(container.querySelectorAll('.sighting-card'));
                const emptyMsg = document.createElement('div');
                emptyMsg.className = 'community-empty';
                emptyMsg.style.display = 'none';
                emptyMsg.textContent = 'No posts match this filter yet.';
                container.appendChild(emptyMsg);

                filterSelect.addEventListener('change', function () {
                    const selected = this.value;
                    let visible = 0;
                    cards.forEach((card) => {
                        const status = card.getAttribute('data-status') || '';
                        const match = selected === 'All' || status.toLowerCase() === selected.toLowerCase();
                        card.style.display = match ? '' : 'none';
                        if (match) visible++;
                    });
                    emptyMsg.style.display = visible === 0 ? '' : 'none';
                });
            }

            if (!container) return;

            container.addEventListener('click', async function (e) {
                const clickedImg = e.target.closest('.clickable-image');
                if (clickedImg) {
                    let gallery = [];
                    try {
                        gallery = JSON.parse(clickedImg.getAttribute('data-gallery') || '[]');
                    } catch (_) {
                        gallery = [];
                    }
                    if (!gallery.length && clickedImg.getAttribute('src')) {
                        gallery = [clickedImg.getAttribute('src')];
                    }
                    const idx = parseInt(clickedImg.getAttribute('data-index') || '0', 10) || 0;
                    openLightbox(gallery, idx);
                    return;
                }

                const dismissBtn = e.target.closest('.card-dismiss-btn');
                if (dismissBtn) {
                    const card = dismissBtn.closest('.sighting-card');
                    if (card) card.style.display = 'none';
                    return;
                }

                const likeBtn = e.target.closest('.like-btn');
                if (likeBtn) {
                    const postId = likeBtn.getAttribute('data-post-id');
                    const fd = new FormData();
                    fd.append('action', 'toggle_like');
                    fd.append('post_id', postId);

                    const res = await fetch('community_action.php', { method: 'POST', body: fd });
                    const data = await res.json();
                    if (!data.success) {
                        alert(data.message || 'Could not update like.');
                        return;
                    }

                    likeBtn.classList.toggle('liked', Boolean(data.liked));
                    const icon = likeBtn.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-solid', Boolean(data.liked));
                        icon.classList.toggle('fa-regular', !Boolean(data.liked));
                    }
                    const countEl = likeBtn.querySelector('.like-count');
                    if (countEl) countEl.textContent = data.like_count;
                    return;
                }

                const commentToggle = e.target.closest('.comment-toggle-btn');
                if (commentToggle) {
                    const postId = commentToggle.getAttribute('data-post-id');
                    const drawer = document.getElementById('comments-drawer-' + postId);
                    if (drawer) {
                        drawer.hidden = !drawer.hidden;
                        if (!drawer.hidden) {
                            const input = drawer.querySelector('input[name="comment_text"]');
                            if (input) input.focus();
                        }
                    }
                    return;
                }

                const shareBtn = e.target.closest('.share-btn');
                if (shareBtn) {
                    const postId = shareBtn.getAttribute('data-post-id');
                    const shareUrl = `${window.location.origin}${window.location.pathname}#post-${postId}`;
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(shareUrl).catch(() => {});
                    }
                    alert('Post link copied!');
                    return;
                }

                const bookmarkBtn = e.target.closest('.bookmark-btn');

                if (bookmarkBtn) {

                    const postId =
                        bookmarkBtn.getAttribute('data-post-id');

                    const fd = new FormData();

                    fd.append('post_id', postId);

                    const res = await fetch(
                        'bookmark_post.php',
                        {
                            method: 'POST',
                            body: fd
                        }
                    );

                    const data = await res.json();

                    if (!data.success) {

                        alert(
                            data.message ||
                            'Could not save post.'
                        );

                        return;
                    }

                    bookmarkBtn.classList.toggle(
                        'bookmarked',
                        data.saved
                    );

                    const icon =
                        bookmarkBtn.querySelector('i');

                    if (icon) {

                        icon.classList.toggle(
                            'fa-solid',
                            data.saved
                        );

                        icon.classList.toggle(
                            'fa-regular',
                            !data.saved
                        );
                    }
                }
            });

            container.addEventListener('submit', async function (e) {
                const form = e.target.closest('.comment-form');
                if (!form) return;
                e.preventDefault();

                const postId = form.getAttribute('data-post-id');
                const input = form.querySelector('input[name="comment_text"]');
                const text = input ? input.value.trim() : '';
                if (!text) return;

                const fd = new FormData();
                fd.append('action', 'add_comment');
                fd.append('post_id', postId);
                fd.append('comment_text', text);

                const res = await fetch('community_action.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (!data.success) {
                    alert(data.message || 'Could not post comment.');
                    return;
                }

                const list = document.getElementById('comments-list-' + postId);
                if (list) {
                    if (list.children.length === 1 && list.children[0].textContent.trim() === 'No comments yet.') {
                        list.innerHTML = '';
                    }
                    const item = document.createElement('div');
                    item.className = 'comment-item';
                    const strong = document.createElement('strong');
                    strong.textContent = data.comment.username;
                    const timeSpan = document.createElement('span');
                    timeSpan.className = 'comment-time';
                    timeSpan.textContent = data.comment.created_at;
                    const bodyDiv = document.createElement('div');
                    bodyDiv.textContent = data.comment.comment_text;
                    item.appendChild(strong);
                    item.appendChild(timeSpan);
                    item.appendChild(bodyDiv);
                    list.appendChild(item);
                    list.scrollTop = list.scrollHeight;
                }

                const card = form.closest('.sighting-card');
                const countSpan = card ? card.querySelector('.comment-count') : null;
                if (countSpan) countSpan.textContent = data.comment_count;

                input.value = '';
            });
        })();
    </script>
</body>
</html>