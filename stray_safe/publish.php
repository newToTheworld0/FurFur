<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StraySafe - Publish Post</title>
    <link rel="stylesheet" href="home.css" />
    <link rel="stylesheet" href="style.css" />
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="main-content" style="margin-top: 2rem; max-width: 1050px; margin-left: auto; margin-right: auto; padding: 0 1rem;">
        <section class="publish-section">
            <div class="banner-header">
                <h1>Share an animal sighting</h1>
                <p>Share a sighting, add its location, and help others stay informed about animals in your area.</p>
                <p class="sub-banner">Help the community know what is happening nearby</p>
            </div>

            <form id="sightingForm" enctype="multipart/form-data">
                <div class="form-top-row">
                    <div class="textarea-group">
                        <label for="description">What's happening?</label>
                        <textarea id="description" name="description" rows="4" placeholder="Describe the animal you saw, what it was doing, or how it may need help..." required></textarea>
                    </div>

                    <aside class="info-card">
                        <span class="card-tag">HOW IT WORKS</span>
                        <h4>Post</h4>
                        <p>Describe the animal and its location.</p>
                    </aside>
                </div>

                <div class="form-inputs-grid">
                    <div class="input-group">
                        <label for="animal_type">Animal type</label>
                        <select id="animal_type" name="animal_type" required>
                            <option value="" disabled selected>Select animal</option>
                            <option value="Dog">Dog</option>
                            <option value="Cat">Cat</option>
                            <option value="Other">Other</option>
                            <option value="Unknown">Unknown</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="location_text">
                            Location <span id="toggleMapsBtn" class="inline-link">+ Add maps location (optional)</span>
                        </label>
                        <input type="text" id="location_text" name="location_text" placeholder="Street, Barangay, City" required />
                        <input type="url" id="maps_link" name="maps_link" class="hidden-input" placeholder="Paste Google Maps link..." />
                    </div>

                    <div class="input-group">
                        <label for="condition">Condition</label>
                        <input type="text" id="condition" name="condition" placeholder="Appears okay / may be injured..." required />
                    </div>

                    <div class="input-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" required>
                            <option value="Stray">Stray</option>
                            <option value="Adoption">Adoption</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <label for="photos" class="photo-upload-label">+ Add photos (multiple allowed)</label>
                    <input type="file" id="photos" name="photos[]" accept="image/*" multiple hidden />
                    <span id="fileCountText" class="file-count"></span>
                </div>

                <button type="submit" class="btn-publish">Publish Post</button>
            </form>
        </section>
    </main>

    <script src="app.js"></script>
    <script src="script.js"></script>
</body>
</html>