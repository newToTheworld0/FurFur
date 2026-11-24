
<?php
$success_message = "";
$breed_results = [];
$pet_results = [];

$breed_error = "";
$pet_error = "";

$uploaded_image = null;
$image_name = "";


// Function for sending an image to FastAPI
function sendToRecognitionAPI($api_url, $image)
{
    $curl_file = new CURLFile(
        $image["tmp_name"],
        $image["type"],
        $image["name"]
    );

    $post_data = [
        "file" => $curl_file
    ];

    $ch = curl_init($api_url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);

        return [
            "success" => false,
            "error" => "Connection error: " . $error
        ];
    }

    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    $result = json_decode($response, true);

    if ($http_status >= 400) {
        return [
            "success" => false,
            "error" => $result["detail"] ?? "API request failed."
        ];
    }

    if ($result === null) {
        return [
            "success" => false,
            "error" => "Invalid API response."
        ];
    }

    return [
        "success" => true,
        "data" => $result
    ];
}


// Process uploaded image
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        isset($_FILES["pet_image"]) &&
        $_FILES["pet_image"]["error"] === UPLOAD_ERR_OK
    ) {

        $image = $_FILES["pet_image"];

        // Verify that the uploaded file is an image
        $file_info = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->file($image["tmp_name"]);

        $allowed_types = [
            "image/jpeg",
            "image/png",
            "image/webp"
        ];

        if (in_array($mime_type, $allowed_types)) {

            // Prepare image for display in the browser
            $image_data = file_get_contents($image["tmp_name"]);

            $uploaded_image = "data:" . $mime_type .
                ";base64," . base64_encode($image_data);

            $image_name = $image["name"];

            if ($image["name"] === "camera-capture.jpg") {
    $success_message = "Photo captured successfully!";
}   

            // Send image to breed classification API
            $breed_response = sendToRecognitionAPI(
                "http://127.0.0.1:8000/predict/breed",
                $image
            );

            if ($breed_response["success"]) {
                $breed_results = $breed_response["data"]["predictions"];
            } else {
                $breed_error = $breed_response["error"];
            }

            // Send image to individual identification API
            $pet_response = sendToRecognitionAPI(
                "http://127.0.0.1:8000/identify",
                $image
            );

            if ($pet_response["success"]) {
                $pet_results = $pet_response["data"]["matches"];
            } else {
                $pet_error = $pet_response["error"];
            }

        } else {
            $breed_error = "Unsupported image format. Use JPG, PNG, or WEBP.";
        }

    } else {
        $breed_error = "Please select an image to recognize.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pet Recognition | Stray Safe</title>

<style>

* {
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
    background-color: #FFFCFA;
    color: #333;
}

/* Heading */

h1 {
    color: #188175;
    margin: 0 0 8px;
}

h2, h3 {
    margin-top: 0;
}

h3 {
    color: #188175;
}

/* Upload form */

form {
    margin-top: 18px;
    margin-bottom: 18px;
    padding: 15px;
    background-color: white;
    border-radius: 12px;
}

input[type="file"] {
    display: block;
    margin: 12px 0;
}

button {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(100deg, #188175, #4EA179);
    color: white;
    cursor: pointer;
}

button:hover {
    opacity: 0.9;
}

/* Main recognition layout */

.recognition-container {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 20px;
    align-items: stretch;
    margin-top: 20px;
}

/* Uploaded image */

.image-container {
    background-color: white;
    padding: 15px;
    border-radius: 15px;
    text-align: center;
    min-width: 0;
}

.pet-image {
    display: block;
    width: 100%;
    max-width: 400px;
    max-height: 400px;
    height: auto;
    object-fit: contain;
    margin: 0 auto;
    border-radius: 12px;
}

/* Right side */

.results-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
    min-width: 0;
    min-height: 0;
}

/* Result boxes */

.breed-results,
.pet-results {
    background-color: white;
    padding: 15px;
    border-radius: 15px;
    min-width: 0;
    min-height: 0;
    display: flex;
    flex-direction: column;
    flex: 1;
}

/* Only these lists scroll */

.breed-list,
.pet-list {
    flex: 1;
    min-height: 0;
    max-height: 250px;
    overflow-y: auto;
    border-radius: 10px;
}

/* Individual results */

.breed-item,
.pet-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 13px;
    margin-bottom: 8px;
    background-color: #FFFCFA;
    border-radius: 10px;
}

/* First result stays at the top */

.pinned-result {
    position: sticky;
    top: 0;
    z-index: 2;
    background-color: #188175;
    color: white;
    border: 1px solid #4EA179;
    font-weight: bold;
}

/* Responsive layout */

@media (max-width: 768px) {

    body {
        padding: 15px;
    }

    .recognition-container {
        grid-template-columns: 1fr;
    }

    .pet-image {
        max-width: 350px;
        max-height: 350px;
    }

}

/* 
AHHHHHHHHHHHHHHHHHHHHH 
*/


@media (min-width: 769px) {

    body {
        padding: 18px 25px;
        gap: 0;
    }

    h1 {
        font-size: 27px;
        margin: 0 0 5px;
    }

    body > p {
        font-size: 15px;
        margin: 0 0 12px;
    }

    body > form {
        padding: 13px 16px;
        margin: 8px 0;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    body > form input[type="file"] {
        margin: 0;
        min-width: 0;
    }

    body > form button {
        padding: 10px 20px;
        white-space: nowrap;
    }

    body > h2 {
        font-size: 21px;
        margin: 8px 0;
    }

    body > hr {
        margin: 8px 0;
    }

    .recognition-container {
        flex: none;
        height: clamp(380px, 58vh, 520px);
        margin-top: 10px;
        gap: 20px;
    }

    .image-container {
        padding: 18px;
    }

    .image-container h3 {
        margin-bottom: 12px;
    }

 .pet-image {
    display: block;
    width: 100%;
    height: auto;
    max-width: 450px;
    max-height: 350px;
    object-fit: contain;
    margin: 0 auto;
}

    .results-container {
        gap: 16px;
    }

    .breed-results,
    .pet-results {
        padding: 18px;
    }

    .breed-results h3,
    .pet-results h3 {
        margin-bottom: 12px;
    }

    .breed-list,
    .pet-list {
        flex: 1;
        min-height: 0;
        max-height: none;
    }

    .breed-item,
    .pet-item {
        padding: 14px;
        margin-bottom: 9px;
    }
}


/* Upload and camera controls */
#recognition-form {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px 20px;
    padding: 20px 24px;
}

#recognition-form input[type="file"] {
    flex: 1;
    min-width: 220px;
    margin: 0;
}

#recognition-form label {
    white-space: nowrap;
}

#recognition-form button {
    padding: 12px 22px;
    min-height: 44px;
    white-space: nowrap;
}

/* Primary recognition button */
#recognition-form button[type="submit"] {
    background: linear-gradient(110deg, #F28C28, #F5B041);
    color: white;
    font-weight: bold;
    box-shadow: 0 3px 8px rgba(242, 140, 40, 0.22);
    transition: transform 0.2s, box-shadow 0.2s;
}

#recognition-form button[type="submit"]:hover {
    background: linear-gradient(110deg, #E67E22, #F39C12);
    transform: translateY(-1px);
    box-shadow: 0 5px 12px rgba(242, 140, 40, 0.3);
}

/* Camera panel */

/* Camera panel */
#camera-container {
    flex-basis: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    padding: 18px;
    background: #FFFCFA;
    border: 1px solid #e8eeeb;
    border-radius: 12px;
}

#camera-container[hidden] {
    display: none !important;
}

#camera-preview {
    display: block;
    width: 100%;
    max-width: 520px;
    max-height: 300px;
    object-fit: contain;
    border-radius: 10px;
    background: #222;
}

.camera-buttons {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
}

.camera-buttons button {
    min-width: 130px;
}

@media (max-width: 768px) {
    #camera-container {
        width: 100%;
    }

    .camera-buttons {
        width: 100%;
    }

    .camera-buttons button {
        flex: 1;
    }
}

/* Top notification */
.notification {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(-15px);

    padding: 13px 22px;
    min-width: 260px;
    max-width: calc(100% - 30px);

    background: #E8F6EE;
    color: #176B43;
    border: 2px solid #4EA179;
    border-radius: 12px;

    font-size: 14px;
    font-weight: 600;
    text-align: center;

    box-shadow: 0 5px 18px rgba(24, 129, 117, 0.15);

    opacity: 0;
    visibility: hidden;
    z-index: 9999;

    transition: opacity 0.25s ease, transform 0.25s ease,
                visibility 0.25s ease;
}

.notification.show {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(0);
}

.scanner-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    margin-bottom: 0px;
}

.scanner-header h1 {
    margin: 0;
}

.back-home-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #188175;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 14px;
    transition: background 0.2s ease;
}

.back-home-btn:hover {
    background: #12685f;
}
</style>


</head>

<body>
    <div id="notification" class="notification" role="status" aria-live="polite"></div>

    <div class="scanner-header">
        <h1>Pet Recognition</h1>
        <a href="../stray_safe/home.php" class="back-home-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Home
        </a>
    </div>

    <p>
        Upload a picture of a pet to identify its breed
        and check whether it matches a registered pet.
    </p>


    <!-- Image Upload Form -->

    
<form id="recognition-form" method="POST" enctype="multipart/form-data">

    <label for="pet_image">Upload a pet image:</label>
    <input
        type="file"
        id="pet_image"
        name="pet_image"
        accept="image/jpeg,image/png,image/webp"
    >

    <button type="button" id="start-camera">Start Camera</button>

    <div id="camera-container" hidden>
        <video id="camera-preview" autoplay playsinline></video>

        <div class="camera-buttons">
            <button type="button" id="capture-photo">Take Photo</button>
            <button type="button" id="stop-camera">Close Camera</button>
        </div>
    </div>

    <p id="camera-message" aria-live="polite"></p>

    <button type="submit">Recognize Pet</button>

    <canvas id="capture-canvas" hidden></canvas>

</form>



    <?php if ($uploaded_image !== null): ?>

        <hr>

        <h2 id="results-heading">Recognition Results</h2>

        <div class="recognition-container" id="recognition-results">

            <!-- Uploaded Image -->

            <div class="image-container">

                <h3>Uploaded Image</h3>

                <img
                    src="<?php echo htmlspecialchars($uploaded_image); ?>"
                    alt="Uploaded pet"
                    class="pet-image"
                >

                <p>
                    <?php echo htmlspecialchars($image_name); ?>
                </p>

            </div>


            <!-- Recognition Results -->

            <div class="results-container">

                <!-- Breed Results -->

                <div class="breed-results">

                    <h3>Breed Classification</h3>

                    <?php if ($breed_error !== ""): ?>

                        <p>
                            <?php echo htmlspecialchars($breed_error); ?>
                        </p>

                    <?php elseif (count($breed_results) > 0): ?>

                        <div class="breed-list">

                            <?php foreach ($breed_results as $index => $breed): ?>

                                <div class="breed-item <?php echo $index === 0 ? 'pinned-result' : ''; ?>">

                                    <span>
                                        <?php echo $index + 1; ?>.
                                        <?php echo htmlspecialchars($breed["breed"]); ?>
                                    </span>

                                    <span>
                                        <?php echo number_format($breed["score"] * 100, 2); ?>%
                                    </span>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <p>No breed predictions available.</p>

                    <?php endif; ?>

                </div>


                <!-- Individual Pet Results -->

                <div class="pet-results">

                    <h3>Pet Identification</h3>

                    <?php if ($pet_error !== ""): ?>

                        <p>
                            <?php echo htmlspecialchars($pet_error); ?>
                        </p>

                    <?php elseif (count($pet_results) > 0): ?>

                        <div class="pet-list">

                            <?php foreach ($pet_results as $index => $pet): ?>

                                <div class="pet-item <?php echo $index === 0 ? 'pinned-result' : ''; ?>">

                                    <span>
                                        <?php echo $index + 1; ?>.
                                        <?php echo htmlspecialchars($pet["pet_name"]); ?>
                                    </span>

                                    <span>
                                        <?php echo htmlspecialchars($pet["pet_id"]); ?>
                                    </span>

                                    <span>
                                        <?php echo number_format($pet["similarity"] * 100, 2); ?>%
                                    </span>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <p>No matching registered pets found.</p>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    <?php endif; ?>

    <script> 

const form = document.getElementById("recognition-form");

const startCameraButton = document.getElementById("start-camera");
const captureButton = document.getElementById("capture-photo");
const stopCameraButton = document.getElementById("stop-camera");

const cameraContainer = document.getElementById("camera-container");
const video = document.getElementById("camera-preview");
const canvas = document.getElementById("capture-canvas");

const imageInput = document.getElementById("pet_image");
const cameraMessage = document.getElementById("camera-message");

const notification = document.getElementById("notification");

let notificationTimer;

function showNotification(message) {
    notification.textContent = message;
    notification.classList.add("show");

    clearTimeout(notificationTimer);

    notificationTimer = setTimeout(() => {
        notification.classList.remove("show");
    }, 3500);
}

<?php if ($success_message !== ""): ?>
    showNotification(<?php echo json_encode($success_message); ?>);
<?php endif; ?>

let cameraStream = null;


// Stop the camera and release its resources
function stopCameraStream() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }

    video.srcObject = null;
}


// Close the camera panel
function closeCameraPanel() {
    stopCameraStream();
    cameraContainer.hidden = true;
}


// Start the camera
startCameraButton.addEventListener("click", async () => {

    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        cameraMessage.textContent =
            "Camera access is not supported by this browser.";
        return;
    }

    try {
        startCameraButton.disabled = true;

        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: { ideal: "environment" }
            },
            audio: false
        });

        video.srcObject = cameraStream;
        cameraContainer.hidden = false;

        cameraMessage.textContent = "Camera ready. Take a photo.";

    } catch (error) {
        cameraMessage.textContent =
            "Unable to access camera. Check your browser permissions.";

        stopCameraStream();

    } finally {
        startCameraButton.disabled = false;
    }
});


// Take a photo and immediately recognize it
captureButton.addEventListener("click", () => {

    if (!cameraStream) {
        return;
    }

    const width = video.videoWidth;
    const height = video.videoHeight;

    if (width === 0 || height === 0) {
        cameraMessage.textContent =
            "Camera is not ready yet. Please wait.";
        return;
    }

    canvas.width = width;
    canvas.height = height;

    const context = canvas.getContext("2d");

    context.drawImage(video, 0, 0, width, height);

    canvas.toBlob((blob) => {

        if (!blob) {
            cameraMessage.textContent = "Failed to capture photo.";
            return;
        }

        const photoFile = new File(
            [blob],
            "camera-capture.jpg",
            { type: "image/jpeg" }
        );

        const transfer = new DataTransfer();
        transfer.items.add(photoFile);

        imageInput.files = transfer.files;

        // Close the camera before submitting
        closeCameraPanel();
        
        // Automatically submit the existing PHP form
        form.requestSubmit();

    }, "image/jpeg", 0.92);
});


// Manually close the camera
stopCameraButton.addEventListener("click", () => {

    closeCameraPanel();

    cameraMessage.textContent = "Camera closed.";
});

const resultsSection = document.getElementById("recognition-results");
const resultsHeading = document.getElementById("results-heading");

// Clear previous results when selecting a new image
imageInput.addEventListener("change", () => {

    if (imageInput.files.length > 0) {

        closeCameraPanel();

        // Hide previous recognition results
        if (resultsSection) {
            resultsSection.style.display = "none";
        }

        if (resultsHeading) {
            resultsHeading.style.display = "none";
        }

        showNotification("Image selected. Click Recognize Pet.");
    }
});


// Release camera resources when leaving the page
window.addEventListener("beforeunload", stopCameraStream);

    </script>
</body>
</html>
