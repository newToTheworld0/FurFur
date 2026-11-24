<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StraySafe - Community Feed</title>
    <link rel="stylesheet" href="home.css" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="main-content" style="margin-top: 2rem; max-width: 1050px; margin-left: auto; margin-right: auto; padding: 0 1rem;">
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

                    <div id="postsContainer" class="posts-list"></div>
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

    <script src="app.js"></script>
    <script src="script.js"></script>
</body>
</html>