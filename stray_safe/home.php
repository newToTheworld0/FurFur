<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Homepage</title>
        <link rel="stylesheet" href="home.css">
    </head>
    <body>
       <?php include 'navbar.php'; ?>

       <div class="description">
            <div class="quote-container">
                <h2>Community Animal Rescue</h2>
                <p>Connecting compassionate citizens to report stray animals, facilitate safe adoptions, and report animal mistreatment across local communities.</p>
                <img src="paw-quote.png" alt="quote paw">
            </div>
            <div class="add-info">
                <h2>Every Animal Deserves <span>Care</span></h2>
                <p>Help make stray animals safer by sharing information, finding pets in need, and <br> connecting them with people who can help.</p>
            </div>

           <div id="com-btn" onclick="window.location.href='community.php'">
                Open Community Feed
            </div>

            <div id="circle"></div>
            <div id="ellipse"></div>
            
            <div id="homepost">
                <div class="container">
                   <div class="sub-one">
                        <div class="left-wing">
                            <div class="user-header">
                                <img src="pfp.jpg" id="pfp" alt="Profile">
                                <p><span>User_001</span>October 15, 2026</p>
                            </div>
                            <div class="pet-photos">
                                <img src="pet1.jpg" class="feed" alt="Pet 1"/>
                                <img src="pet2.jpg" class="feed" alt="Pet 2"/>
                            </div>
                        </div>
                        <div class="right-wing">
                            <p id="p1">Public Market, San <br>Fernando, Pampanga</p>
                            <p id="p2">View Specific Location</p>
                            <p id="p3">Spotted this friendly-looking dog and pigs near the public market. It looks so cute. Sharing in case someone recognizes it.<br><br>Condition: Needs assistance</p>
                        </div>
                   </div>
                   <div class="sub-two">
                        <div class="react">❤️ 12</div>
                        <div class="react">💬 5</div>
                        <div class="react">➤ Share</div>
                   </div>
                </div>
            </div>
       </div>

            <div class="features-count">
                <div class="features"><span>01</span> Share a sighting</div>
                <div class="features"><span>02</span> Connect with others</div>
                <div class="features"><span>03</span> Support animal welfare</div>
            </div>

            <div class="information-box">
                <div class="sub-info-box1">
                    <p class="t1">How StraySafe Helps</p>
                    <p class="t2">One place makes a difference</p>
                    <p class="t3">Use the website to discover animals, share useful <br>information, or explore responsible homing.</p>
                </div>
                <div class="sub-info-box2">
                    <a class="box" href="community.php">
                        <h3>Browse Animals</h3>
                        <img src="box1.png" alt="Browse">
                        <p>View community animal profiles and <br>their reported locations.</p>
                        <h4>Community Feed ➔</h4>
                    </a>
                    <a class="box" href="find.php">
                        <h3>Find a Pet</h3>
                        <img src="box2.png" alt="Find">
                        <p>See about animals that may be ready for <br>adoption.</p>
                        <h4>Find a Pet ➔</h4>
                    </a>
                    <a class="box" href="publish.php">
                        <h3>Report a Stray</h3>
                        <img src="box3.png" alt="Report">
                        <p>Share a sighting so the community <br>can be more aware.</p>
                        <h4>Publish ➔</h4>
                    </a>
                </div>
                <div class="sub-info-box3">
                    <div class="last-box">
                        <div class="last-box-text">
                            <span>be a part of change</span>
                            <h3>A safer community starts with awareness.</h3>
                            <p>Responsible care can begin with simply sharing accurate information.</p>
                        </div>
                        <div class="learn" onclick="window.location.href='about.php'">
                            <a>Learn about StraySafe</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- PetScan Section (place right after the "Learn about StraySafe" banner) -->
<section class="petscan-section" aria-label="PetScan">
    <div class="petscan-card">
        <div class="petscan-left">
            <h2 class="petscan-eyebrow">Need help Identifying your pet?</h2>

            <div class="petscan-banner-box">
                <div>
                    <h3>PetScan</h3>
                    <p>Use our camera to Identify what animal or breed your pet is!</p>
                </div>
                <i class="fa-solid fa-paw petscan-paw-bg" aria-hidden="true"></i>
            </div>

            <div class="petscan-steps">
                <div class="petscan-step">
                    <h4>Take a Photo</h4>
                    <p>Use your camera to capture an animal</p>
                </div>
                <div class="petscan-step">
                    <h4>Identify Animal</h4>
                    <p>Discover its possible type and breed</p>
                </div>
            </div>
        </div>

        <div class="petscan-right">
            <div class="petscan-graphic-wrap">
                <span class="petscan-shape petscan-shape-green"></span>
                <span class="petscan-shape petscan-shape-gray"></span>
                <i class="fa-solid fa-camera petscan-camera-icon" aria-hidden="true"></i>
            </div>

            <button type="button" id="openPetScanBtn" class="petscan-open-btn"
                onclick="window.location.href='../pet_recognition/recognition.php'">
                    Open Camera!
            </button>
        </div>
    </div>
</section>

<!-- Paltan ko -->
<!-- PetScan Camera / Photo Modal -->
<div id="petScanModal" class="petscan-modal" role="dialog" aria-modal="true" aria-labelledby="petScanModalTitle">
    <div class="petscan-modal-box">
        <button type="button" id="closePetScanBtn" class="petscan-modal-close" aria-label="Close">&times;</button>
        <h3 id="petScanModalTitle">PetScan</h3>
        <p class="petscan-modal-sub">Capture a photo with your camera or upload an image to preview and inspect.</p>

        <div class="petscan-viewport">
            <video id="petScanVideo" autoplay playsinline muted style="display:none;"></video>
            <canvas id="petScanCanvas" style="display:none;"></canvas>
            <img id="petScanCapturedImg" alt="Captured animal preview" style="display:none;" />
            <div id="petScanPlaceholder" class="petscan-placeholder">
                Starting camera... If camera access isn't available, choose a photo below.
            </div>
        </div>

        <div class="petscan-modal-controls">
            <button type="button" id="capturePetScanBtn" class="petscan-btn-primary">Snap Photo</button>
            <button type="button" id="retakePetScanBtn" class="petscan-btn-secondary" style="display:none;">Retake</button>
            <label class="petscan-btn-secondary petscan-upload-label">
                Upload Photo
                <input type="file" id="petScanFileInput" accept="image/*" hidden />
            </label>
        </div>

        <div id="petScanResult" class="petscan-result" style="display:none;">
            <strong>Photo Ready!</strong>
            <p id="petScanResultText">Image captured. You can attach this photo when posting a sighting or use it to check breed features manually.</p>
        </div>
    </div>
</div>

<style>
    .petscan-section {
        margin: 1.5rem auto 2rem;
        max-width: 1140px;
        padding: 0 1rem;
    }

    .petscan-card {
        display: grid;
        grid-template-columns: 1.35fr 0.75fr;
        gap: 2rem;
        align-items: center;
        background: #eef6f2;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 1.75rem 2.25rem;
    }

    .petscan-eyebrow {
        font-size: 1.05rem;
        font-weight: 700;
        color: #1f6f54;
        margin: 0 0 0.9rem;
    }

    .petscan-banner-box {
        position: relative;
        overflow: hidden;
        background: linear-gradient(90deg, #16654b 0%, #56a887 100%);
        color: #ffffff;
        border-radius: 10px;
        padding: 1.15rem 1.5rem;
        margin-bottom: 1.35rem;
    }

    .petscan-banner-box h3 {
        margin: 0 0 0.3rem;
        font-size: 1.65rem;
        font-weight: 800;
        line-height: 1.15;
    }

    .petscan-banner-box p {
        margin: 0;
        font-size: 0.92rem;
        opacity: 0.95;
    }

    .petscan-paw-bg {
        position: absolute;
        right: 18px;
        bottom: -8px;
        font-size: 4.5rem;
        color: rgba(255, 255, 255, 0.22);
        transform: rotate(-18deg);
        pointer-events: none;
    }

    .petscan-steps {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .petscan-step h4 {
        margin: 0 0 0.25rem;
        font-size: 1.15rem;
        font-weight: 800;
        color: #111827;
    }

    .petscan-step p {
        margin: 0;
        font-size: 0.9rem;
        color: #4b5563;
    }

    .petscan-right {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    .petscan-graphic-wrap {
        position: relative;
        width: 190px;
        height: 130px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .petscan-shape {
        position: absolute;
        pointer-events: none;
    }

    .petscan-shape-green {
        width: 115px;
        height: 125px;
        left: 16px;
        top: 2px;
        background: #9bb3a6;
        border-radius: 50%;
        transform: rotate(-28deg);
    }

    .petscan-shape-gray {
        width: 112px;
        height: 112px;
        right: 10px;
        top: 8px;
        background: #6b6e70;
        border-radius: 50%;
    }

    .petscan-camera-icon {
        position: relative;
        z-index: 2;
        font-size: 4.2rem;
        color: #ffffff;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .petscan-open-btn {
        background: #4b7738;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 0.7rem 1.65rem;
        font-size: 1.05rem;
        font-weight: 700;
        cursor: pointer;
        transition: background-color 0.15s ease, transform 0.15s ease;
    }

    .petscan-open-btn:hover {
        background: #3c612c;
        transform: translateY(-1px);
    }

    .petscan-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.65);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .petscan-modal.open {
        display: flex;
    }

    .petscan-modal-box {
        position: relative;
        background: #ffffff;
        border-radius: 16px;
        width: 100%;
        max-width: 500px;
        padding: 1.5rem;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
    }

    .petscan-modal-close {
        position: absolute;
        top: 12px;
        right: 16px;
        background: none;
        border: none;
        font-size: 1.6rem;
        color: #374151;
        cursor: pointer;
    }

    .petscan-modal-sub {
        margin: 0.25rem 0 1rem;
        font-size: 0.9rem;
        color: #4b5563;
    }

    .petscan-viewport {
        width: 100%;
        height: 260px;
        background: #0f172a;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    .petscan-viewport video,
    .petscan-viewport img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .petscan-placeholder {
        color: #e2e8f0;
        font-size: 0.92rem;
        padding: 1rem;
        text-align: center;
    }

    .petscan-modal-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
    }

    .petscan-btn-primary,
    .petscan-btn-secondary {
        border: none;
        border-radius: 8px;
        padding: 0.55rem 1rem;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
    }

    .petscan-btn-primary {
        background: #0c9488;
        color: #ffffff;
    }

    .petscan-btn-secondary {
        background: #e2e8f0;
        color: #111827;
        display: inline-flex;
        align-items: center;
    }

    .petscan-result {
        margin-top: 1rem;
        padding: 0.85rem 1rem;
        background: #f0fdfa;
        border: 1px solid #99f6e4;
        border-radius: 10px;
        font-size: 0.9rem;
        color: #134e4a;
    }

    .petscan-result p {
        margin: 0.25rem 0 0;
    }

    @media (max-width: 780px) {
        .petscan-card {
            grid-template-columns: 1fr;
            padding: 1.35rem;
        }

        .petscan-steps {
            grid-template-columns: 1fr;
            gap: 0.85rem;
        }
    }
</style>

<script>
(function () {
    const openBtn = document.getElementById('openPetScanBtn');
    const modal = document.getElementById('petScanModal');
    const closeBtn = document.getElementById('closePetScanBtn');
    const video = document.getElementById('petScanVideo');
    const canvas = document.getElementById('petScanCanvas');
    const capturedImg = document.getElementById('petScanCapturedImg');
    const placeholder = document.getElementById('petScanPlaceholder');
    const captureBtn = document.getElementById('capturePetScanBtn');
    const retakeBtn = document.getElementById('retakePetScanBtn');
    const fileInput = document.getElementById('petScanFileInput');
    const resultBox = document.getElementById('petScanResult');

    let stream = null;

    function stopCamera() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        video.style.display = 'none';
    }

    async function startCamera() {
        capturedImg.style.display = 'none';
        retakeBtn.style.display = 'none';
        captureBtn.style.display = 'inline-block';
        resultBox.style.display = 'none';
        placeholder.style.display = 'block';
        placeholder.textContent = 'Starting camera...';

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            placeholder.textContent = 'Camera API not supported here. Use "Upload Photo" instead.';
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' },
                audio: false
            });
            video.srcObject = stream;
            video.style.display = 'block';
            placeholder.style.display = 'none';
        } catch (err) {
            placeholder.textContent = 'Camera permission denied or unavailable. Use "Upload Photo" below.';
        }
    }

    function openModal() {
        modal.classList.add('open');
        startCamera();
    }

    function closeModal() {
        stopCamera();
        modal.classList.remove('open');
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });
    }

    if (captureBtn) {
        captureBtn.addEventListener('click', function () {
            if (!stream || video.readyState < 2) return;
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            capturedImg.src = canvas.toDataURL('image/png');
            capturedImg.style.display = 'block';
            stopCamera();
            placeholder.style.display = 'none';
            captureBtn.style.display = 'none';
            retakeBtn.style.display = 'inline-block';
            resultBox.style.display = 'block';
        });
    }

    if (retakeBtn) {
        retakeBtn.addEventListener('click', startCamera);
    }

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function (ev) {
                stopCamera();
                placeholder.style.display = 'none';
                capturedImg.src = ev.target.result;
                capturedImg.style.display = 'block';
                captureBtn.style.display = 'none';
                retakeBtn.style.display = 'inline-block';
                resultBox.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });
    }
})();
</script>

            <footer>
                <div class="footer-box f1">
                    <img src="paw.png" alt="Logo">
                    <p>StraySafe</p>
                </div>
                <div class="footer-box f2">
                    <p>Information and Welfare Tracking System for Community Animals</p>
                </div>
                <div class="footer-box f3">
                    <p>Student project</p>
                </div>
            </footer>
        <script src="script.js"></script>
    </body>
</html>