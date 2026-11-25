<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';

$posts = [];
$dbError = "";

$sql = "SELECT id, username, description, animal_type, location_text, maps_link, animal_condition, status, images, created_at FROM posts ORDER BY id DESC";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $decodedImages = json_decode($row['images'] ?? '[]', true);
        $firstImage = (is_array($decodedImages) && !empty($decodedImages[0]))
            ? trim((string) $decodedImages[0])
            : 'paw.png';

        $row['first_image'] = $firstImage;
        $posts[] = $row;
    }
    $result->free();
} else {
    $dbError = "Unable to load community posts right now.";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StraySafe — Find a Pet</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="stylesheet" href="find.css" />
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Decorative Paws -->
    <img src="paws.png" alt="" class="decor-paw paw-top-right" aria-hidden="true" />
    <img src="paws.png" alt="" class="decor-paw paw-bottom-left" aria-hidden="true" />

    <main class="find-wrapper">
        <!-- Top Hero Banner -->
        <section class="find-hero">
            <div class="find-banner">
                <h1>See animals who needs home!</h1>
                <p>Browse and search different animals</p>
                <p class="hero-sub">Find your buddy</p>
            </div>
        </section>

        <!-- Search & Filter Controls -->
        <section class="filter-controls">
            <div class="search-box">
                <span class="search-icon" aria-hidden="true">&#128269;</span>
                <input
                    type="text"
                    id="searchInput"
                    placeholder="Search by animal or location"
                    aria-label="Search by animal or location"
                />
            </div>

            <div class="species-filter-wrap">
                <select id="speciesFilter" aria-label="Filter by species">
                    <option value="all">All Species</option>
                    <option value="Dog">Dog</option>
                    <option value="Cat">Cat</option>
                    <option value="Other">Other</option>
                    <option value="Unknown">Unknown</option>
                </select>
            </div>
        </section>

        <!-- Pet Cards Grid -->
        <?php if ($dbError !== ''): ?>
            <p class="no-results"><?php echo htmlspecialchars($dbError, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php elseif (empty($posts)): ?>
            <p class="no-results">No community posts available yet.</p>
        <?php else: ?>
            <section class="pet-grid" id="petGrid">
                <?php foreach ($posts as $post): ?>
                    <?php
                        $postId = (int) ($post['id'] ?? 0);
                        $speciesRaw = trim((string) ($post['animal_type'] ?? 'Unknown'));
                        $speciesVal = htmlspecialchars($speciesRaw !== '' ? $speciesRaw : 'Unknown', ENT_QUOTES, 'UTF-8');

                        $statusRaw = trim((string) ($post['status'] ?? ''));
                        $locationRaw = trim((string) ($post['location_text'] ?? ''));
                        $descRaw = trim((string) ($post['description'] ?? ''));
                        $conditionRaw = trim((string) ($post['animal_condition'] ?? ''));
                        $usernameRaw = trim((string) ($post['username'] ?? 'Community Member'));

                        $titleText = $statusRaw !== '' ? ($speciesVal . ' (' . htmlspecialchars($statusRaw, ENT_QUOTES, 'UTF-8') . ')') : $speciesVal;
                        $noteText = $descRaw;
                        if ($conditionRaw !== '') {
                            $noteText .= ($noteText !== '' ? ' — ' : '') . 'Condition: ' . $conditionRaw;
                        }

                        $imgVal = htmlspecialchars($post['first_image'] ?: 'paw.png', ENT_QUOTES, 'UTF-8');
                        $locationVal = htmlspecialchars($locationRaw, ENT_QUOTES, 'UTF-8');
                        $notesVal = htmlspecialchars($noteText, ENT_QUOTES, 'UTF-8');
                        $searchBlob = htmlspecialchars(strtolower($speciesRaw . ' ' . $statusRaw . ' ' . $locationRaw . ' ' . $descRaw . ' ' . $conditionRaw . ' ' . $usernameRaw), ENT_QUOTES, 'UTF-8');
                    ?>
                    <article
                        class="pet-card"
                        data-species="<?php echo $speciesVal; ?>"
                        data-search="<?php echo $searchBlob; ?>"
                    >
                        <div class="pet-photo-box">
                            <img src="<?php echo $imgVal; ?>" alt="<?php echo $titleText; ?>" />
                            <span class="species-badge"><?php echo $speciesVal; ?></span>
                        </div>

                        <div class="pet-body">
                            <h2 class="pet-title"><?php echo $titleText; ?></h2>

                            <?php if ($locationVal !== ''): ?>
                                <p class="pet-location">
                                    <span class="pin-icon" aria-hidden="true">&#128205;</span>
                                    <?php echo $locationVal; ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($notesVal !== ''): ?>
                                <div class="pet-note-box">
                                    <?php echo $notesVal; ?>
                                </div>
                            <?php endif; ?>

                            <div class="pet-card-footer">
                                <a href="community.php?post_id=<?php echo $postId; ?>#post-<?php echo $postId; ?>" class="btn-view-post">
                                    <span class="heart-icon" aria-hidden="true">&#10084;</span>
                                    <span>View Post</span>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
            <p id="noResultsMsg" class="no-results" hidden>No matching posts found.</p>
        <?php endif; ?>
    </main>

    <script src="script.js"></script>
    <script>
        const searchInput = document.getElementById('searchInput');
        const speciesFilter = document.getElementById('speciesFilter');
        const petCards = Array.from(document.querySelectorAll('.pet-card'));
        const noResultsMsg = document.getElementById('noResultsMsg');

        function filterPets() {
            const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
            const selectedSpecies = speciesFilter ? speciesFilter.value : 'all';
            let visibleCount = 0;

            petCards.forEach((card) => {
                const cardSpecies = (card.getAttribute('data-species') || '').toLowerCase();
                const searchableText = card.getAttribute('data-search') || '';

                const matchesSpecies = (selectedSpecies === 'all') || (cardSpecies === selectedSpecies.toLowerCase());
                const matchesQuery = (query === '') || searchableText.includes(query);

                if (matchesSpecies && matchesQuery) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            if (noResultsMsg) {
                noResultsMsg.hidden = visibleCount > 0;
            }
        }

        if (searchInput) searchInput.addEventListener('input', filterPets);
        if (speciesFilter) speciesFilter.addEventListener('change', filterPets);
    </script>
</body>
</html>